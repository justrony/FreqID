<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999; max-width: 400px;">

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-lg border-0" role="alert"
             style="background-color: #d1fae5; color: #065f46; border-left: 5px solid #059669 !important;">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 me-2"></i>
                <div>
                    <strong>Sucesso!</strong><br>
                    {{ session('success') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-lg border-0" role="alert"
             style="background-color: #fee2e2; color: #991b1b; border-left: 5px solid #dc2626 !important;">
            <div class="d-flex align-items-center">
                <i class="bi bi-x-circle-fill fs-4 me-2"></i>
                <div>
                    <strong>Erro!</strong><br>
                    {{ session('error') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-lg border-0" role="alert"
             style="background-color: #fee2e2; color: #991b1b; border-left: 5px solid #dc2626 !important;">
            <div class="d-flex">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-2 mt-1"></i>
                <div>
                    <strong>Atenção:</strong>
                    <ul class="mb-0 ps-3 mt-1" style="font-size: 0.9em;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        let successAlerts = document.querySelectorAll('.alert-success');

        successAlerts.forEach(function(alert) {
            setTimeout(function() {

                let bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });
</script>
