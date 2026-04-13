<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AttendanceOnlySeeder extends Seeder
{

    public function run(): void
    {
        // busca todos os alunos com turmas
        $students = DB::table('students')->get();

        if ($students->isEmpty()) {
            $this->command->warn('Nenhum aluno encontrado.');
            return;
        }

        $now         = Carbon::now();
        $attendances = [];

        foreach ($students as $student) {
            $presenceRate = mt_rand(70, 95) / 100;

            for ($m = 11; $m >= 0; $m--) {
                $monthStart = $now->copy()->subMonths($m)->startOfMonth();
                $monthEnd   = $now->copy()->subMonths($m)->endOfMonth();

                $day = $monthStart->copy();
                while ($day->lte($monthEnd)) {
                    if ($day->isWeekday()) {
                        $status = ((mt_rand(0, 100) / 100) <= $presenceRate) ? 'presente' : 'ausente';

                        $attendances[] = [
                            'student_id'      => $student->id,
                            'class_id'        => $student->class_id,
                            'marked_at'       => $day->copy()->setTime(7, mt_rand(30, 59))->toDateTimeString(),
                            'status'          => $status,
                            'confidence_score'=> $status === 'presente' ? round(mt_rand(85, 99) / 100, 4) : null,
                            'created_at'      => now()->toDateTimeString(),
                            'updated_at'      => now()->toDateTimeString(),
                        ];
                    }
                    $day->addDay();
                }
            }
        }

        $this->command->info('Inserindo ' . count($attendances) . ' registros em chunks...');

        foreach (array_chunk($attendances, 500) as $chunk) {
            DB::table('attendances')->insert($chunk);
        }

        $this->command->info('Concluído!');
    }
}
