@extends('layouts.home')
@section('title', 'Editar Usuário - ' . config('app.name'))

@section('content')
    <main class="col-md-9 ms-sm-auto col-lg-10 p-0">
        <header class="top-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-0 text-secondary fw-bold">Administração: Editar Usuário</h5>
                <a href="{{ route('usuario.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
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
                                    <i class="bi bi-pencil-square text-primary fs-3"></i>
                                </div>
                                <h5 class="fw-bold">Atualizar Dados de Acesso</h5>
                                <p class="text-muted small">Modifique as informações básicas de <strong>{{ $user->name }}</strong>.</p>
                            </div>

                            <form action="{{ route('usuario.update', $user) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="name" class="form-label small fw-bold text-muted">Nome Completo</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                                        <input type="text"
                                               name="name"
                                               id="name"
                                               class="form-control bg-light border-start-0 ps-0 @error('name') is-invalid @enderror"
                                               value="{{ old('name', $user->name) }}"
                                               required>
                                        @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="email" class="form-label small fw-bold text-muted">E-mail Institucional</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                                        <input type="email"
                                               name="email"
                                               id="email"
                                               class="form-control bg-light border-start-0 ps-0 @error('email') is-invalid @enderror"
                                               value="{{ old('email', $user->email) }}"
                                               required>
                                        @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="affiliation" class="form-label small fw-bold text-muted">Afiliação</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-buildings"></i></span>
                                        <select name="affiliation"
                                                id="affiliation"
                                                class="form-select bg-light border-start-0 @error('affiliation') is-invalid @enderror"
                                                required>
                                            <option value="" disabled {{ old('affiliation', $user->affiliation) ? '' : 'selected' }}>Selecione a afiliação...</option>
                                            <option value="seduc" {{ old('affiliation', $user->affiliation) === 'seduc' ? 'selected' : '' }}>Secretaria de Educação (SEDUC)</option>
                                            <option value="school" {{ old('affiliation', $user->affiliation) === 'school' ? 'selected' : '' }}>Escola</option>
                                        </select>
                                        @error('affiliation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="mb-4 p-3 bg-light rounded border border-dashed">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="reset_password" id="reset_password">
                                        <label class="form-check-label fw-bold text-secondary small" for="reset_password">
                                            Redefinir para senha padrão: (12345678)
                                        </label>
                                    </div>
                                    <p class="mb-0 x-small text-muted mt-1">Marque esta opção se o utilizador esqueceu a senha atual.</p>
                                </div>

                                <div class="mb-4">
                                    <label for="school_ids" class="form-label small fw-bold text-muted">Escolas Vinculadas</label>
                                    <select name="school_ids[]" id="school_ids" class="form-select bg-light @error('school_ids') is-invalid @enderror" multiple>
                                        @foreach($schools as $school)
                                            <option value="{{ $school->id }}"
                                                {{ in_array($school->id, old('school_ids', $linkedSchools)) ? 'selected' : '' }}>
                                                {{ $school->name }} — {{ $school->inep_code }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text small">Segure Ctrl (ou Cmd) para selecionar mais de uma escola.</div>
                                    @error('school_ids')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary py-2 fw-bold">
                                        <i class="bi bi-save me-2"></i>
                                        Salvar Alterações
                                    </button>
                                    <a href="{{ route('usuario.index') }}" class="btn btn-link btn-sm text-muted text-decoration-none">Cancelar</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <style>
        .border-dashed { border-style: dashed !important; }
        .x-small { font-size: 0.75rem; }
    </style>
@endsection
