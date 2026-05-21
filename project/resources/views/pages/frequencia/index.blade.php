@extends('layouts.home')
@section('title', 'Registro de Frequência - FreqID')

@section('custom_css')
<style>
    .turma-card {
        border: none;
        border-radius: 16px;
        border-left: 5px solid var(--accent-color);
        box-shadow: 0 4px 18px rgba(0,0,0,0.07);
        transition: transform .2s, box-shadow .2s;
        cursor: pointer;
        text-decoration: none;
        color: inherit;
        display: block;
    }
    .turma-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 28px rgba(0,0,0,0.13);
        color: inherit;
    }
    .turma-icon {
        width: 52px; height: 52px;
        border-radius: 50%;
        background: #eff6ff;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem;
        color: var(--primary-color);
        flex-shrink: 0;
    }
    .empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
    .empty-state i { font-size: 4rem; margin-bottom: 16px; display: block; }
</style>
@endsection

@section('content')
<main class="col-md-9 ms-sm-auto col-lg-10 p-0">
    <header class="top-header p-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="m-0 fw-bold" style="color:var(--primary-color)">
                    <i class="bi bi-camera-video me-2"></i>Registro de Frequência
                </h5>
                <small class="text-muted">Selecione a turma para iniciar o registro de presença</small>
            </div>
            <span class="badge bg-secondary px-3 py-2">{{ $schoolClasses->count() }} turma(s)</span>
        </div>
    </header>

    <div class="p-4">
        @if($schoolClasses->isEmpty())
            <div class="empty-state">
                <i class="bi bi-people"></i>
                <h5 class="fw-semibold">Nenhuma turma encontrada</h5>
                <p class="small">Você ainda não possui turmas vinculadas à sua escola.</p>
            </div>
        @else
            <div class="row g-4">
                @foreach($schoolClasses as $turma)
                <div class="col-md-6 col-lg-4">
                    <a href="{{ route('frequencia.show', $turma) }}" class="turma-card">
                        <div class="card-body p-4 d-flex gap-3 align-items-center">
                            <div class="turma-icon">
                                <i class="bi bi-person-video2"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <h6 class="fw-bold mb-1 text-truncate">{{ $turma->name }}</h6>
                                <div class="small text-muted text-truncate">
                                    <i class="bi bi-buildings me-1"></i>{{ $turma->school->name ?? '—' }}
                                </div>
                            </div>
                            <i class="bi bi-chevron-right text-muted flex-shrink-0"></i>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</main>
@endsection
