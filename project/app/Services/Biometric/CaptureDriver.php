<?php

namespace App\Services\Biometric;

/**
 * GoF Bridge — Implementor Interface
 *
 * Define o contrato para qualquer driver de captura biométrica.
 * Permite trocar a implementação (câmera Python, câmera web, mock, etc.)
 * sem alterar a lógica de negócio da abstração BiometricCapture.
 */
interface CaptureDriver
{
    /**
     * Requisita uma captura e retorna os dados brutos do dispositivo.
     *
     * @param  int  $studentId  ID do aluno a ser registrado
     * @return array{landmark_hash: string, landmarks: array}
     *
     * @throws \App\Services\Biometric\CaptureException
     */
    public function capture(int $studentId): array;
}
