@extends('layouts.home')
@section('title', 'Cadastro Biométrico - FreqID')

@section('content')
    <main class="col-md-9 ms-sm-auto col-lg-10 p-0">
        <header class="top-header p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="m-0 fw-bold" style="color: var(--primary-color)">
                        <i class="bi bi-person-bounding-box me-2"></i>Cadastro Biométrico Facial
                    </h5>
                    <small class="text-muted">Selecione o aluno para capturar a biometria</small>
                </div>
                <span class="badge bg-secondary">{{ $students->count() }} aluno(s)</span>
            </div>
        </header>

        <div class="p-4">

            {{-- Alertas de sessão --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Legenda --}}
            <div class="d-flex gap-3 mb-3">
                <span class="badge rounded-pill" style="background-color: #198754; font-weight: 400; padding: 6px 12px;">
                    <i class="bi bi-check-circle me-1"></i> Biometria cadastrada
                </span>
                <span class="badge rounded-pill" style="background-color: #6c757d; font-weight: 400; padding: 6px 12px;">
                    <i class="bi bi-camera me-1"></i> Aguardando captura
                </span>
            </div>

            {{-- Tabela de alunos --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="biometricTable" style="width:100%">
                            <thead class="bg-light text-secondary">
                                <tr>
                                    <th class="ps-4 py-3">Aluno</th>
                                    <th>Turma</th>
                                    <th>Escola</th>
                                    <th class="text-center">Biometria</th>
                                    <th class="text-end pe-4">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($students as $student)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                                 style="width:36px;height:36px;background:{{ $student->face_feature_exists ? '#d1fae5' : '#f1f5f9' }};flex-shrink:0">
                                                <i class="bi {{ $student->face_feature_exists ? 'bi-person-check-fill text-success' : 'bi-person text-secondary' }}"></i>
                                            </div>
                                            <span class="fw-semibold text-dark">{{ $student->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ $student->schoolClass->name ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ $student->school->name ?? '—' }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($student->face_feature_exists)
                                            <span class="badge bg-success rounded-pill px-3">
                                                <i class="bi bi-check2 me-1"></i>Cadastrado
                                            </span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill px-3">
                                                <i class="bi bi-dash me-1"></i>Pendente
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('biometric.show', $student->id) }}"
                                           class="btn btn-sm {{ $student->face_feature_exists ? 'btn-outline-primary' : 'btn-primary' }} d-inline-flex align-items-center gap-1">
                                            <i class="bi {{ $student->face_feature_exists ? 'bi-arrow-repeat' : 'bi-camera-fill' }}"></i>
                                            {{ $student->face_feature_exists ? 'Recapturar' : 'Capturar' }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-people fs-1 d-block mb-2"></i>
                                        Nenhum aluno encontrado nas suas turmas.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        if ($("#biometricTable").length && $("tbody tr").length > 1) {
            $('#biometricTable').DataTable({
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json', search: "_INPUT_", searchPlaceholder: "Buscar aluno..." },
                pageLength: 20,
                order: [[0, 'asc']],
                columnDefs: [{ orderable: false, targets: [3, 4] }],
                dom: '<"d-flex justify-content-between align-items-center mb-3"f>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
            });
        }
    });
</script>
@endsection
