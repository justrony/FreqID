<?php

namespace App\Services\Biometric;

use App\Models\FaceFeature;
use App\Models\Student;
use Illuminate\Support\Facades\Log;

/**
 * GoF Bridge — Abstraction
 *
 * Contém a lógica de negócio de cadastro biométrico e delega
 * a CAPTURA ao CaptureDriver (Implementor), que pode ser trocado
 * sem alterar esta classe (Python, WebCam JS, Mock em testes, etc.).
 *
 * Responsabilidades desta classe:
 *  - Validar acesso do professor ao aluno
 *  - Coordenar a captura via driver
 *  - Persistir o FaceFeature no banco
 *  - Registrar logs de auditoria
 */
class BiometricRegistrar
{
    public function __construct(
        private readonly CaptureDriver $driver,
    ) {}

    /**
     * Cadastra (ou atualiza) a biometria facial de um aluno.
     *
     * @param  int   $studentId  ID do aluno
     * @param  int   $userId     ID do professor que está realizando o cadastro
     * @return FaceFeature
     *
     * @throws CaptureException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function register(int $studentId, int $userId): FaceFeature
    {
        // 1. Garante que o aluno existe
        $student = Student::findOrFail($studentId);

        // 2. Valida escopo: professor deve pertencer à escola do aluno
        $hasAccess = \App\Models\User::find($userId)
            ?->schools()
            ->where('schools.id', $student->school_id)
            ->exists();

        if (!$hasAccess) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'Você não tem permissão para cadastrar alunos desta escola.'
            );
        }

        // 3. Delega a captura ao driver (Bridge)
        $captureData = $this->driver->capture($studentId);

        // 4. Persiste a biometria
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
