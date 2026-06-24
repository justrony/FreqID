<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    /**
     *
     * @return int Quantidade de faltas registradas.
     */
    public function markAbsences(Carbon $date): int
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay   = $date->copy()->endOfDay();

        $alreadyMarkedIds = Attendance::whereBetween('marked_at', [$startOfDay, $endOfDay])
            ->pluck('student_id')
            ->unique()
            ->all();

        $query = Student::whereNotNull('class_id')
            ->when($alreadyMarkedIds, fn ($q) => $q->whereNotIn('id', $alreadyMarkedIds));

        $total = 0;

        $query->chunk(500, function ($students) use ($startOfDay, &$total) {
            $rows = [];

            foreach ($students as $student) {
                $rows[] = [
                    'student_id'       => $student->id,
                    'class_id'         => $student->class_id,
                    'marked_at'        => $startOfDay->copy()->setTime(23, 59, 59),
                    'status'           => 'ausente',
                    'confidence_score' => null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }

            if (!empty($rows)) {
                DB::table('attendances')->insert($rows);
                $total += count($rows);
            }
        });

        Log::info('[AttendanceService] Faltas registradas', [
            'date'  => $startOfDay->toDateString(),
            'count' => $total,
        ]);

        return $total;
    }
}
