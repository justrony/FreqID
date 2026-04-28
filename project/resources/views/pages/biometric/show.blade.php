@extends('layouts.home')
@section('title', 'Captura Biométrica — {{ $student->name }} - FreqID')

@section('custom_css')
<style>
    /* ---- Painel de câmera ---- */
    .camera-panel {
        background: #0d1117;
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        min-height: 320px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    /* Stream de vídeo real */
    #cameraStream {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 16px;
        display: none; /* Escondido até a câmera ser ativada */
    }
    /* Overlay sobre o stream */
    .camera-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        pointer-events: none;
    }
    .camera-panel #camera-placeholder {
        color: rgba(255,255,255,0.3);
        text-align: center;
    }
    /* Scanner animado */
    .scan-line {
        display: none;
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, transparent, var(--accent-color), transparent);
        animation: scanDown 2s linear infinite;
        z-index: 10;
    }
    @keyframes scanDown {
        0%   { top: 0%; }
        100% { top: 100%; }
    }
    /* Corner brackets */
    .corner-tl, .corner-tr, .corner-bl, .corner-br {
        position: absolute;
        width: 28px;
        height: 28px;
        border-color: var(--accent-color);
        border-style: solid;
        opacity: 0.9;
        z-index: 10;
    }
    .corner-tl { top:12px; left:12px;  border-width: 3px 0 0 3px; }
    .corner-tr { top:12px; right:12px; border-width: 3px 3px 0 0; }
    .corner-bl { bottom:12px; left:12px;  border-width: 0 0 3px 3px; }
    .corner-br { bottom:12px; right:12px; border-width: 0 3px 3px 0; }

    /* Badge de câmera ao vivo */
    .live-badge {
        display: none;
        position: absolute;
        top: 14px; left: 50%;
        transform: translateX(-50%);
        background: rgba(0,0,0,0.6);
        color: #fff;
        font-size: 0.7rem;
        padding: 3px 10px;
        border-radius: 20px;
        z-index: 11;
        align-items: center;
        gap: 5px;
    }
    .live-badge .live-dot {
        width: 7px; height: 7px; border-radius: 50%;
        background: #ef4444;
        animation: pulse 1s infinite;
        display: inline-block;
    }

    /* Status badge */
    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }
    .status-dot.ready   { background: #22c55e; box-shadow: 0 0 6px #22c55e; }
    .status-dot.loading { background: #f59e0b; box-shadow: 0 0 6px #f59e0b; animation: pulse 1s infinite; }
    .status-dot.error   { background: #ef4444; }
    .status-dot.live    { background: #22c55e; box-shadow: 0 0 6px #22c55e; animation: pulse 1s infinite; }

    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }

    /* Step tracker */
    .step-item { display: flex; align-items: flex-start; gap: 10px; opacity: 0.4; transition: 0.3s; }
    .step-item.active  { opacity: 1; }
    .step-item.done    { opacity: 0.7; }
    .step-icon { width: 28px; height: 28px; border-radius: 50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:0.75rem; }
    .step-item.active .step-icon { background: var(--accent-color); color: white; }
    .step-item.done   .step-icon { background: #22c55e; color: white; }
    .step-item        .step-icon { background: #e2e8f0; color: #94a3b8; }

    /* Erro de câmera */
    #camera-error { display: none; }
</style>
@endsection

@section('content')
<main class="col-md-9 ms-sm-auto col-lg-10 p-0">
    <header class="top-header p-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="m-0 fw-bold" style="color:var(--primary-color)">
                    <i class="bi bi-camera-fill me-2"></i>Captura Biométrica
                </h5>
                <small class="text-muted">Aluno: <strong>{{ $student->name }}</strong> · {{ $student->schoolClass->name ?? '' }}</small>
            </div>
            <a href="{{ route('biometric.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </header>

    <div class="p-4">

        {{-- Alerta se já tem biometria --}}
        @if($enrolled)
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div>
                Este aluno já possui biometria cadastrada. Ao capturar novamente, o registro anterior será
                <strong>substituído</strong>.
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-x-circle-fill fs-5"></i>
            {{ session('error') }}
        </div>
        @endif

        <div class="row g-4">

            {{-- Painel principal --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">

                        {{-- Info do aluno --}}
                        <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:56px;height:56px;background:{{ $enrolled ? '#d1fae5' : '#eff6ff' }};flex-shrink:0;font-size:1.4rem">
                                <i class="bi {{ $enrolled ? 'bi-person-check-fill text-success' : 'bi-person-fill' }}" style="color:var(--primary-color)"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">{{ $student->name }}</h6>
                                <small class="text-muted">
                                    Matrícula: <code>{{ $student->registration }}</code> ·
                                    Turma: {{ $student->schoolClass->name ?? '—' }} ·
                                    Escola: {{ $student->school->name ?? '—' }}
                                </small>
                            </div>
                            @if($enrolled)
                                <span class="badge bg-success ms-auto px-3 py-2">
                                    <i class="bi bi-check2-circle me-1"></i>Cadastrado
                                </span>
                            @else
                                <span class="badge bg-warning text-dark ms-auto px-3 py-2">
                                    <i class="bi bi-clock me-1"></i>Pendente
                                </span>
                            @endif
                        </div>

                        {{-- Painel de câmera com stream ao vivo --}}
                        <div class="camera-panel mb-3" id="cameraPanel">

                            {{-- Corners sempre visíveis --}}
                            <div class="corner-tl"></div>
                            <div class="corner-tr"></div>
                            <div class="corner-bl"></div>
                            <div class="corner-br"></div>

                            {{-- Scan line (aparece ao capturar) --}}
                            <div class="scan-line" id="scanLine"></div>

                            {{-- Badge LIVE --}}
                            <div class="live-badge" id="liveBadge">
                                <span class="live-dot"></span> AO VIVO
                            </div>

                            {{-- Stream MJPEG do Python --}}
                            <img id="cameraStream"
                                 src=""
                                 alt="Stream da câmera"
                                 style="width:100%;border-radius:16px;display:none;">

                            {{-- Placeholder (antes de ativar) --}}
                            <div id="camera-placeholder">
                                <i class="bi bi-camera-fill d-block mb-2" style="font-size:2.5rem"></i>
                                <span style="font-size:0.85rem">Clique em <strong>Ativar Câmera</strong><br>para ver o feed ao vivo</span>
                            </div>

                            {{-- Loader de conexão --}}
                            <div id="camera-connecting" class="text-center text-white" style="display:none">
                                <div class="spinner-border text-info mb-2" style="width:2rem;height:2rem"></div>
                                <div style="font-size:0.85rem">Conectando à câmera...</div>
                            </div>

                            {{-- Erro de câmera offline --}}
                            <div id="camera-error" class="text-center" style="display:none">
                                <i class="bi bi-camera-video-off fs-1 mb-2" style="color:#ef4444"></i>
                                <div style="font-size:0.85rem;color:#ef4444">
                                    Serviço de câmera indisponível<br>
                                    <small class="text-muted">Verifique se o servidor Python está rodando na porta 8001</small>
                                </div>
                                <button class="btn btn-sm btn-outline-danger mt-2" onclick="tryActivateCamera()">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Tentar novamente
                                </button>
                            </div>
                        </div>

                        {{-- Status em tempo real --}}
                        <div class="d-flex align-items-center mb-4" id="statusBar">
                            <span class="status-dot ready" id="statusDot"></span>
                            <span id="statusText" class="small text-muted">Clique em "Ativar Câmera" para começar</span>
                        </div>

                        {{-- Botões de ação --}}
                        <div class="d-flex gap-2 flex-wrap">
                            {{-- Botão 1: Ativar câmera --}}
                            <button type="button"
                                    id="btnActivate"
                                    class="btn btn-outline-primary d-flex align-items-center gap-2">
                                <i class="bi bi-camera-video-fill"></i>
                                Ativar Câmera
                            </button>

                            {{-- Botão 2: Capturar (aparece após câmera ativa) --}}
                            <form action="{{ route('biometric.store', $student->id) }}"
                                  method="POST"
                                  id="captureForm">
                                @csrf
                                <button type="button"
                                        id="btnCapture"
                                        class="btn btn-primary d-flex align-items-center gap-2"
                                        disabled>
                                    <i class="bi bi-camera-fill"></i>
                                    {{ $enrolled ? 'Recapturar Biometria' : 'Capturar Biometria' }}
                                </button>
                            </form>

                            <a href="{{ route('biometric.index') }}"
                               class="btn btn-outline-secondary d-flex align-items-center gap-1">
                                <i class="bi bi-x-lg"></i> Cancelar
                            </a>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Painel lateral: instruções + steps --}}
            <div class="col-lg-4">

                {{-- Instruções --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3" style="color:var(--primary-color)">
                            <i class="bi bi-info-circle me-1"></i>Como funciona
                        </h6>
                        <ol class="ps-3 small text-muted" style="line-height:1.8">
                            <li>Clique em <strong>Ativar Câmera</strong> para ver o feed ao vivo</li>
                            <li>Posicione o aluno em frente à câmera</li>
                            <li>Aguarde a <span class="text-success fw-semibold">borda verde</span> indicar rosto detectado</li>
                            <li>Clique em <strong>Capturar Biometria</strong></li>
                            <li>O Python extrai os pontos faciais (MediaPipe)</li>
                            <li>A biometria é salva no banco de dados</li>
                        </ol>
                    </div>
                </div>

                {{-- Legenda do stream --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body py-3">
                        <h6 class="fw-bold mb-2" style="color:var(--primary-color); font-size:0.85rem">
                            <i class="bi bi-palette me-1"></i>Legenda do vídeo
                        </h6>
                        <div class="d-flex flex-column gap-2 small">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:14px;height:14px;border:2px solid #22c55e;border-radius:2px;flex-shrink:0"></div>
                                <span class="text-muted">Borda verde — rosto detectado</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:14px;height:14px;border:2px solid #f97316;border-radius:2px;flex-shrink:0"></div>
                                <span class="text-muted">Borda laranja — sem rosto</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:14px;height:14px;background:rgba(255,255,255,0.2);border-radius:2px;flex-shrink:0"></div>
                                <span class="text-muted">Malha facial — landmarks MediaPipe</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Steps de progresso --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3" style="color:var(--primary-color)">
                            <i class="bi bi-list-check me-1"></i>Progresso
                        </h6>
                        <div class="d-flex flex-column gap-3" id="stepsList">
                            <div class="step-item active" data-step="1">
                                <div class="step-icon">1</div>
                                <div>
                                    <div class="fw-semibold small">Aluno selecionado</div>
                                    <div class="text-muted" style="font-size:0.75rem">{{ $student->name }}</div>
                                </div>
                            </div>
                            <div class="step-item" data-step="2" id="step2">
                                <div class="step-icon">2</div>
                                <div>
                                    <div class="fw-semibold small">Câmera ao vivo</div>
                                    <div class="text-muted" style="font-size:0.75rem">Stream MJPEG / FastAPI</div>
                                </div>
                            </div>
                            <div class="step-item" data-step="3" id="step3">
                                <div class="step-icon">3</div>
                                <div>
                                    <div class="fw-semibold small">Extração de landmarks</div>
                                    <div class="text-muted" style="font-size:0.75rem">MediaPipe Face Mesh</div>
                                </div>
                            </div>
                            <div class="step-item" data-step="4" id="step4">
                                <div class="step-icon">4</div>
                                <div>
                                    <div class="fw-semibold small">Salvar biometria</div>
                                    <div class="text-muted" style="font-size:0.75rem">Persistir no banco</div>
                                </div>
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
$(document).ready(function () {

    const STREAM_URL    = 'http://localhost:8001/stream';
    const HEALTH_URL    = 'http://localhost:8001/health';

    const $btnActivate  = $('#btnActivate');
    const $btnCapture   = $('#btnCapture');
    const $form         = $('#captureForm');
    const $scanLine     = $('#scanLine');
    const $placeholder  = $('#camera-placeholder');
    const $connecting   = $('#camera-connecting');
    const $errorBox     = $('#camera-error');
    const $stream       = $('#cameraStream');
    const $liveBadge    = $('#liveBadge');
    const $dot          = $('#statusDot');
    const $status       = $('#statusText');

    let cameraActive = false;

    function setStatus(type, text) {
        $dot.attr('class', 'status-dot ' + type);
        $status.text(text);
    }

    function activateStep(n) {
        $('[data-step]').each(function () {
            const s = parseInt($(this).data('step'));
            if (s < n)        $(this).attr('class', 'step-item done');
            else if (s === n) $(this).attr('class', 'step-item active');
            else              $(this).attr('class', 'step-item');
        });
    }

    // ── Ativar câmera ────────────────────────────────────────────────────────
    window.tryActivateCamera = function () {
        $placeholder.hide();
        $errorBox.hide();
        $connecting.show();
        setStatus('loading', 'Verificando serviço Python...');

        // Testa o /health antes de tentar o stream
        $.get(HEALTH_URL)
            .done(function () {
                showStream();
            })
            .fail(function () {
                $connecting.hide();
                $errorBox.show();
                setStatus('error', 'Serviço Python offline (porta 8001)');
            });
    };

    function showStream() {
        // Carrega o MJPEG no img tag
        $stream.attr('src', STREAM_URL);

        $stream.on('load', function () {
            // stream começou a chegar
        });

        // Pequeno delay para garantir que o stream começou
        setTimeout(function () {
            $connecting.hide();
            $stream.show();
            $liveBadge.css('display', 'flex');
            $btnCapture.prop('disabled', false);
            $btnActivate.prop('disabled', true)
                        .html('<i class="bi bi-camera-video-fill me-1"></i>Câmera ativa');
            cameraActive = true;
            activateStep(2);
            setStatus('live', 'Câmera ao vivo — posicione o aluno');
        }, 1200);
    }

    $btnActivate.on('click', function () {
        tryActivateCamera();
    });

    // ── Capturar biometria ───────────────────────────────────────────────────
    $btnCapture.on('click', function () {
        if (!cameraActive) return;

        $btnCapture.prop('disabled', true)
                   .html('<span class="spinner-border spinner-border-sm me-2"></span>Capturando...');
        $scanLine.show();
        setStatus('loading', 'Capturando landmarks faciais...');
        activateStep(3);

        setTimeout(function () {
            setStatus('loading', 'Salvando biometria...');
            activateStep(4);

            // Para o stream antes de submeter (evita conexão pendente)
            $stream.attr('src', '');

            $form.submit();
        }, 1500);
    });
});
</script>
@endsection
