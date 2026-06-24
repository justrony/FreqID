<?php

namespace App\Services;

use App\Models\FaceFeature;
use Illuminate\Support\Facades\Log;

class FaceScanService
{
    public function storeFaceScan(array $data): FaceFeature
    {
        $faceFeature = FaceFeature::create([
            'embedding' => $data['embedding'] ?? null,
        ]);

        Log::info('FaceScan armazenado', [
            'id' => $faceFeature->id,
        ]);

        return $faceFeature;
    }

    public function registerFace(array $data): FaceFeature
    {
        $faceFeature = FaceFeature::updateOrCreate(
            ['student_id' => $data['student_id']],
            [
                'embedding' => $data['embedding'] ?? null,
            ]
        );

        Log::info('Rosto cadastrado', [
            'id'         => $faceFeature->id,
            'student_id' => $faceFeature->student_id,
        ]);

        return $faceFeature;
    }

    public function findByEmbedding(array $liveEmbedding): ?array
    {
        $pythonUrl = config('services.freqid_scanner.url', 'http://localhost:8001');
        $bestGlobalDistance = 999.0;
        $bestGlobalFaceId = null;

        FaceFeature::chunk(500, function ($features) use ($liveEmbedding, $pythonUrl, &$bestGlobalDistance, &$bestGlobalFaceId) {
            $registeredFaces = [];
            foreach ($features as $feature) {
                if ($feature->embedding && is_array($feature->embedding)) {
                    $registeredFaces[] = [
                        'face_id'   => $feature->id,
                        'embedding' => $feature->embedding,
                    ];
                }
            }

            if (empty($registeredFaces)) {
                return;
            }

            try {
                $matchResponse = \Illuminate\Support\Facades\Http::timeout(10)->post("{$pythonUrl}/match", [
                    'live_embedding'   => $liveEmbedding,
                    'registered_faces' => $registeredFaces,
                ]);

                if ($matchResponse->successful()) {
                    $matchData = $matchResponse->json();
                    $verified = $matchData['verified'] ?? false;
                    $distance = $matchData['distance'] ?? 999.0;
                    $bestFaceId = $matchData['best_face_id'] ?? null;

                    if ($verified && $bestFaceId && $distance < $bestGlobalDistance) {
                        $bestGlobalDistance = $distance;
                        $bestGlobalFaceId = $bestFaceId;
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('[FaceScanService] Falha ao conectar ao Python (match chunk)', ['error' => $e->getMessage()]);
            }
        });

        if ($bestGlobalFaceId) {
            $feature = FaceFeature::with('student.schoolClass')->find($bestGlobalFaceId);
            if ($feature) {
                return ['feature' => $feature, 'distance' => $bestGlobalDistance];
            }
        }

        return null;
    }
}
