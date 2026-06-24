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
            ['name' => 'João da Silva (Teste)', 'registration' => 'BIO001', 'cpf' => '00000000001', 'nome_mae' => 'Maria da Silva', 'nome_pai' => 'José da Silva', 'data_nascimento' => '2010-01-01'],
            ['name' => 'Maria Oliveira (Teste)', 'registration' => 'BIO002', 'cpf' => '00000000002', 'nome_mae' => 'Ana Oliveira', 'nome_pai' => 'Carlos Oliveira', 'data_nascimento' => '2011-05-15'],
            ['name' => 'Carlos Pereira (Teste)', 'registration' => 'BIO003', 'cpf' => '00000000003', 'nome_mae' => 'Carla Pereira', 'nome_pai' => null, 'data_nascimento' => '2010-08-20'],
            ['name' => 'Ana Souza (Teste)', 'registration' => 'BIO004', 'cpf' => '00000000004', 'nome_mae' => 'Juliana Souza', 'nome_pai' => 'Ricardo Souza', 'data_nascimento' => '2012-03-10'],
            ['name' => 'Pedro Costa (Teste)', 'registration' => 'BIO005', 'cpf' => '00000000005', 'nome_mae' => 'Sonia Costa', 'nome_pai' => null, 'data_nascimento' => '2010-11-05'],
        ];

        foreach ($testStudents as $studentData) {
            Student::firstOrCreate(
                ['registration' => $studentData['registration']],
                [
                    'name'            => $studentData['name'],
                    'school_id'       => $school->id,
                    'class_id'        => $schoolClass->id,
                    'cpf'             => $studentData['cpf'],
                    'nome_mae'        => $studentData['nome_mae'],
                    'nome_pai'        => $studentData['nome_pai'],
                    'data_nascimento' => $studentData['data_nascimento'],
                ]
            );
        }
    }
}
