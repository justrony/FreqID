<?php

namespace App\Services;

use App\Models\Attendance;

class AttendanceService
{
    public function registerFrequency(array $data)
    {

        return Attendance::create([
            'student_id' => $data['student_id'],
            'check_in'   => now(),
            'status'     => 'presente',
        ]);
    }
}
