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

        // NOVO: Vincula todos os usuários às escolas criadas, assim como o StudentSeeder faz
        // Isso garante que os usuários (como o Teste2) consigam ver os gráficos dessas 3 escolas
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            $user->schools()->syncWithoutDetaching($schoolIds);
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
                        'school_id'       => $schoolId,
                        'class_id'        => $classId,
                        'name'            => "{$firstName} {$lastName}",
                        'registration'    => 'MAT' . str_pad(++$regCounter, 5, '0', STR_PAD_LEFT),
                        'cpf'             => str_pad($regCounter, 11, '0', STR_PAD_LEFT),
                        'nome_mae'        => "Mãe do(a) {$firstName}",
                        'data_nascimento' => now()->subYears(15)->format('Y-m-d'), // ~15 anos
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }
            }
        }

        // 4. Presenças — insere em lotes de 500 para não estourar memória
        $buffer = [];
        $now    = Carbon::now();

        foreach ($studentIds as $classId => $ids) {
            foreach ($ids as $studentId) {
                $presenceRate = mt_rand(70, 95) / 100;

                for ($m = 11; $m >= 0; $m--) {
                    $monthStart = $now->copy()->subMonths($m)->startOfMonth();
                    $monthEnd   = $now->copy()->subMonths($m)->endOfMonth();

                    $day = $monthStart->copy();
                    while ($day->lte($monthEnd)) {
                        if ($day->isWeekday()) {
                            $status = ((mt_rand(0, 100) / 100) <= $presenceRate)
                                ? 'presente'
                                : 'ausente';

                            $buffer[] = [
                                'student_id'       => $studentId,
                                'class_id'         => $classId,
                                'marked_at'        => $day->copy()->setTime(7, mt_rand(30, 59)),
                                'status'           => $status,
                                'confidence_score' => $status === 'presente' ? round(mt_rand(85, 99) / 100, 4) : null,
                                'created_at'       => now(),
                                'updated_at'       => now(),
                            ];

                            // Flush a cada 500 registros
                            if (count($buffer) >= 500) {
                                DB::table('attendances')->insert($buffer);
                                $buffer = [];
                            }
                        }
                        $day->addDay();
                    }
                }
            }
        }

        // Insere registros restantes
        if (!empty($buffer)) {
            DB::table('attendances')->insert($buffer);
        }
    }
}
