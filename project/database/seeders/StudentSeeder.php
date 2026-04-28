<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Cria ou pega uma escola de teste
        $school = School::firstOrCreate(
            ['inep_code' => '99999999'],
            ['name' => 'Escola de Teste Biometria']
        );

        // 2. Associa todos os usuários existentes a esta escola 
        // (Isso é importante para o professor conseguir ver os alunos na tela de Biometria)
        $users = User::all();
        foreach ($users as $user) {
            $user->schools()->syncWithoutDetaching([$school->id]);
        }

        // 3. Cria uma turma de teste
        $schoolClass = SchoolClass::firstOrCreate(
            ['name' => 'Turma de Teste Facial', 'school_id' => $school->id]
        );

        // 4. Cria estudantes de teste específicos
        $testStudents = [
            ['name' => 'João da Silva (Teste)', 'registration' => 'BIO001'],
            ['name' => 'Maria Oliveira (Teste)', 'registration' => 'BIO002'],
            ['name' => 'Carlos Pereira (Teste)', 'registration' => 'BIO003'],
            ['name' => 'Ana Souza (Teste)', 'registration' => 'BIO004'],
            ['name' => 'Pedro Costa (Teste)', 'registration' => 'BIO005'],
        ];

        foreach ($testStudents as $studentData) {
            Student::firstOrCreate(
                ['registration' => $studentData['registration']],
                [
                    'name'      => $studentData['name'],
                    'school_id' => $school->id,
                    'class_id'  => $schoolClass->id,
                ]
            );
        }
    }
}
