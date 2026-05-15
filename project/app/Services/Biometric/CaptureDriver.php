<?php

namespace App\Services\Biometric;


interface CaptureDriver
{
    /**
     *
     *
     * @param  int  $studentId
     * @return array{landmark_hash: string, landmarks: array}
     *
     * @throws \App\Services\Biometric\CaptureException
     */
    public function capture(int $studentId): array;
}
