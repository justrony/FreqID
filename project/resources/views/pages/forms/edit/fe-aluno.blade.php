@extends('layouts.home')
@section('title', 'Editar Aluno - FreqID')

@section('content')
    <main class="col-md-9 ms-sm-auto col-lg-10 p-0">
        <header class="top-header p-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-0 text-secondary fw-bold">Editar Aluno</h5>
                <a href="{{ route('aluno.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left"></i>
                    <span>Voltar para a Lista</span>
                </a>
            </div>
        </header>

        <div class="p-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <form action="{{ route('aluno.update', $aluno) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h6 class="border-bottom pb-2 mb-4" style="color: var(--primary-color)">Informações do Aluno</h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $aluno->name) }}"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="registration" class="form-label">Matrícula <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('registration') is-invalid @enderror"
                                       id="registration"
                                       name="registration"
                                       value="{{ old('registration', $aluno->registration) }}"
                                       required>
                                @error('registration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="cpf" class="form-label">CPF <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('cpf') is-invalid @enderror"
                                       id="cpf"
                                       name="cpf"
                                       value="{{ old('cpf', $aluno->cpf) }}"
                                       required>
                                @error('cpf')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="data_nascimento" class="form-label">Data de Nascimento <span class="text-danger">*</span></label>
                                <input type="date"
                                       class="form-control @error('data_nascimento') is-invalid @enderror"
                                       id="data_nascimento"
                                       name="data_nascimento"
                                       value="{{ old('data_nascimento', optional($aluno->data_nascimento)->format('Y-m-d')) }}"
                                       required>
                                @error('data_nascimento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="school_id" class="form-label">Escola <span class="text-danger">*</span></label>
                                <select class="form-select @error('school_id') is-invalid @enderror"
                                        id="school_id"
                                        name="school_id"
                                        required>
                                    <option value="">Selecione uma Escola</option>
                                    @foreach($schools as $school)
                                        <option value="{{ $school->id }}" {{ old('school_id', $aluno->school_id) == $school->id ? 'selected' : '' }}>
                                            {{ $school->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('school_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-5">
                                <label for="class_id" class="form-label">Turma <span class="text-danger">*</span></label>
                                <select class="form-select @error('class_id') is-invalid @enderror"
                                        id="class_id"
                                        name="class_id"
                                        required>
                                    <option value="">Selecione uma Turma</option>
                                    @foreach($schoolClasses as $class)
                                        <option value="{{ $class->id }}" {{ old('class_id', $aluno->class_id) == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('class_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="nome_mae" class="form-label">Nome da Mãe <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('nome_mae') is-invalid @enderror"
                                       id="nome_mae"
                                       name="nome_mae"
                                       value="{{ old('nome_mae', $aluno->nome_mae) }}"
                                       required>
                                @error('nome_mae')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="nome_pai" class="form-label">Nome do Pai</label>
                                <input type="text"
                                       class="form-control @error('nome_pai') is-invalid @enderror"
                                       id="nome_pai"
                                       name="nome_pai"
                                       value="{{ old('nome_pai', $aluno->nome_pai) }}">
                                @error('nome_pai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                                <i class="bi bi-save"></i>
                                Atualizar Aluno
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection
