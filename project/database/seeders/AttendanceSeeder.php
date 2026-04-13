<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Escolas
        $schools = [
            ['name' => 'E.M. João Pessoa', 'inep_code' => '23001001'],
            ['name' => 'E.M. Santos Dumont', 'inep_code' => '23001002'],
            ['name' => 'E.M. Tiradentes', 'inep_code' => '23001003'],
        ];

        $schoolIds = [];
        foreach ($schools as $school) {
            $schoolIds[] = DB::table('schools')->insertGetId(array_merge($school, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 2. Turmas por escola
        $classNames = ['1º Ano A', '1º Ano B', '2º Ano A', '2º Ano B', '3º Ano A'];
        $classIds = [];

        foreach ($schoolIds as $schoolId) {
            foreach ($classNames as $className) {
                $classIds[$schoolId][] = DB::table('classes')->insertGetId([
                    'school_id'  => $schoolId,
                    'name'       => $className,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Alunos por turma
        $firstNames = [
            'Ana', 'Bruno', 'Carla', 'Diego', 'Eduarda', 'Felipe', 'Gabriela',
            'Henrique', 'Isabela', 'João', 'Kelly', 'Lucas', 'Mariana', 'Nathan',
            'Olivia', 'Pedro', 'Rafaela', 'Samuel', 'Tatiane', 'Ulisses',
        ];

        $studentIds = [];
        $regCounter  = 1000;

        foreach ($schoolIds as $schoolId) {
            foreach ($classIds[$schoolId] as $classId) {
                // 20 alunos por turma
                $selected = $firstNames;
                shuffle($selected);
                $selected = array_slice($selected, 0, 20);

                foreach ($selected as $firstName) {
                    $lastName  = collect(['Silva', 'Santos', 'Oliveira', 'Souza', 'Lima', 'Pereira', 'Costa', 'Ferreira'])->random();
                    $studentIds[$classId][] = DB::table('students')->insertGetId([
                        'school_id'    => $schoolId,
                        'class_id'     => $classId,
                        'name'         => "{$firstName} {$lastName}",
                        'registration' => 'MAT' . str_pad(++$regCounter, 5, '0', STR_PAD_LEFT),
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }
            }
        }

        // 4. Presenças
        $attendances = [];
        $now         = Carbon::now();

        foreach ($studentIds as $classId => $ids) {
            foreach ($ids as $studentId) {
                $presenceRate = mt_rand(70, 95) / 100;

                for ($m = 11; $m >= 0; $m--) {
                    $monthStart = $now->copy()->subMonths($m)->startOfMonth();
                    $monthEnd   = $now->copy()->subMonths($m)->endOfMonth();

                    // Dias úteis do mês
                    $day = $monthStart->copy();
                    while ($day->lte($monthEnd)) {
                        if ($day->isWeekday()) {
                            if ((mt_rand(0, 100) / 100) <= $presenceRate) {
                                $status = 'presente';
                            } else {
                                $status = 'ausente';
                            }

                            $attendances[] = [
                                'student_id'       => $studentId,
                                'class_id'         => $classId,
                                'marked_at'        => $day->copy()->setTime(7, mt_rand(30, 59)),
                                'status'           => $status,
                                'confidence_score' => $status === 'presente' ? round(mt_rand(85, 99) / 100, 4) : null,
                                'created_at'       => now(),
                                'updated_at'       => now(),
                            ];
                        }
                        $day->addDay();
                    }
                }
            }
        }

        //chunks 
        foreach (array_chunk($attendances, 500) as $chunk) {
            DB::table('attendances')->insert($chunk);
        }
    }
}
