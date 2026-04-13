<?php

namespace App\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


class AttendanceRepository
{
    private function baseQuery(int $ano, array $schoolIds = [])
    {
        return DB::table('attendances')
            ->join('classes', 'attendances.class_id', '=', 'classes.id')
            ->whereYear('attendances.marked_at', $ano)
            ->when($schoolIds !== [], fn ($q) =>
                $q->whereIn('classes.school_id', $schoolIds)
            );
    }


    public function countTotal(int $ano, array $schoolIds = []): int
    {
        return (clone $this->baseQuery($ano, $schoolIds))->count();
    }


    public function countPresentes(int $ano, array $schoolIds = []): int
    {
        return (clone $this->baseQuery($ano, $schoolIds))
            ->where('attendances.status', 'presente')
            ->count();
    }


    // gráfico de linha

    /**
     * @return Collection<int, object{ mes: int, total: int, presentes: int }>
     */
    public function frequenciaPorMes(int $ano, array $schoolIds = []): Collection
    {
        return DB::table('attendances')
            ->join('classes', 'attendances.class_id', '=', 'classes.id')
            ->when($schoolIds !== [], fn ($q) =>
                $q->whereIn('classes.school_id', $schoolIds)
            )
            ->whereYear('attendances.marked_at', $ano)
            ->selectRaw("
                EXTRACT(MONTH FROM attendances.marked_at)::int AS mes,
                COUNT(*) AS total,
                SUM(CASE WHEN attendances.status = 'presente' THEN 1 ELSE 0 END) AS presentes
            ")
            ->groupByRaw('EXTRACT(MONTH FROM attendances.marked_at)')
            ->orderByRaw('mes ASC')
            ->get()
            ->keyBy('mes');
    }

    /**
     * @return Collection<int, object{ label: string, total: int, presentes: int }>
     */
    public function frequenciaPorTurma(int $ano, array $schoolIds = [], int $limit = 10): Collection
    {
        return DB::table('attendances')
            ->join('classes', 'attendances.class_id', '=', 'classes.id')
            ->join('schools', 'classes.school_id', '=', 'schools.id')
            ->when($schoolIds !== [], fn ($q) =>
                $q->whereIn('classes.school_id', $schoolIds)
            )
            ->whereYear('attendances.marked_at', $ano)
            ->selectRaw("
                classes.id,
                CONCAT(schools.name, ' — ', classes.name) AS label,
                COUNT(*) AS total,
                SUM(CASE WHEN attendances.status = 'presente' THEN 1 ELSE 0 END) AS presentes
            ")
            ->groupBy('classes.id', 'classes.name', 'schools.name')
            ->orderByRaw(
                'SUM(CASE WHEN attendances.status = ? THEN 1 ELSE 0 END)::float
                 / NULLIF(COUNT(*), 0) DESC',
                ['presente']
            )
            ->limit($limit)
            ->get();
    }

    // Donut

    /**
     * @return Collection<int, object{ id: int, name: string, total: int, presentes: int }>
     */
    public function frequenciaPorEscola(int $ano, array $schoolIds = []): Collection
    {
        return DB::table('attendances')
            ->join('classes', 'attendances.class_id', '=', 'classes.id')
            ->join('schools', 'classes.school_id', '=', 'schools.id')
            ->when($schoolIds !== [], fn ($q) =>
                $q->whereIn('classes.school_id', $schoolIds)
            )
            ->whereYear('attendances.marked_at', $ano)
            ->selectRaw("
                schools.id,
                schools.name,
                COUNT(*) AS total,
                SUM(CASE WHEN attendances.status = 'presente' THEN 1 ELSE 0 END) AS presentes
            ")
            ->groupBy('schools.id', 'schools.name')
            ->get();
    }

    // ranking

    /**
     * @return Collection<int, object{
     *   student_name: string, class_name: string,
     *   school_name: string, total: int, faltas: int
     * }>
     */
    public function topAlunasComFaltas(int $ano, array $schoolIds = [], int $limit = 5): Collection
    {
        return DB::table('attendances')
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->join('classes',  'attendances.class_id',  '=', 'classes.id')
            ->join('schools',  'classes.school_id',     '=', 'schools.id')
            ->when($schoolIds !== [], fn ($q) =>
                $q->whereIn('classes.school_id', $schoolIds)
            )
            ->whereYear('attendances.marked_at', $ano)
            ->selectRaw("
                students.name AS student_name,
                classes.name  AS class_name,
                schools.name  AS school_name,
                COUNT(*) AS total,
                SUM(CASE WHEN attendances.status = 'ausente' THEN 1 ELSE 0 END) AS faltas
            ")
            ->groupBy('students.id', 'students.name', 'classes.name', 'schools.name')
            ->orderByRaw(
                'SUM(CASE WHEN attendances.status = ? THEN 1 ELSE 0 END) DESC',
                ['ausente']
            )
            ->limit($limit)
            ->get();
    }



    /**
     *
     * @return Collection<int, int>
     */
    public function anosDisponiveis(array $schoolIds = []): Collection
    {
        return DB::table('attendances')
            ->join('classes', 'attendances.class_id', '=', 'classes.id')
            ->when($schoolIds !== [], fn ($q) =>
                $q->whereIn('classes.school_id', $schoolIds)
            )
            ->selectRaw('EXTRACT(YEAR FROM attendances.marked_at)::int AS ano')
            ->groupByRaw('EXTRACT(YEAR FROM attendances.marked_at)')
            ->orderByRaw('ano DESC')
            ->pluck('ano');
    }
}
