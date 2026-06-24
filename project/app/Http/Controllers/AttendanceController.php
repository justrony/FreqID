<?php

namespace App\Http\Controllers;

use App\Models\FaceFeature;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('pages.frequencia.show');
    }

    public function capture(Request $request): JsonResponse
    {
        $pythonUrl = config('services.freqid_scanner.url', 'http://localhost:8001');

        try {
            $pythonResponse = Http::timeout(10)->post("{$pythonUrl}/capture-hash", []);
        } catch (\Throwable $e) {
            Log::error('[AttendanceController] Falha ao conectar ao Python (capture)', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'type'    => 'camera',
                'message' => 'Não foi possível conectar ao servidor de câmera.',
            ], 503);
        }

        if ($pythonResponse->status() === 404) {
            return response()->json([
                'success' => false,
                'type'    => 'no_face',
                'message' => 'Nenhum rosto detectado. Posicione o aluno de frente à câmera.',
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
        $liveEmbedding = $pythonData['embedding'] ?? null;

        if (!$liveEmbedding || !is_array($liveEmbedding)) {
            return response()->json([
                'success' => false,
                'type'    => 'error',
                'message' => 'Resposta inválida do servidor de câmera.',
            ], 200);
        }

        $matchResult = app(\App\Services\FaceScanService::class)->findByEmbedding($liveEmbedding);

        if (!$matchResult) {
            return response()->json([
                'success' => false,
                'type'    => 'not_found',
                'message' => 'Aluno não identificado ou erro na análise.',
            ], 200);
        }

        $bestMatch = $matchResult['feature'];
        $distance = $matchResult['distance'];

        if (!$bestMatch || !$bestMatch->student) {
            return response()->json([
                'success' => false,
                'type'    => 'not_found',
                'message' => 'Aluno não encontrado no banco de dados.',
            ], 200);
        }

        $student = $bestMatch->student;

        $attendance = Attendance::create([
            'student_id' => $student->id,
            'class_id'   => $student->class_id,
            'marked_at'  => now(),
            'status'     => 'presente',
        ]);

        $studentClass = $student->schoolClass?->name ?? 'Sem turma';
        $time = now()->format('H:i:s');

        return response()->json([
            'success'       => true,
            'student_name'  => $student->name,
            'student_class' => $studentClass,
            'time'          => $time,
            'distance'      => $distance,
        ]);
    }
}
