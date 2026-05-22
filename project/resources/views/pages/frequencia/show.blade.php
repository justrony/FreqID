@extends('layouts.home')
@section('title', 'Registrar Frequência — FreqID')

@section('custom_css')
<style>
    /* ── Camera panel ─────────────────────────────────────────── */
    .camera-wrapper {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        background: #0f172a;
        width: 100%;
        aspect-ratio: 4/3;
    }
    #cameraStream {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    #cameraOffline {
        display: none;
        position: absolute;
        inset: 0;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        color: #94a3b8;
        gap: 8px;
    }
    #cameraOffline i { font-size: 3rem; }

    /* ── Live badge ───────────────────────────────────────────── */
    /*.live-badge {*/
    /*    position: absolute;*/
    /*    top: 14px; left: 14px;*/
    /*    background: rgba(239,68,68,.9);*/
    /*    color: #fff;*/
    /*    font-size: .68rem;*/
    /*    font-weight: 700;*/
    /*    letter-spacing: 1px;*/
    /*    padding: 4px 10px;*/
    /*    border-radius: 20px;*/
    /*    display: flex;*/
    /*    align-items: center;*/
    /*    gap: 6px;*/
    /*    z-index: 5;*/
    /*}*/
    /*.live-dot {*/
    /*    width: 8px; height: 8px;*/
    /*    background: #fff;*/
    /*    border-radius: 50%;*/
    /*    animation: blink 1s infinite;*/
    /*}*/
    /*@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.2} }*/

    /* ── Result panel ─────────────────────────────────────────── */
    #resultPanel {
        border-radius: 12px;
        padding: 16px 20px;
        display: none;
        animation: fadeUp .25s ease;
    }
    @keyframes fadeUp { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }

    /* ── Session list ─────────────────────────────────────────── */
    .session-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
        animation: fadeUp .25s ease;
    }
    .session-item:last-child { border-bottom: none; }
    .session-avatar {
        width: 38px; height: 38px;
        border-radius: 50%;
        background: #eff6ff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .85rem;
        font-weight: 700;
        color: var(--primary-color);
        flex-shrink: 0;
    }

    /* ── Button ───────────────────────────────────────────────── */
    #btnCapture {
        padding: 13px 40px;
        border-radius: 50px;
        font-size: 1rem;
        font-weight: 600;
        transition: .2s;
    }
    #btnCapture:active { transform: scale(.97); }
    #btnCapture:disabled { opacity: .6; cursor: not-allowed; }

    /* ── Status spinner overlay ───────────────────────────────── */
    .capture-loading {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,.45);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        border-radius: 16px;
        display: none;
    }
    .capture-loading .spinner-border { width: 3rem; height: 3rem; color: #fff; }
</style>
@endsection

@section('content')
<main class="col-md-9 ms-sm-auto col-lg-10 p-0">
    <header class="top-header p-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="m-0 fw-bold" style="color:var(--primary-color)">
                    <i class="bi bi-camera-video-fill me-2"></i>Registro de Frequência
                </h5>
                <small class="text-muted">Posicione o aluno em frente à câmera e clique em capturar</small>
            </div>
        </div>
    </header>

    <div class="p-4">
        <div class="row g-4">

            {{-- Câmera ────────────────────────────────────────────── --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-3">
                        <div class="camera-wrapper mb-3">

                            <div class="capture-loading" id="captureLoading">
                                <div class="spinner-border"></div>
                            </div>

                            <img id="cameraStream"
                                 src="http://localhost:8001/stream"
                                 alt="Câmera ao vivo">

                            <div id="cameraOffline">
                                <i class="bi bi-camera-video-off-fill"></i>
                                <span class="fw-semibold small">Câmera indisponível</span>
                                <span class="text-muted" style="font-size:.75rem">Verifique se o servidor Python está rodando.</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center">
                            <button id="btnCapture" class="btn btn-primary d-flex align-items-center gap-2">
                                <i class="bi bi-person-check-fill fs-5"></i>
                                Capturar Frequência
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Resultado ─────────────────────────────────────── --}}
                <div id="resultPanel"></div>
            </div>

            {{-- Lateral ─────────────────────────────────────────── --}}
            <div class="col-lg-5">

                {{-- Instruções --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3" style="color:var(--primary-color)">
                            <i class="bi bi-info-circle me-1"></i>Como usar
                        </h6>
                        <ol class="ps-3 small text-muted mb-0" style="line-height:2">
                            <li>Posicione o aluno <strong>de frente à câmera</strong></li>
                            <li>Aguarde o rosto aparecer no feed ao vivo</li>
                            <li>Clique em <strong>Capturar Frequência</strong></li>
                            <li>O sistema identifica o aluno automaticamente</li>
                            <li>Repita para os demais alunos</li>
                        </ol>
                    </div>
                </div>

                {{-- Sessão --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold mb-0" style="color:var(--primary-color)">
                                <i class="bi bi-list-check me-1"></i>Registros desta sessão
                            </h6>
                            <span class="badge bg-primary rounded-pill" id="sessionCount">0</span>
                        </div>
                        <div id="sessionList">
                            <div id="sessionEmpty" class="text-center text-muted small py-3">
                                <i class="bi bi-hourglass d-block fs-4 mb-2"></i>
                                Nenhum registro ainda
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>
@endsection

@section('scripts')
<script>
$(function () {

    const CAPTURE_URL   = '{{ route("frequencia.capturar") }}'; // Laravel proxy
    const $btn          = $('#btnCapture');
    const $result       = $('#resultPanel');
    const $sessionList  = $('#sessionList');
    const $sessionCount = $('#sessionCount');
    const $stream       = $('#cameraStream');
    const $offline      = $('#cameraOffline');
    const $loading      = $('#captureLoading');
    let   sessionCount  = 0;

    // ── Câmera offline ───────────────────────────────────────────
    $stream.on('error', function () {
        $(this).hide();
        $offline.css('display','flex');
    });
    $stream.on('load', function () {
        $(this).show();
        $offline.hide();
    });

    // ── Captura ──────────────────────────────────────────────────
    $btn.on('click', async function () {
        $btn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-2"></span>Capturando...');
        $loading.fadeIn(150);
        $result.fadeOut(100).removeClass('alert-success alert-danger alert-warning').html('');

        try {
            const res  = await fetch(CAPTURE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({}),
            });

            const data = await res.json();
            $loading.fadeOut(150);

            if (data.success) {
                showSuccess(data.student_name, data.student_class, data.time);
                addSessionItem(data.student_name, data.student_class, data.time);
            } else {
                if (data.type === 'not_found') showWarning(data.message);
                else                           showError(data.message);
            }

        } catch (e) {
            $loading.fadeOut(150);
            showError('Não foi possível conectar ao servidor.<br>Verifique se o servidor Python está rodando e tente novamente.');
        } finally {
            $btn.prop('disabled', false)
                .html('<i class="bi bi-person-check-fill fs-5"></i> Capturar Frequência');
        }
    });

    // ── Feedback visual ──────────────────────────────────────────
    function showSuccess(name, turma, time) {
        $result.addClass('alert alert-success').html(`
            <div class="d-flex align-items-start gap-3">
                <i class="bi bi-check-circle-fill fs-3 text-success flex-shrink-0"></i>
                <div>
                    <div class="fw-bold fs-6">${name}</div>
                    <div class="small text-muted">${turma ?? ''}</div>
                    <div class="small mt-1"><i class="bi bi-clock me-1"></i>${time} — Presença registrada com sucesso</div>
                </div>
            </div>
        `).fadeIn(200);
    }

    function showWarning(msg) {
        $result.addClass('alert alert-warning').html(`
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-person-x-fill fs-4 flex-shrink-0"></i>
                <span class="small">${msg}</span>
            </div>
        `).fadeIn(200);
    }

    function showError(msg) {
        $result.addClass('alert alert-danger').html(`
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-x-circle-fill fs-4 flex-shrink-0"></i>
                <span class="small">${msg}</span>
            </div>
        `).fadeIn(200);
    }

    // ── Histórico da sessão ──────────────────────────────────────
    function addSessionItem(name, turma, time) {
        sessionCount++;
        $sessionCount.text(sessionCount);
        $('#sessionEmpty').hide();

        const initials = name.split(' ').slice(0,2).map(w => w[0] ?? '?').join('').toUpperCase();

        $sessionList.prepend(`
            <div class="session-item">
                <div class="session-avatar">${initials}</div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-semibold small text-truncate">${name}</div>
                    <div class="text-muted text-truncate" style="font-size:.72rem">
                        <i class="bi bi-check2-circle me-1 text-success"></i>${time}
                        ${turma ? '· ' + turma : ''}
                    </div>
                </div>
            </div>
        `);
    }

});
</script>
@endsection
