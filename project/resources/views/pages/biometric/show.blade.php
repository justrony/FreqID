@extends('layouts.home')
@section('title', 'Cadastro Biométrico — {{ $student->name }} - FreqID')

@section('custom_css')
<style>
    /* ── Drop Zone ─────────────────────────────────────────── */
    .dropzone-area {
        border: 2.5px dashed #cbd5e1;
        border-radius: 16px;
        background: #f8fafc;
        min-height: 280px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: border-color .2s, background .2s;
        position: relative;
        overflow: hidden;
    }
    .dropzone-area:hover,
    .dropzone-area.drag-over {
        border-color: var(--primary-color);
        background: #eff6ff;
    }
    .dropzone-area.has-preview {
        border-style: solid;
        border-color: #22c55e;
        background: #f0fdf4;
        min-height: 0;
        padding: 0;
    }
    #photoInput { display: none; }

    /* ── Preview ───────────────────────────────────────────── */
    #previewWrapper {
        display: none;
        position: relative;
        width: 100%;
    }
    #photoPreview {
        width: 100%;
        max-height: 380px;
        object-fit: contain;
        border-radius: 14px;
        display: block;
    }
    #previewOverlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.45);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity .2s;
    }
    #previewWrapper:hover #previewOverlay { opacity: 1; }

    /* ── Face detection badge ──────────────────────────────── */
    #faceBadge {
        display: none;
        position: absolute;
        top: 12px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(34,197,94,0.9);
        color: #fff;
        font-size: 0.72rem;
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 600;
        white-space: nowrap;
        z-index: 5;
    }

    /* ── Status dot ────────────────────────────────────────── */
    .status-dot {
        width: 10px; height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }
    .status-dot.idle    { background: #94a3b8; }
    .status-dot.ok      { background: #22c55e; box-shadow: 0 0 6px #22c55e; }
    .status-dot.error   { background: #ef4444; }
    .status-dot.loading { background: #f59e0b; animation: pulse 1s infinite; }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

    /* ── Step tracker ──────────────────────────────────────── */
    .step-item { display: flex; align-items: flex-start; gap: 10px; opacity: .4; transition: .3s; }
    .step-item.active { opacity: 1; }
    .step-item.done   { opacity: .7; }
    .step-icon { width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:.75rem; }
    .step-item.active .step-icon { background: var(--accent-color); color:#fff; }
    .step-item.done   .step-icon { background: #22c55e; color:#fff; }
    .step-item        .step-icon { background: #e2e8f0; color:#94a3b8; }

    /* ── Upload button ─────────────────────────────────────── */
    #btnSubmit:disabled { opacity: .55; cursor: not-allowed; }
</style>
@endsection

@section('content')
<main class="col-md-9 ms-sm-auto col-lg-10 p-0">
    <header class="top-header p-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="m-0 fw-bold" style="color:var(--primary-color)">
                    <i class="bi bi-image me-2"></i>Cadastro Biométrico por Foto
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
                Este aluno já possui biometria cadastrada. Ao enviar uma nova foto, o registro anterior será
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

        @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-x-circle-fill fs-5"></i>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
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

                        {{-- Formulário de upload --}}
                        <form action="{{ route('biometric.store', $student->id) }}"
                              method="POST"
                              enctype="multipart/form-data"
                              id="enrollForm">
                            @csrf

                            {{-- Drop Zone --}}
                            <div class="dropzone-area mb-3" id="dropZone">

                                {{-- Badge de rosto detectado (client-side) --}}
                                <span id="faceBadge"><i class="bi bi-person-check me-1"></i>Rosto detectado</span>

                                {{-- Placeholder inicial --}}
                                <div id="dropPlaceholder" class="text-center px-4 py-3">
                                    <i class="bi bi-cloud-arrow-up" style="font-size:3rem;color:#94a3b8"></i>
                                    <p class="fw-semibold mt-3 mb-1" style="color:#475569">
                                        Arraste a foto aqui ou clique para selecionar
                                    </p>
                                    <p class="text-muted small mb-3">JPG, PNG ou WEBP · máx. 5 MB</p>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnChoose">
                                        <i class="bi bi-folder2-open me-1"></i>Escolher arquivo
                                    </button>
                                </div>

                                {{-- Preview da foto selecionada --}}
                                <div id="previewWrapper">
                                    <img id="photoPreview" src="" alt="Preview da foto">
                                    <div id="previewOverlay">
                                        <button type="button" class="btn btn-light btn-sm" id="btnChange">
                                            <i class="bi bi-arrow-repeat me-1"></i>Trocar foto
                                        </button>
                                    </div>
                                </div>

                            </div>

                            {{-- Input file oculto --}}
                            <input type="file" id="photoInput" name="photo"
                                   accept="image/jpeg,image/png,image/webp">

                            {{-- Nome do arquivo selecionado --}}
                            <div id="fileInfo" class="small text-muted mb-3" style="display:none">
                                <i class="bi bi-paperclip me-1"></i>
                                <span id="fileName"></span>
                                <span id="fileSize" class="ms-2 text-secondary"></span>
                            </div>

                            {{-- Status em tempo real --}}
                            <div class="d-flex align-items-center mb-4">
                                <span class="status-dot idle" id="statusDot"></span>
                                <span id="statusText" class="small text-muted">Selecione uma foto para começar</span>
                            </div>

                            {{-- Botões --}}
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="submit"
                                        id="btnSubmit"
                                        class="btn btn-primary d-flex align-items-center gap-2"
                                        disabled>
                                    <i class="bi bi-cloud-check-fill"></i>
                                    {{ $enrolled ? 'Atualizar Biometria' : 'Cadastrar Biometria' }}
                                </button>

                                <a href="{{ route('biometric.index') }}"
                                   class="btn btn-outline-secondary d-flex align-items-center gap-1">
                                    <i class="bi bi-x-lg"></i> Cancelar
                                </a>
                            </div>

                        </form>

                    </div>
                </div>
            </div>

            {{-- Painel lateral --}}
            <div class="col-lg-4">

                {{-- Instruções --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3" style="color:var(--primary-color)">
                            <i class="bi bi-info-circle me-1"></i>Como funciona
                        </h6>
                        <ol class="ps-3 small text-muted" style="line-height:1.8">
                            <li>Selecione ou arraste uma <strong>foto do aluno</strong></li>
                            <li>A foto deve mostrar o rosto <strong>de frente e bem iluminado</strong></li>
                            <li>Verifique o preview antes de enviar</li>
                            <li>Clique em <strong>Cadastrar Biometria</strong></li>
                            <li>O Python extrai os pontos faciais (MediaPipe)</li>
                            <li>A biometria é salva e a foto é <strong>descartada</strong></li>
                        </ol>
                    </div>
                </div>

                {{-- Dicas de foto boa --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body py-3">
                        <h6 class="fw-bold mb-2" style="color:var(--primary-color);font-size:.85rem">
                            <i class="bi bi-lightbulb me-1"></i>Dicas para melhor resultado
                        </h6>
                        <div class="d-flex flex-column gap-2 small">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0"></i>
                                <span class="text-muted">Rosto centralizado e visível</span>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0"></i>
                                <span class="text-muted">Boa iluminação, sem contra-luz</span>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0"></i>
                                <span class="text-muted">Expressão neutra, olhos abertos</span>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-x-circle-fill text-danger mt-1 flex-shrink-0"></i>
                                <span class="text-muted">Evite óculos escuros ou máscara</span>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-x-circle-fill text-danger mt-1 flex-shrink-0"></i>
                                <span class="text-muted">Evite fotos em grupo</span>
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
                            <div class="step-item done" data-step="1">
                                <div class="step-icon">1</div>
                                <div>
                                    <div class="fw-semibold small">Aluno selecionado</div>
                                    <div class="text-muted" style="font-size:.75rem">{{ $student->name }}</div>
                                </div>
                            </div>
                            <div class="step-item" data-step="2" id="step2">
                                <div class="step-icon">2</div>
                                <div>
                                    <div class="fw-semibold small">Foto selecionada</div>
                                    <div class="text-muted" style="font-size:.75rem">JPG, PNG ou WEBP</div>
                                </div>
                            </div>
                            <div class="step-item" data-step="3" id="step3">
                                <div class="step-icon">3</div>
                                <div>
                                    <div class="fw-semibold small">Envio ao servidor</div>
                                    <div class="text-muted" style="font-size:.75rem">Python processa a imagem</div>
                                </div>
                            </div>
                            <div class="step-item" data-step="4" id="step4">
                                <div class="step-icon">4</div>
                                <div>
                                    <div class="fw-semibold small">Biometria salva</div>
                                    <div class="text-muted" style="font-size:.75rem">Persistida no banco</div>
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

    const $dropZone       = $('#dropZone');
    const $input          = $('#photoInput');
    const $btnChoose      = $('#btnChoose');
    const $btnChange      = $('#btnChange');
    const $btnSubmit      = $('#btnSubmit');
    const $placeholder    = $('#dropPlaceholder');
    const $previewWrapper = $('#previewWrapper');
    const $preview        = $('#photoPreview');
    const $fileInfo       = $('#fileInfo');
    const $fileName       = $('#fileName');
    const $fileSize       = $('#fileSize');
    const $statusDot      = $('#statusDot');
    const $statusText     = $('#statusText');
    const $faceBadge      = $('#faceBadge');
    const $form           = $('#enrollForm');

    const MAX_BYTES = 5 * 1024 * 1024; // 5 MB
    const ALLOWED   = ['image/jpeg', 'image/png', 'image/webp'];

    // ── Helpers ─────────────────────────────────────────────────────────────
    function setStatus(type, text) {
        $statusDot.attr('class', 'status-dot ' + type);
        $statusText.text(text);
    }

    function activateStep(n) {
        $('[data-step]').each(function () {
            const s = parseInt($(this).data('step'));
            if (s < n)        $(this).attr('class', 'step-item done');
            else if (s === n) $(this).attr('class', 'step-item active');
            else              $(this).attr('class', 'step-item');
        });
    }

    function formatBytes(bytes) {
        return bytes < 1024 * 1024
            ? (bytes / 1024).toFixed(0) + ' KB'
            : (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    // ── Processar arquivo selecionado ────────────────────────────────────────
    function processFile(file) {
        // Valida tipo
        if (!ALLOWED.includes(file.type)) {
            setStatus('error', 'Tipo de arquivo inválido. Use JPG, PNG ou WEBP.');
            return;
        }

        // Valida tamanho
        if (file.size > MAX_BYTES) {
            setStatus('error', `Arquivo muito grande (${formatBytes(file.size)}). Máximo: 5 MB.`);
            return;
        }

        // Preview
        const reader = new FileReader();
        reader.onload = function (e) {
            $preview.attr('src', e.target.result);
            $placeholder.hide();
            $previewWrapper.show();
            $dropZone.addClass('has-preview');
            $faceBadge.fadeIn(200);

            // Info
            $fileName.text(file.name);
            $fileSize.text('· ' + formatBytes(file.size));
            $fileInfo.show();

            // Habilitar envio
            $btnSubmit.prop('disabled', false);
            setStatus('ok', 'Foto pronta para envio — verifique o preview');
            activateStep(2);
        };
        reader.readAsDataURL(file);
    }

    // ── Drag & Drop ──────────────────────────────────────────────────────────
    $dropZone.on('dragover', function (e) {
        e.preventDefault();
        $(this).addClass('drag-over');
    });

    $dropZone.on('dragleave drop', function () {
        $(this).removeClass('drag-over');
    });

    $dropZone.on('drop', function (e) {
        e.preventDefault();
        const file = e.originalEvent.dataTransfer.files[0];
        if (file) {
            // Injeta no input para o form enviar
            const dt = new DataTransfer();
            dt.items.add(file);
            $input[0].files = dt.files;
            processFile(file);
        }
    });

    // ── Clique na dropzone / botão escolher ─────────────────────────────────
    $dropZone.on('click', function (e) {
        if (!$(e.target).is('#btnChange, #btnSubmit')) {
            $input.trigger('click');
        }
    });

    $btnChoose.on('click', function (e) {
        e.stopPropagation();
        $input.trigger('click');
    });

    $btnChange.on('click', function (e) {
        e.stopPropagation();
        $input.trigger('click');
    });

    $input.on('change', function () {
        const file = this.files[0];
        if (file) processFile(file);
    });

    // ── Submissão do formulário ──────────────────────────────────────────────
    $form.on('submit', function () {
        $btnSubmit.prop('disabled', true)
                  .html('<span class="spinner-border spinner-border-sm me-2"></span>Enviando...');
        setStatus('loading', 'Enviando foto para processamento...');
        activateStep(3);

        // Após um tick visual, deixa o form submeter normalmente
        setTimeout(function () {
            activateStep(4);
        }, 500);
    });

});
</script>
@endsection
