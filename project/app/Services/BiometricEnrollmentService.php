<?php

namespace App\Services;

use App\Models\FaceFeature;
use App\Models\Student;
use App\Services\Biometric\BiometricRegistrar;
use App\Services\Biometric\CaptureException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Service Layer para operações de cadastro biométrico facial.
 *
 * Orquestra o fluxo completo de registro:
 *   1. Lista alunos disponíveis para o professor
 *   2. Verifica se já existe biometria cadastrada
 *   3. Delega o registro ao BiometricRegistrar (Bridge GoF)
 */
class BiometricEnrollmentService
{
    public function __construct(
        private readonly BiometricRegistrar $registrar,
    ) {}

    /**
     * Retorna os alunos das turmas vinculadas às escolas do professor,
     * com indicação de quais já possuem biometria cadastrada.
     */
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

    /**
     * Verifica se um aluno já possui biometria cadastrada.
     */
    public function isEnrolled(int $studentId): bool
    {
        return FaceFeature::where('student_id', $studentId)->exists();
    }

    /**
     * Executa o cadastro biométrico via BiometricRegistrar.
     *
     * @throws CaptureException          se a câmera falhar
     * @throws AuthorizationException    se o professor não tiver acesso
     */
    public function enroll(int $studentId, int $userId): FaceFeature
    {
        return $this->registrar->register($studentId, $userId);
    }
}
