@extends('layouts.home')
@section('title', 'Gestão de Usuários - ' . config('app.name'))

@section('content')

    <main class="col-md-9 ms-sm-auto col-lg-10 p-0">
        <header class="top-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-0 text-secondary fw-bold">Admin - Usuários</h5>
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('usuario.create') }}" class="btn btn-warning text-white btn-sm d-flex align-items-center gap-2">
                        <i class="bi bi-person-plus"></i>
                        <span>Novo Usuário</span>
                    </a>
                </div>
            </div>
        </header>

        <div class="p-4" id="usuarios-list">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="usuarioTable">
                            <thead class="bg-light text-secondary">
                            <tr>
                                <th scope="col" class="ps-4 py-3">Nome</th>
                                <th scope="col">E-mail</th>
                                <th scope="col" class="text-center">Status</th>
                                <th scope="col" class="text-end pe-4">Ações</th>
                            </tr>
                            </thead>
                            <tbody>

                            @forelse($users as $user)
                                <tr class="{{ $user->trashed() ? 'opacity-50' : '' }}">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3 d-none d-md-block">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <span class="fw-bold text-dark">{{ $user->name }}</span>
                                        </div>
                                    </td>

                                    <td>{{ $user->email }}</td>

                                    <td class="text-center">
                                        @if($user->trashed())
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Inativo</span>
                                        @else
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Ativo</span>
                                        @endif
                                    </td>

                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex gap-2">
                                            @if(!$user->trashed())
                                                <a href="{{ route('usuario.edit', $user) }}"
                                                   class="btn btn-outline-primary btn-sm"
                                                   title="Editar">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>

                                                <button type="button" class="btn btn-outline-danger btn-sm"
                                                        title="Inativar Usuário"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#confirmInactivateModal"
                                                        data-usuario-id="{{ $user->id }}"
                                                        data-usuario-nome="{{ $user->name }}">
                                                    <i class="bi bi-person-x"></i>
                                                </button>
                                            @else
                                                <form action="{{ route('usuario.restore', $user->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-outline-success btn-sm" title="Reativar Usuário">
                                                        <i class="bi bi-person-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="empty-table">
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-people fs-1 d-block mb-2"></i>
                                        Nenhum usuário cadastrado.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if(isset($users) && $users instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="card-footer bg-white border-0 py-3">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </div>
    </main>

    <div class="modal fade" id="confirmInactivateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold text-danger">Confirmar Inativação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p>Tem certeza que deseja inativar o acesso de <strong id="user-name-modal"></strong>?</p>
                    <p class="mb-0 small text-muted">Ele não poderá mais acessar o sistema até ser reativado.</p>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <form action="" id="inactivateForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger px-4">Inativar Usuário</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            let inactivateModal = $('#confirmInactivateModal');

            inactivateModal.on('show.bs.modal', function (event) {
                let button = $(event.relatedTarget);
                let usuarioId = button.data('usuario-id');
                let usuarioNome = button.data('usuario-nome');

                let inactivateUrl = '{{ route("usuario.destroy", "USUARIO_ID") }}';
                inactivateUrl = inactivateUrl.replace('USUARIO_ID', usuarioId);

                let inactivateForm = inactivateModal.find('#inactivateForm');
                inactivateForm.attr('action', inactivateUrl);

                let modalUsuarioNome = inactivateModal.find('#user-name-modal');
                modalUsuarioNome.text(usuarioNome);
            });

            if ($("#usuarioTable").length > 0 && $(".empty-table").length === 0) {
                $('#usuarioTable').DataTable({
                    retrieve: true,
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json',
                        search: "_INPUT_",
                        searchPlaceholder: "Buscar usuários..."
                    },
                    pageLength: 15,
                    ordering: true,
                    order: [[0, 'asc']],
                    columnDefs: [
                        { orderable: false, targets: 3 }
                    ],
                    dom: '<"d-flex justify-content-between align-items-center mb-3"f>rt<"d-flex justify-content-between align-items-center"ip>',
                });
            }
        });
    </script>
@endsection
