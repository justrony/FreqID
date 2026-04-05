@extends('layouts.home')
@section('title', 'Listagem de Turmas - FreqID')

@section('content')
    <main class="col-md-9 ms-sm-auto col-lg-10 p-0">
        <header class="top-header p-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-0 text-secondary fw-bold">Gerenciar Turmas</h5>
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('turma.create') }}" class="btn btn-warning text-white btn-sm d-flex align-items-center gap-2">
                        <i class="bi bi-plus-lg"></i>
                        <span>Nova Turma</span>
                    </a>
                </div>
            </div>
        </header>

        <div class="p-4" id="school-classes-list">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="schoolClassesTable" style="width:100%">
                            <thead class="bg-light text-secondary">
                            <tr>
                                <th scope="col" class="ps-4 py-3">Nome da Turma</th>
                                <th scope="col">Escola</th>
                                <th scope="col" class="text-end pe-4">Ações</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($schoolClasses as $schoolClass)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $schoolClass->name }}</div>
                                    </td>

                                    <td>
                                        <span class="text-muted">{{ $schoolClass->school->name ?? '—' }}</span>
                                    </td>

                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex gap-2">
                                            <a href="{{ route('turma.edit', $schoolClass) }}"
                                               class="btn btn-outline-primary btn-sm"
                                               title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <button type="button" class="btn btn-outline-danger btn-sm"
                                                    title="Delete"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#confirmDeleteSchoolClassModal"
                                                    data-school-class-id="{{ $schoolClass->id }}"
                                                    data-school-class-name="{{ $schoolClass->name }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="empty-table-row">
                                    <td colspan="3" class="text-center py-5 text-muted">
                                        <i class="bi bi-people fs-1 d-block mb-2"></i>
                                        Nenhuma turma cadastrada.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if(isset($schoolClasses) && $schoolClasses instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="card-footer bg-white border-0 py-3">
                        {{ $schoolClasses->links() }}
                    </div>
                @endif
            </div>
        </div>
    </main>

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="confirmDeleteSchoolClassModal" tabindex="-1" aria-labelledby="confirmDeleteSchoolClassModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="confirmDeleteSchoolClassModalLabel">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir a turma <strong id="modal-school-class-name"></strong>?</p>
                    <p class="text-danger small"><i class="bi bi-exclamation-triangle-fill me-1"></i> Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteSchoolClassForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-trash me-1"></i> Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            let deleteModal = $('#confirmDeleteSchoolClassModal');
            if (deleteModal.length) {
                deleteModal.on('show.bs.modal', function (event) {
                    let button = $(event.relatedTarget);
                    let schoolClassId   = button.data('school-class-id');
                    let schoolClassName = button.data('school-class-name');

                    let deleteUrl = '{{ route("turma.destroy", "SCHOOL_CLASS_ID") }}'.replace('SCHOOL_CLASS_ID', schoolClassId);

                    $(this).find('#deleteSchoolClassForm').attr('action', deleteUrl);
                    $(this).find('#modal-school-class-name').text(schoolClassName);
                });
            }

            if ($("#schoolClassesTable").length > 0 && $(".empty-table-row").length === 0) {
                $('#schoolClassesTable').DataTable({
                    retrieve: true,
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json',
                        search: "_INPUT_",
                        searchPlaceholder: "Buscar turmas..."
                    },
                    pageLength: 15,
                    ordering: true,
                    order: [[0, 'asc']],
                    columnDefs: [
                        { orderable: false, targets: 2 }
                    ],
                    dom: '<"d-flex justify-content-between align-items-center mb-3"f>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                });
            }
        });
    </script>
@endsection
