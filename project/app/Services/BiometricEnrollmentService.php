<?php

namespace App\Services;

use App\Models\FaceFeature;
use App\Models\Student;
use App\Services\Biometric\BiometricRegistrar;
use App\Services\Biometric\CaptureException;
use App\Services\Biometric\PhotoUploadDriver;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 *
 * Dois modos de cadastro:
 *   - enroll()           : Cam ao vivo
 *   - enrollFromPhoto()  : Por imagem
 */
class BiometricEnrollmentService
{
    public function __construct(
        private readonly BiometricRegistrar $registrar,
        private readonly PhotoUploadDriver  $photoDriver,
    ) {}

    public function getStudentsForEnrollment(int $userId): Collection
    {
        $schoolIds = \App\Models\User::find($userId)
            ?->schools()
            ->pluck('schools.id')
            ->toArray() ?? [];

        return Student::with(['schoolClass', 'school'])
            ->whereIn('school_id', $schoolIds)
            ->withExists('faceFeature')
            ->orderBy('name')
            ->get();
    }

    public function isEnrolled(int $studentId): bool
    {
        return FaceFeature::where('student_id', $studentId)->exists();
    }

    public function enrollFromPhoto(int $studentId, int $userId, UploadedFile $photo): FaceFeature
    {
        $student = Student::findOrFail($studentId);
        $user    = \App\Models\User::find($userId);

        if ($user === null) {
            throw new AuthorizationException('Usuário não encontrado.');
        }

        $hasAccess = $user->schools()
            ->where('schools.id', $student->school_id)
            ->exists();

        if (!$hasAccess) {
            throw new AuthorizationException(
                'Você não tem permissão para cadastrar alunos desta escola.'
            );
        }

        $captureData = $this->photoDriver->captureFromPhoto($studentId, $photo);

        $faceFeature = FaceFeature::updateOrCreate(
            ['student_id' => $studentId],
            [
                'landmark_hash' => $captureData['landmark_hash'],
                'embedding'     => $captureData['landmarks'] ?? null,
            ]
        );

        Log::info('[BiometricEnrollmentService] Biometria cadastrada via upload', [
            'student_id'    => $studentId,
            'registered_by' => $userId,
            'face_id'       => $faceFeature->id,
        ]);

        return $faceFeature;
    }

    /**
     * @deprecated Utilize enrollFromPhoto() para cadastro via upload de imagem.
     * Mantido para compatibilidade com fluxos legados.
     *
     * @throws CaptureException
     * @throws AuthorizationException
     */
    public function enroll(int $studentId, int $userId): FaceFeature
    {
        return $this->registrar->register($studentId, $userId);
    }
}

