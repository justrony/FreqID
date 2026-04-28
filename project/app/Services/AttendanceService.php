<?php

namespace App\Services;

use App\Models\Attendance;

class AttendanceService
{
    public function registerFrequency(array $data): Attendance
    {
        return Attendance::create([
            'student_id' => $data['student_id'],
            'class_id'   => $data['class_id'],
            'marked_at'  => now(),
            'status'     => 'presente',
        ]);
    }
}
