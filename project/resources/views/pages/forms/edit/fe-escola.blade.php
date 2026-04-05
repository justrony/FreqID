@extends('layouts.home')
@section('title', 'Editar Escola - FreqID')

@section('content')
    <main class="col-md-9 ms-sm-auto col-lg-10 p-0">
        <header class="top-header p-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-0 text-secondary fw-bold">Editar Escola: {{ $school->name }}</h5>
                <a href="{{ route('escola.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left"></i>
                    <span>Voltar para a Lista</span>
                </a>
            </div>
        </header>

        <div class="p-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <form action="{{ route('escola.update', $school) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h6 class="border-bottom pb-2 mb-4 text-primary">Informações da Escola</h6>

                        <div class="row g-3">

                            <div class="col-md-8">
                                <label for="name" class="form-label">Nome da Escola <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $school->name) }}"
                                       placeholder="Ex: Escola Municipal João da Silva"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="inep_code" class="form-label">Código INEP <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('inep_code') is-invalid @enderror"
                                       id="inep_code"
                                       name="inep_code"
                                       value="{{ old('inep_code', $school->inep_code) }}"
                                       placeholder="Ex: 31000123"
                                       maxlength="8"
                                       required>
                                @error('inep_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-muted">Código de 8 dígitos fornecido pelo INEP/MEC.</div>
                            </div>

                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-success d-flex align-items-center gap-2">
                                <i class="bi bi-arrow-clockwise"></i>
                                Atualizar Escola
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection
