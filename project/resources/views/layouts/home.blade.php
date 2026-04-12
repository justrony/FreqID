@extends('layouts.app')

@section('custom_css')
    <style>
        body { background-color: var(--bg-light); overflow-x: hidden; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, var(--primary-color), var(--primary-color)); color: white; position: sticky; top: 0; z-index: 100; }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); transition: 0.3s; padding: 12px 20px; }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { background-color: rgba(255,255,255,0.15); color: white; transform: translateX(5px); border-left: 3px solid var(--yellow-id); }
        .brand-logo { font-weight: 700; padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .top-header { background: white; padding: 15px 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 99; }
        .action-card { border: none; border-radius: 12px; transition: 0.3s; cursor: pointer; height: 100%; border-left: 5px solid var(--accent-color); box-shadow: 0 10px 20px rgba(0,0,0,0.08); background-color: rgba(0,0,0,0);}
        .action-card:hover { transform: translateY(-5px);}
        .action-icon-box { color: var(--primary-color); background-color: #eef7fc; }
        .card-header-custom { background-color: white; color: var(--primary-color); }
    </style>
@endsection

@section('main')
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse p-0" id="sidebarMenu">
                <div class="position-sticky pt-3">
                    <div class="brand-logo text-center">
                        <img src="{{ asset('images/logo_prefeitura_menor.png') }}" alt="Logo" class="img-fluid mb-2" style="max-height: 60px;">
                        <br>
                        Sistema de Frequência Escolar
                        <div style="font-size: 0.7em; text-transform: uppercase; opacity: 0.8;">Secretaria de </div>
                        <span class="fs-5">Educação</span>
                    </div>
                    <ul class="nav flex-column mt-3">
                        <li class="nav-item"><a class="nav-link" href="{{route('relatorio.inicio')}}"><i class="bi bi-house-door"></i> Início</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('turmas') }}"><i class="bi bi-people"></i> Turmas</a></li>
                        @can('access-admin')
                            <li class="nav-item"><a class="nav-link" href="{{route('usuarios')}}"><i class="bi bi-person-gear"></i> Usuários</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('escolas') }}"><i class="bi bi-buildings"></i> Escolas</a></li>
                        @endcan
                    </ul>
                </div>
            </nav>

    @yield('content')

    <footer class="text-center pt-4">
        <p class="small text-muted">&copy; {{ date('Y') }} Sistema de Frequência Escolar - Prefeitura de Maracanaú</p>
    </footer>


@endsection