@extends('layouts.home')
@section('title', 'Dashboard de Frequência Escolar')

@section('content')
<main class="col-md-9 ms-sm-auto col-lg-10 p-0">

    {{-- ════ HEADER ════ --}}
    <header class="top-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="m-0 fw-bold" style="color: var(--primary-color)">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard de Frequência
                </h5>
                <p class="m-0 small text-muted">
                    @if($isSchoolUser)
                        <i class="bi bi-buildings me-1"></i>{{ $userSchools->join(' · ') }} · Ano letivo {{ $ano }}
                    @else
                        <i class="bi bi-globe me-1"></i>Visão geral · Secretaria de Educação · Ano letivo {{ $ano }}
                    @endif
                </p>
            </div>
            <div class="d-flex align-items-center gap-3">
                {{-- Filtro de ano --}}
                <form action="{{ route('relatorio.inicio') }}" method="GET" class="d-flex align-items-center gap-2">
                    <select name="ano" class="form-select form-select-sm shadow-sm" onchange="this.form.submit()" style="min-width:90px">
                        @foreach($anosDisponiveis as $a)
                            <option value="{{ $a }}" {{ $a == $ano ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                        @if($anosDisponiveis->isEmpty())
                            <option value="{{ $ano }}">{{ $ano }}</option>
                        @endif
                    </select>
                </form>
                @include('components.logged')
            </div>
        </div>
    </header>

    <div class="p-4" style="background: #f0f4f8; min-height: calc(100vh - 61px);">

        {{-- ════ BANNER CONTEXTO (só para usuário de escola) ════ --}}
        @if($isSchoolUser)
        <div class="alert d-flex align-items-center gap-3 mb-4 border-0 shadow-sm" style="background:linear-gradient(135deg,#0176bc15,#0176bc08);border-left:4px solid #0176bc !important;border-radius:12px">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:38px;height:38px;background:#0176bc15">
                <i class="bi bi-funnel-fill" style="color:#0176bc"></i>
            </div>
            <div>
                <div class="fw-bold" style="color:#0176bc;font-size:.85rem">Dados filtrados por escola</div>
                <div class="text-muted" style="font-size:.78rem">Você está vendo apenas os dados de: <strong>{{ $userSchools->join(', ') }}</strong></div>
            </div>
        </div>
        @endif

        {{-- ════ KPI ROW ════ --}}
        <div class="row g-3 mb-4">

            {{-- Taxa Geral --}}
            <div class="col-6 col-md-4 col-xl-2">
                <div class="kpi-card h-100" style="--kpi-accent:#0176bc">
                    <div class="kpi-icon"><i class="bi bi-graph-up-arrow"></i></div>
                    <div class="kpi-value" id="kpiTaxa">{{ $taxaGeral }}%</div>
                    <div class="kpi-label">Taxa de Presença</div>
                </div>
            </div>

            {{-- Presenças --}}
            <div class="col-6 col-md-4 col-xl-2">
                <div class="kpi-card h-100" style="--kpi-accent:#22c55e">
                    <div class="kpi-icon"><i class="bi bi-person-check-fill"></i></div>
                    <div class="kpi-value">{{ number_format($totalPresentes) }}</div>
                    <div class="kpi-label">Registros Presentes</div>
                </div>
            </div>

            {{-- Ausências --}}
            <div class="col-6 col-md-4 col-xl-2">
                <div class="kpi-card h-100" style="--kpi-accent:#ef4444">
                    <div class="kpi-icon"><i class="bi bi-person-x-fill"></i></div>
                    <div class="kpi-value">{{ number_format($totalAusentes) }}</div>
                    <div class="kpi-label">Registros Ausentes</div>
                </div>
            </div>

            {{-- Alunos --}}
            <div class="col-6 col-md-4 col-xl-2">
                <div class="kpi-card h-100" style="--kpi-accent:#8b5cf6">
                    <div class="kpi-icon"><i class="bi bi-people-fill"></i></div>
                    <div class="kpi-value">{{ number_format($totalAlunos) }}</div>
                    <div class="kpi-label">Total de Alunos</div>
                </div>
            </div>

            {{-- Turmas --}}
            <div class="col-6 col-md-4 col-xl-2">
                <div class="kpi-card h-100" style="--kpi-accent:#f59e0b">
                    <div class="kpi-icon"><i class="bi bi-grid-3x3-gap-fill"></i></div>
                    <div class="kpi-value">{{ $totalTurmas }}</div>
                    <div class="kpi-label">Turmas Ativas</div>
                </div>
            </div>

            {{-- Escolas --}}
            <div class="col-6 col-md-4 col-xl-2">
                <div class="kpi-card h-100" style="--kpi-accent:#06b6d4">
                    <div class="kpi-icon"><i class="bi bi-buildings-fill"></i></div>
                    <div class="kpi-value">{{ $totalEscolas }}</div>
                    <div class="kpi-label">Escolas</div>
                </div>
            </div>
        </div>

        {{-- ════ ROW 2: Linha temporal + Donut por Escola ════ --}}
        <div class="row g-3 mb-4">

            {{-- Evolução mensal --}}
            <div class="col-lg-8">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <span><i class="bi bi-calendar3 me-2 text-primary"></i>Evolução Mensal da Frequência</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold">{{ $ano }}</span>
                    </div>
                    <div class="dash-card-body">
                        <div style="position:relative;height:260px">
                            <canvas id="chartLinha"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Donut por escola --}}
            <div class="col-lg-4">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <span><i class="bi bi-buildings me-2 text-info"></i>{{ $isSchoolUser ? 'Frequência da Escola' : 'Taxa por Escola' }}</span>
                    </div>
                    <div class="dash-card-body d-flex flex-column">
                        <div class="d-flex justify-content-center mb-3">
                            <div style="position:relative;height:160px;width:160px;flex-shrink:0">
                                <canvas id="chartDonut"></canvas>
                            </div>
                        </div>
                        <div id="escolaLegenda"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ════ ROW 3: Barras por turma + Top faltas ════ --}}
        <div class="row g-3">

            {{-- Ranking de turmas --}}
            <div class="col-lg-8">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <span><i class="bi bi-bar-chart-fill me-2 text-warning"></i>Frequência por Turma (Top 10)</span>
                        <span class="badge" style="background:#f59e0b20;color:#f59e0b">% presença</span>
                    </div>
                    <div class="dash-card-body">
                        <div style="position:relative;height:280px">
                            <canvas id="chartBarras"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Top 5 alunos com mais faltas --}}
            <div class="col-lg-4">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <span><i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i>Alertas de Faltas</span>
                        <span class="badge bg-danger bg-opacity-10 text-danger">Top 5</span>
                    </div>
                    <div class="dash-card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse($topFaltas as $i => $aluno)
                                @php
                                    $taxa = $aluno->total > 0 ? round($aluno->faltas / $aluno->total * 100) : 0;
                                    $cor  = $taxa >= 30 ? '#ef4444' : ($taxa >= 20 ? '#f59e0b' : '#22c55e');
                                @endphp
                                <li class="list-group-item border-0 px-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rank-badge" style="background: {{ $cor }}20; color:{{ $cor }}">
                                            {{ $i + 1 }}
                                        </div>
                                        <div class="flex-grow-1 min-width-0">
                                            <div class="fw-semibold text-truncate" style="font-size:.85rem">{{ $aluno->student_name }}</div>
                                            <div class="text-muted" style="font-size:.73rem">{{ $aluno->class_name }} · {{ $aluno->school_name }}</div>
                                            <div class="progress mt-1" style="height:4px;border-radius:4px">
                                                <div class="progress-bar" role="progressbar"
                                                    style="width:{{ $taxa }}%; background:{{ $cor }}; border-radius:4px"
                                                    aria-valuenow="{{ $taxa }}" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="fw-bold" style="color:{{ $cor }};font-size:.9rem;white-space:nowrap">
                                            {{ $taxa }}% faltas
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-center text-muted py-5">
                                    <i class="bi bi-emoji-smile fs-3 d-block mb-2"></i>
                                    Nenhuma falta registrada!
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    /* ── KPI Cards ─────────────────────────── */
    .kpi-card {
        background: white;
        border-radius: 16px;
        padding: 20px 18px;
        box-shadow: 0 2px 12px rgba(0,0,0,.07);
        border-top: 4px solid var(--kpi-accent);
        transition: transform .2s, box-shadow .2s;
    }
    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,.12);
    }
    .kpi-icon {
        font-size: 1.5rem;
        color: var(--kpi-accent);
        margin-bottom: 8px;
    }
    .kpi-value {
        font-size: 1.7rem;
        font-weight: 800;
        color: #1e293b;
        line-height: 1;
        margin-bottom: 4px;
    }
    .kpi-label {
        font-size: .72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #94a3b8;
    }

    /* ── Dash Cards ────────────────────────── */
    .dash-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,.07);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .dash-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px;
        border-bottom: 1px solid #f1f5f9;
        font-size: .85rem;
        font-weight: 700;
        color: #334155;
    }
    .dash-card-body {
        padding: 20px;
        flex: 1;
    }

    /* ── Rank Badge ────────────────────────── */
    .rank-badge {
        width: 30px; height: 30px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: .8rem;
        flex-shrink: 0;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Paleta de cores ─────────────────────────────────────────
    const palette  = ['#0176bc','#22c55e','#8b5cf6','#f59e0b','#ef4444','#06b6d4','#ec4899','#14b8a6'];
    const grid     = { color: 'rgba(0,0,0,.05)' };
    const fontDef  = { family: "'Inter', sans-serif", size: 12 };

    Chart.defaults.font = fontDef;

    // ─────────────────────────────────────────────────────────────
    // 1. Gráfico de LINHA — frequência mensal
    // ─────────────────────────────────────────────────────────────
    const linhaCtx    = document.getElementById('chartLinha').getContext('2d');
    const mesesLabels = @json($mesesLabels);
    const mesesTaxas  = @json($mesesTaxas);
    const mesesPresentes = @json($mesesPresentes);
    const mesesTotal  = @json($mesesTotal);

    const gradient = linhaCtx.createLinearGradient(0, 0, 0, 260);
    gradient.addColorStop(0,   'rgba(1, 118, 188, .35)');
    gradient.addColorStop(1,   'rgba(1, 118, 188, .0)');

    new Chart(linhaCtx, {
        type: 'line',
        data: {
            labels: mesesLabels,
            datasets: [{
                label: 'Taxa de Presença (%)',
                data: mesesTaxas,
                borderColor: '#0176bc',
                backgroundColor: gradient,
                borderWidth: 3,
                pointBackgroundColor: '#0176bc',
                pointRadius: 5,
                pointHoverRadius: 7,
                tension: 0.4,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y}% de presença`,
                        afterLabel: (ctx) => {
                            const i = ctx.dataIndex;
                            return `  ${mesesPresentes[i].toLocaleString('pt-BR')} de ${mesesTotal[i].toLocaleString('pt-BR')} registros`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    min: 0, max: 100,
                    grid,
                    ticks: { callback: v => v + '%' }
                },
                x: { grid: { display: false } }
            }
        }
    });

    // ─────────────────────────────────────────────────────────────
    // 2. Gráfico DONUT — taxa por escola
    // ─────────────────────────────────────────────────────────────
    const donutCtx       = document.getElementById('chartDonut').getContext('2d');
    const escolaLabels   = @json($escolaLabels);
    const escolaTaxas    = @json($escolaTaxas);
    const escolaPresentes= @json($escolaPresentes);

    new Chart(donutCtx, {
        type: 'doughnut',
        data: {
            labels: escolaLabels,
            datasets: [{
                data: escolaTaxas,
                backgroundColor: palette.slice(0, escolaLabels.length),
                borderWidth: 0,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}%` }
                }
            }
        },
        plugins: [{
            id: 'centerText',
            afterDraw(chart) {
                const { ctx, chartArea: { left, top, width, height } } = chart;
                const cx = left + width / 2;
                const cy = top  + height / 2;
                ctx.save();
                ctx.textAlign    = 'center';
                ctx.textBaseline = 'middle';

                const avg = escolaTaxas.length
                    ? (escolaTaxas.reduce((a, b) => a + b, 0) / escolaTaxas.length).toFixed(1)
                    : 0;

                ctx.font      = `bold ${Math.round(width * 0.14)}px Inter, sans-serif`;
                ctx.fillStyle = '#1e293b';
                ctx.fillText(avg + '%', cx, cy - Math.round(height * 0.07));

                ctx.font      = `${Math.round(width * 0.08)}px Inter, sans-serif`;
                ctx.fillStyle = '#94a3b8';
                ctx.fillText('Média geral', cx, cy + Math.round(height * 0.1));
                ctx.restore();
            }
        }]
    });

    // Legenda customizada do donut
    const legEl = document.getElementById('escolaLegenda');
    escolaLabels.forEach((label, i) => {
        legEl.innerHTML += `
            <div class="d-flex align-items-center justify-content-between mb-1">
                <div class="d-flex align-items-center gap-2">
                    <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${palette[i]}"></span>
                    <span style="font-size:.75rem;color:#475569">${label}</span>
                </div>
                <span style="font-size:.78rem;font-weight:700;color:#1e293b">${escolaTaxas[i]}%</span>
            </div>`;
    });

    // ─────────────────────────────────────────────────────────────
    // 3. Gráfico de BARRAS — ranking de turmas
    // ─────────────────────────────────────────────────────────────
    const barCtx     = document.getElementById('chartBarras').getContext('2d');
    const turmaLabels = @json($turmaLabels);
    const turmaTaxas  = @json($turmaTaxas);

    // Cores baseadas no desempenho
    const barColors = turmaTaxas.map(v =>
        v >= 90 ? '#22c55e' :
        v >= 80 ? '#0176bc' :
        v >= 70 ? '#f59e0b' : '#ef4444'
    );

    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: turmaLabels,
            datasets: [{
                label: 'Presença (%)',
                data: turmaTaxas,
                backgroundColor: barColors,
                borderRadius: 8,
                hoverBackgroundColor: barColors.map(c => c + 'cc'),
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: { label: ctx => ` ${ctx.parsed.x}% de presença` }
                }
            },
            scales: {
                x: {
                    min: 0, max: 100,
                    grid,
                    ticks: { callback: v => v + '%' }
                },
                y: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });
});
</script>
@endsection
