@extends('layouts.home')
@section('title', 'Novo Usuário - ' . config('app.name'))

@section('content')
    <main class="col-md-9 ms-sm-auto col-lg-10 p-0">
        <header class="top-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-0 text-secondary fw-bold">Administração: Cadastrar Novo Usuário</h5>
                <a href="{{route('usuario.index')}}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left"></i>
                    <span>Voltar</span>
                </a>
            </div>
        </header>

        <div class="p-4">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <div class="bg-primary bg-opacity-10 d-inline-block p-3 rounded-circle mb-3">
                                    <i class="bi bi-person-plus text-primary fs-3"></i>
                                </div>
                                <h5 class="fw-bold">Registrar Novo Acesso</h5>
                            </div>

                            <form action="{{route('usuario.store')}}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label for="name" class="form-label small fw-bold text-muted">Nome</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                                        <input type="text"
                                               name="name"
                                               id="name"
                                               class="form-control bg-light border-start-0 ps-0 @error('name') is-invalid @enderror"
                                               placeholder="Digite o nome do usuário."
                                               value="{{ old('name') }}"
                                               required>
                                        @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="email" class="form-label small fw-bold text-muted">E-mail</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                                        <input type="email"
                                               name="email"
                                               id="email"
                                               class="form-control bg-light border-start-0 ps-0 @error('email') is-invalid @enderror"
                                               placeholder="email@exemplo.com"
                                               value="{{ old('email') }}"
                                               required>
                                        @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-text small">Senha padrão <strong>12345678</strong>. O usuário terá que alterá-la.</div>
                                </div>

                                <div class="mb-4">
                                    <label for="affiliation" class="form-label small fw-bold text-muted">Afiliação</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-building"></i></span>
                                        <select name="affiliation"
                                                id="affiliation"
                                                class="form-select bg-light border-start-0 @error('affiliation') is-invalid @enderror"
                                                required>
                                            <option value="" disabled {{ old('affiliation') ? '' : 'selected' }}>Selecione a afiliação...</option>
                                            <option value="seduc" {{ old('affiliation') === 'seduc' ? 'selected' : '' }}>Secretaria de Educação (SEDUC)</option>
                                            <option value="school" {{ old('affiliation') === 'school' ? 'selected' : '' }}>Escola</option>
                                        </select>
                                        @error('affiliation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary py-2 fw-bold">
                                        <i class="bi bi-check-lg me-2"></i>
                                        Cadastrar Usuário
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
