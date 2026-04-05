@extends('layouts.home')
@section('title', 'Nova Turma - FreqID')

@section('content')
    <main class="col-md-9 ms-sm-auto col-lg-10 p-0">
        <header class="top-header p-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-0 text-secondary fw-bold">Cadastro de Nova Turma</h5>
                <a href="{{ route('turma.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left"></i>
                    <span>Voltar para a Lista</span>
                </a>
            </div>
        </header>

        <div class="p-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <form action="{{ route('turma.store') }}" method="POST">
                        @csrf

                        <h6 class="border-bottom pb-2 mb-4" style="color: var(--primary-color)">Informações da Turma</h6>

                        <div class="row g-3">

                            <div class="col-md-8">
                                <label for="name" class="form-label">Nome da Turma <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name') }}"
                                       placeholder="Ex: 1º Ano A"
                                       required>
                                @error('name')
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
                                        <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                                            {{ $school->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('school_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                                <i class="bi bi-save"></i>
                                Cadastrar Turma
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection
