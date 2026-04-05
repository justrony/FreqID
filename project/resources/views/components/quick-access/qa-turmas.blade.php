@extends('layouts.home')

@section('content')
    <main class="col-md-9 ms-sm-auto col-lg-10 p-0">
        <header class="top-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-0 text-secondary fw-bold">Turmas</h5>
                @include('components.logged')
            </div>
        </header>

        <div class="p-4">
            <h6 class="text-muted text-uppercase mb-3 fw-bold small">Acesso Rápido</h6>

            <div class="row g-4 mb-5">
                <div class="col-md-6 col-lg-4">
                    <a href="{{route('turma.create')}}" class="card action-card p-4 text-decoration-none"
                       style="border-left-color: var(--secondary-color)">
                        <div class="action-icon-box mb-3 d-flex align-items-center justify-content-center rounded-circle"
                             style="width: 60px; height: 60px; color: var(--secondary-color)">
                            <i class="bi bi-plus-lg"></i>
                        </div>
                        <h5 class="card-title fw-bold text-dark">Cadastrar nova turma</h5>
                        <p class="card-text text-muted small">Criar um novo registro de turma.</p>
                    </a>
                </div>

                <div class="col-md-6 col-lg-4">
                    <a href="{{route('turma.index')}}" class="card action-card p-4 text-decoration-none"
                       style="border-left-color: var(--yellow-id);">
                        <div class="action-icon-box mb-3 d-flex align-items-center justify-content-center rounded-circle"
                             style="width: 60px; height: 60px; color: var(--yellow-id);">
                            <i class="bi bi-search"></i>
                        </div>
                        <h5 class="card-title fw-bold text-dark">Pesquisar Turma</h5>
                        <p class="card-text text-muted small">Consultar histórico de turmas.</p>
                    </a>
                </div>
            </div> </div> </main> @endsection
