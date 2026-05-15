<?php

namespace App\Services\Biometric;

use App\Models\FaceFeature;
use App\Models\Student;
use Illuminate\Support\Facades\Log;


class BiometricRegistrar
{
    public function __construct(
        private readonly CaptureDriver $driver,
    ) {}

    /**
     *
     * @param  int   $studentId
     * @param  int   $userId
     * @return FaceFeature
     *
     * @throws CaptureException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function register(int $studentId, int $userId): FaceFeature
    {
        $student = Student::findOrFail($studentId);


        $hasAccess = \App\Models\User::find($userId)
            ?->schools()
            ->where('schools.id', $student->school_id)
            ->exists();

        if (!$hasAccess) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'Você não tem permissão para cadastrar alunos desta escola.'
            );
        }


        $captureData = $this->driver->capture($studentId);

        $faceFeature = FaceFeature::updateOrCreate(
            ['student_id' => $studentId],
            [
                'landmark_hash' => $captureData['landmark_hash'],
                'embedding'     => $captureData['landmarks'] ?: null,
            ]
        );

        Log::info('[BiometricRegistrar] Biometria cadastrada', [
            'student_id'    => $studentId,
            'registered_by' => $userId,
            'face_id'       => $faceFeature->id,
            'driver'        => get_class($this->driver),
        ]);

        return $faceFeature;
    }
}
