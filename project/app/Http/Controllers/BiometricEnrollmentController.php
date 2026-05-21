<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnrollmentRequest;
use App\Services\Biometric\CaptureException;
use App\Services\BiometricEnrollmentService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Log;

class BiometricEnrollmentController extends Controller
{
    public function __construct(
        private readonly BiometricEnrollmentService $enrollmentService,
    ) {}

    /**
     * lista os alunos do professor.
     */
    public function index()
    {
        $students = $this->enrollmentService->getStudentsForEnrollment(auth()->id());
        return view('pages.biometric.index', compact('students'));
    }

    /**
     * Upload de foto para um aluno específico.
     */
    public function show(int $studentId)
    {
        $students = $this->enrollmentService->getStudentsForEnrollment(auth()->id());
        $student  = $students->firstOrFail(fn($s) => $s->id === $studentId);
        $enrolled = $this->enrollmentService->isEnrolled($studentId);

        return view('pages.biometric.show', compact('student', 'enrolled'));
    }

    /**
     * Processa o upload da foto e cadastra a biometria do aluno.
     */
    public function store(StoreEnrollmentRequest $request, int $studentId)
    {
        try {
            $faceFeature = $this->enrollmentService->enrollFromPhoto(
                $studentId,
                auth()->id(),
                $request->file('photo'),
            );

            return redirect()
                ->route('biometric.index')
                ->with('success', "Biometria do aluno cadastrada com sucesso!");

        } catch (AuthorizationException $e) {
            return redirect()->back()->with('error', $e->getMessage());

        } catch (CaptureException $e) {
            Log::warning('Falha no processamento da imagem', [
                'student_id' => $studentId,
                'error'      => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', "Falha ao processar a imagem");

        } catch (\Throwable $e) {
            Log::error('[BiometricEnrollmentController] Erro inesperado', [
                'student_id' => $studentId,
                'error'      => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Erro inesperado ao cadastrar biometria. Tente novamente.');
        }
    }
}

