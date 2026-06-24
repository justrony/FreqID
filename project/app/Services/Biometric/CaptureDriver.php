<?php

namespace App\Services\Biometric;

interface CaptureDriver
{
    public function capture(int $studentId): array;
}
