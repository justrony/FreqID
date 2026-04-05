<style>
    .dropdown button {
        font-size: 18px;
        line-height: 1;
        padding: 2px 6px;
    }
</style>
<div class="d-flex align-items-center gap-3">
    <div class="d-none d-lg-block text-end">

        <small class="d-block text-muted">
            {{ \Carbon\Carbon::now()->translatedFormat('l, d \d\e F \d\e Y') }}
        </small>

        <div class="d-flex align-items-center gap-2 justify-content-end">
            <span class="fw-bold text-dark">
                Olá, {{ auth()->user()->name ?? 'Usuário' }}
            </span>

            <div class="dropdown">
                <button
                    class="btn btn-sm btn-light border-0"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    title="Menu do usuário"
                >
                    &#8942;
                </button>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                                <i class="bi bi-box-arrow-right"></i>
                                Logout
                            </button>
                        </form>
                    </li>
                </ul>

            </div>
        </div>
    </div>
</div>



