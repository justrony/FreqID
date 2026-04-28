<?php

namespace App\Http\Controllers;

use App\Services\Biometric\CaptureException;
use App\Services\BiometricEnrollmentService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BiometricEnrollmentController extends Controller
{
    public function __construct(
        private readonly BiometricEnrollmentService $enrollmentService,
    ) {}

    /**
     * Exibe a tela de cadastro facial — lista os alunos do professor.
     */
    public function index()
    {
        $students = $this->enrollmentService->getStudentsForEnrollment(auth()->id());
        return view('pages.biometric.index', compact('students'));
    }

    /**
     * Exibe o painel de captura para um aluno específico.
     */
    public function show(int $studentId)
    {
        $students = $this->enrollmentService->getStudentsForEnrollment(auth()->id());
        $student  = $students->firstOrFail(fn($s) => $s->id === $studentId);
        $enrolled = $this->enrollmentService->isEnrolled($studentId);

        return view('pages.biometric.show', compact('student', 'enrolled'));
    }

    /**
     * Dispara o processo de cadastro da biometria para o aluno.
     * O BiometricRegistrar (Bridge) delega ao driver Python a captura real.
     */
    public function store(Request $request, int $studentId)
    {
        try {
            $faceFeature = $this->enrollmentService->enroll($studentId, auth()->id());

            return redirect()
                ->route('biometric.index')
                ->with('success', "Biometria do aluno cadastrada com sucesso! (ID #{$faceFeature->id})");

        } catch (AuthorizationException $e) {
            return redirect()->back()->with('error', $e->getMessage());

        } catch (CaptureException $e) {
            Log::warning('[BiometricEnrollmentController] Falha na captura', [
                'student_id' => $studentId,
                'error'      => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', "Falha na captura: {$e->getMessage()}");

        } catch (\Throwable $e) {
            Log::error('[BiometricEnrollmentController] Erro inesperado', [
                'student_id' => $studentId,
                'error'      => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Erro inesperado ao cadastrar biometria. Tente novamente.');
        }
    }
}
