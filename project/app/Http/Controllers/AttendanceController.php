<?php

namespace App\Http\Controllers;

use App\Models\FaceFeature;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AttendanceController
 *
 * Gerencia o fluxo de registro de frequência por reconhecimento facial.
 */
class AttendanceController extends Controller
{
    /**
     * Exibe a tela única de registro de frequência por câmera ao vivo.
     */
    public function index()
    {
        return view('pages.frequencia.show');
    }

    /**
     * Proxy: recebe a requisição do browser, chama o Python /capture-hash,
     * identifica o aluno pela similaridade dos landmarks e registra a presença.
     */
    public function capture(Request $request): JsonResponse
    {
        $pythonUrl = config('services.freqid_scanner.url', 'http://localhost:8001');

        //  1. Chamar Python /capture-hash só captura ──────────────────────────
        try {
            $pythonResponse = Http::timeout(10)->post("{$pythonUrl}/capture-hash", []);
        } catch (\Throwable $e) {
            Log::error('[AttendanceController] Falha ao conectar ao Python (capture)', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'type'    => 'camera',
                'message' => 'Não foi possível conectar ao servidor de câmera. Verifique se o Python está rodando.',
            ], 503);
        }

        //  2. Tratar erros do Python na captura ─────────────────────────────────
        if ($pythonResponse->status() === 404) {
            return response()->json([
                'success' => false,
                'type'    => 'no_face',
                'message' => 'Nenhum rosto detectado. Posicione o aluno de frente à câmera e tente novamente.',
            ], 200);
        }

        if ($pythonResponse->failed()) {
            $detail = $pythonResponse->json('detail', 'Erro desconhecido no servidor de câmera.');
            return response()->json([
                'success' => false,
                'type'    => 'error',
                'message' => is_string($detail) ? $detail : json_encode($detail),
            ], 200);
        }

        $pythonData = $pythonResponse->json();
        $liveLandmarks = $pythonData['landmarks'] ?? null;

        if (!$liveLandmarks || count($liveLandmarks) === 0) {
            return response()->json([
                'success' => false,
                'type'    => 'error',
                'message' => 'Resposta inválida do servidor de câmera. Landmarks não encontrados.',
            ], 200);
        }

        //  3. Preparar lista de rostos salvos para enviar ao Python ─────────────
        $allFeatures = FaceFeature::with('student.schoolClass')->get();
        $registeredFaces = [];

        foreach ($allFeatures as $feature) {
            if ($feature->embedding && is_array($feature->embedding)) {
                $registeredFaces[] = [
                    'face_id'   => $feature->id,
                    'landmarks' => $feature->embedding,
                ];
            }
        }

        if (empty($registeredFaces)) {
            return response()->json([
                'success' => false,
                'type'    => 'error',
                'message' => 'Nenhum rosto cadastrado no banco de dados para comparação.',
            ], 200);
        }

        //  4. Chamar Python /match para Análise de Procrustes ───────────────────
        try {
            $matchResponse = Http::timeout(10)->post("{$pythonUrl}/match", [
                'live_landmarks'   => $liveLandmarks,
                'registered_faces' => $registeredFaces,
            ]);
        } catch (\Throwable $e) {
            Log::error('[AttendanceController] Falha ao conectar ao Python (match)', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'type'    => 'error',
                'message' => 'Erro ao processar o reconhecimento facial.',
            ], 503);
        }

        if ($matchResponse->failed()) {
            $detail = $matchResponse->json('detail', 'Erro no Procrustes');
            Log::error('[AttendanceController] Erro no /match', ['detail' => $detail]);
            return response()->json([
                'success' => false,
                'type'    => 'error',
                'message' => 'Erro interno na análise do rosto.',
            ], 200);
        }

        $matchData = $matchResponse->json();
        $bestFaceId = $matchData['best_face_id'] ?? null;
        $disparity = $matchData['disparity'] ?? 999.0;


        $threshold = 0.08;

        if (!$bestFaceId || $disparity > $threshold) {
            Log::info('[AttendanceController] Reconhecimento falhou (Procrustes)', ['disparity' => $disparity, 'threshold' => $threshold]);
            return response()->json([
                'success' => false,
                'type'    => 'not_found',
                'message' => 'Aluno não identificado. (Disparidade: ' . number_format($disparity, 4) . ')',
            ], 200);
        }

        $bestMatch = $allFeatures->firstWhere('id', $bestFaceId);
        if (!$bestMatch || !$bestMatch->student) {
            return response()->json([
                'success' => false,
                'type'    => 'not_found',
                'message' => 'Aluno não encontrado no banco de dados.',
            ], 200);
        }

        $student = $bestMatch->student;

        //  5. Registrar frequência ──────────────────────────────────────────────
        $attendance = Attendance::create([
            'student_id' => $student->id,
            'class_id'   => $student->class_id,
            'marked_at'  => now(),
            'status'     => 'presente',
        ]);

        $studentClass = $student->schoolClass?->name ?? 'Sem turma';
        $time = now()->format('H:i:s');

        Log::info('[AttendanceController] Presença registrada via Procrustes', [
            'student_id' => $student->id,
            'disparity'  => $disparity,
        ]);

        return response()->json([
            'success'       => true,
            'student_name'  => $student->name,
            'student_class' => $studentClass,
            'time'          => $time,
        ]);
    }
}
