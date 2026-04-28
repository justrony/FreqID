<?php

namespace App\Services;

use App\Models\FaceFeature;
use Illuminate\Support\Facades\Log;

class FaceScanService
{
    public function storeFaceScan(array $data): FaceFeature
    {
        $faceFeature = FaceFeature::create([
            'landmark_hash' => $data['landmark_hash'],
            'embedding'     => $data['landmarks'] ?? null,
        ]);

        Log::info('FaceScan armazenado', [
            'id'            => $faceFeature->id,
            'landmark_hash' => $faceFeature->landmark_hash,
        ]);

        return $faceFeature;
    }

    public function registerFace(array $data): FaceFeature
    {
        $faceFeature = FaceFeature::updateOrCreate(
            ['student_id' => $data['student_id']],
            [
                'landmark_hash' => $data['landmark_hash'],
                'embedding'     => $data['landmarks'] ?? null,
            ]
        );

        Log::info('Rosto cadastrado', [
            'id'         => $faceFeature->id,
            'student_id' => $faceFeature->student_id,
        ]);

        return $faceFeature;
    }

    public function findByHash(string $hash): ?FaceFeature
    {
        return FaceFeature::where('landmark_hash', $hash)->first();
    }
}
