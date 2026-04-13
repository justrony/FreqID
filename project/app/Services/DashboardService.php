<?php

namespace App\Services;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Repositories\AttendanceRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;


class DashboardService
{
    public function __construct(
        private readonly AttendanceRepository $repository
    ) {}

    /**
     *
     * @return array<int>
     */
    public function resolveSchoolScope(User $user): array
    {
        if ($user->affiliation !== 'school') {
            return [];
        }

        return $user->schools()->pluck('schools.id')->toArray();
    }

    public function isSchoolUser(User $user): bool
    {
        return $user->affiliation === 'school';
    }

    /**
     *
     * @return Collection<int, string>
     */
    public function userSchoolNames(array $schoolIds): Collection
    {
        if (empty($schoolIds)) {
            return collect();
        }

        return School::whereIn('id', $schoolIds)->pluck('name');
    }


    /**
     *
     * @return array{
     *   totalRegistros: int,
     *   totalPresentes: int,
     *   totalAusentes: int,
     *   taxaGeral: float,
     *   totalAlunos: int,
     *   totalTurmas: int,
     *   totalEscolas: int,
     * }
     */
    public function kpis(int $ano, array $schoolIds = []): array
    {
        $totalRegistros = $this->repository->countTotal($ano, $schoolIds);
        $totalPresentes = $this->repository->countPresentes($ano, $schoolIds);

        return [
            'totalRegistros' => $totalRegistros,
            'totalPresentes' => $totalPresentes,
            'totalAusentes'  => $totalRegistros - $totalPresentes,
            'taxaGeral'      => $totalRegistros > 0
                ? round($totalPresentes / $totalRegistros * 100, 1)
                : 0,
            'totalAlunos'  => Student::when($schoolIds !== [], fn ($q) =>
                $q->whereIn('school_id', $schoolIds)
            )->count(),
            'totalTurmas'  => SchoolClass::when($schoolIds !== [], fn ($q) =>
                $q->whereIn('school_id', $schoolIds)
            )->count(),
            'totalEscolas' => $schoolIds !== []
                ? count($schoolIds)
                : School::count(),
        ];
    }


    // gráfico de linha


    /**
     *
     * @return array{
     *   labels: array<string>,
     *   taxas: array<float>,
     *   presentes: array<int>,
     *   totais: array<int>,
     * }
     */
    public function graficoMensal(int $ano, array $schoolIds = []): array
    {
        $dados = $this->repository->frequenciaPorMes($ano, $schoolIds);

        $labels   = [];
        $taxas    = [];
        $presentes = [];
        $totais   = [];

        for ($m = 1; $m <= 12; $m++) {
            $row      = $dados->get($m);
            $total    = $row?->total    ?? 0;
            $presente = $row?->presentes ?? 0;

            $labels[]    = Carbon::createFromDate($ano, $m, 1)
                ->locale('pt_BR')
                ->translatedFormat('M');
            $taxas[]     = $total > 0 ? round($presente / $total * 100, 1) : 0;
            $presentes[] = $presente;
            $totais[]    = $total;
        }

        return compact('labels', 'taxas', 'presentes', 'totais');
    }

    // gráfico de barras


    /**
     *
     * @return array{ labels: array<string>, taxas: array<float> }
     */
    public function graficoTurmas(int $ano, array $schoolIds = [], int $limit = 10): array
    {
        $rows = $this->repository->frequenciaPorTurma($ano, $schoolIds, $limit);

        $labels = [];
        $taxas  = [];

        foreach ($rows as $row) {
            $labels[] = $row->label;
            $taxas[]  = $row->total > 0
                ? round($row->presentes / $row->total * 100, 1)
                : 0;
        }

        return compact('labels', 'taxas');
    }

    // gráfico donut


    /**
     *
     * @return array{
     *   labels: array<string>,
     *   taxas: array<float>,
     *   presentes: array<int>,
     * }
     */
    public function graficoEscolas(int $ano, array $schoolIds = []): array
    {
        $rows = $this->repository->frequenciaPorEscola($ano, $schoolIds);

        $labels   = [];
        $taxas    = [];
        $presentes = [];

        foreach ($rows as $row) {
            $labels[]    = $row->name;
            $taxas[]     = $row->total > 0
                ? round($row->presentes / $row->total * 100, 1)
                : 0;
            $presentes[] = $row->presentes;
        }

        return compact('labels', 'taxas', 'presentes');
    }

    // top alunos com mais faltas


    public function topFaltas(int $ano, array $schoolIds = [], int $limit = 5): Collection
    {
        return $this->repository->topAlunasComFaltas($ano, $schoolIds, $limit);
    }

    // anos disponíveis para o filtro

    public function anosDisponiveis(array $schoolIds = []): Collection
    {
        return $this->repository->anosDisponiveis($schoolIds);
    }
}
