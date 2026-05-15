<?php

namespace App\Http\Controllers;

use App\Exports\CsvDashboardExporter;
use App\Exports\JsonDashboardExporter;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class RelatorioController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboard
    ) {}

    public function grafico(Request $request)
    {
        $ano  = (int) $request->input('ano', now()->year);
        $user = auth()->user();

        $isSchoolUser  = $this->dashboard->isSchoolUser($user);
        $schoolIds     = $this->dashboard->resolveSchoolScope($user);
        $userSchools   = $this->dashboard->userSchoolNames($schoolIds);

        $kpis = $this->dashboard->kpis($ano, $schoolIds);

        $mensal  = $this->dashboard->graficoMensal($ano, $schoolIds);
        $turmas  = $this->dashboard->graficoTurmas($ano, $schoolIds);
        $escolas = $this->dashboard->graficoEscolas($ano, $schoolIds);

        $topFaltas       = $this->dashboard->topFaltas($ano, $schoolIds);
        $anosDisponiveis = $this->dashboard->anosDisponiveis($schoolIds);

        return view('pages.home.inicio', [
            'ano'             => $ano,
            'isSchoolUser'    => $isSchoolUser,
            'userSchools'     => $userSchools,
            'anosDisponiveis' => $anosDisponiveis,

            // KPIs
            'totalRegistros'  => $kpis['totalRegistros'],
            'totalPresentes'  => $kpis['totalPresentes'],
            'totalAusentes'   => $kpis['totalAusentes'],
            'taxaGeral'       => $kpis['taxaGeral'],
            'totalAlunos'     => $kpis['totalAlunos'],
            'totalTurmas'     => $kpis['totalTurmas'],
            'totalEscolas'    => $kpis['totalEscolas'],

            // grafico de linha
            'mesesLabels'     => $mensal['labels'],
            'mesesTaxas'      => $mensal['taxas'],
            'mesesPresentes'  => $mensal['presentes'],
            'mesesTotal'      => $mensal['totais'],

            // grafico de barras
            'turmaLabels'     => $turmas['labels'],
            'turmaTaxas'      => $turmas['taxas'],

            // grafico donut
            'escolaLabels'    => $escolas['labels'],
            'escolaTaxas'     => $escolas['taxas'],
            'escolaPresentes' => $escolas['presentes'],

            // ranking
            'topFaltas'       => $topFaltas,
        ]);
    }

    public function exportar(Request $request, string $format)
    {
        $ano = (int) $request->input('ano', now()->year);
        $user = auth()->user();
        $schoolIds = $this->dashboard->resolveSchoolScope($user);
        
        $kpis = $this->dashboard->kpis($ano, $schoolIds);

        // Uso do Template Method: chamamos o método abstrato 'export' através da instância correta.
        if ($format === 'csv') {
            $exporter = new CsvDashboardExporter();
            $content = $exporter->export($kpis);
            
            return response($content, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="dashboard_export.csv"',
            ]);
        } elseif ($format === 'json') {
            $exporter = new JsonDashboardExporter();
            $content = $exporter->export($kpis);
            
            return response($content, 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="dashboard_export.json"',
            ]);
        }

        abort(404, 'Formato de exportação não suportado');
    }
}
