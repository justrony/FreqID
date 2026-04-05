@extends('layouts.app')

@section('title', 'Login - ' . config('app.name'))

@section('main')
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="row w-100 justify-content-center">
            <div class="col-md-5 col-lg-4">

                <div class="text-center mb-4">
                    <img src="{{ asset('images/LOGO_EDUCAÇÃO.png') }}" alt="Logo" class="img-fluid mb-2" style="max-height: 80px;">
                    <h4 class="fw-bold" style="color: var(--primary-color)">Sistema de Frequência Escolar</h4>
                </div>

                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-body p-5">
                        @if ($errors->any())
                            <div class="alert alert-danger d-flex align-items-center p-2 mb-4" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div class="small">
                                    {{ $errors->first() }}
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login.post') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label small fw-bold text-muted">E-mail</label>
                                <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                    <input type="email"
                                           id="email"
                                           name="email"
                                           class="form-control bg-light border-start-0 ps-0 @error('email') is-invalid @enderror"
                                           value="{{ old('email') }}"
                                           placeholder="Digite seu email"
                                           required
                                           autofocus>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label small fw-bold text-muted">Password</label>
                                <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted">
                                    <i class="bi bi-lock"></i>
                                </span>
                                    <input type="password"
                                           id="password"
                                           name="password"
                                           class="form-control bg-light border-start-0 ps-0"
                                           placeholder="Digite sua Senha"
                                           required>
                                </div>
                            </div>


                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary py-2 fw-bold shadow-sm rounded-3">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Acessar Sistema
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-4 text-muted small">
                    &copy; {{ date('Y') }} Prefeitura de Maracanaú
                </div>

            </div>
        </div>
    </div>

    <style>
        .card {
            transition: transform 0.3s ease;
            background-color: rgba(0,0,0,0);
        }
        .input-group-text {
            border-color: #dee2e6;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #dee2e6;
            background-color: #fff !important;
        }

    </style>
@endsection
