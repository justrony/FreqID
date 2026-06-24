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

    public function register(int $studentId, int $userId): FaceFeature
    {
        $student = Student::findOrFail($studentId);

        $user = \App\Models\User::find($userId);

        if ($user === null) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'Usuário não encontrado.'
            );
        }

        $hasAccess = $user->schools()
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
                'embedding' => $captureData['embedding'],
            ]
        );

        Log::info('[BiometricRegistrar] Biometria cadastrada', [
            'student_id'    => $studentId,
            'registered_by' => $userId,
            'face_id'       => $faceFeature->id,
        ]);

        return $faceFeature;
    }
}
