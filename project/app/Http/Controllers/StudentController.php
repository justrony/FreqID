<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Student;
use App\Services\SchoolClassService;
use App\Services\SchoolService;
use App\Services\StudentService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    public function __construct(
        private StudentService $studentService,
        private SchoolService $schoolService,
        private SchoolClassService $schoolClassService
    ) {}

    public function index()
    {
        $students = $this->studentService->getAll(auth()->user());
        return view('pages.index.alunos', compact('students'));
    }

    public function create()
    {
        $schools = $this->schoolService->getForSelect(auth()->user());
        $schoolClasses = $this->schoolClassService->getAll(auth()->user()); // Depending on frontend JS, we may just pass all or load via ajax.

        return view('pages.forms.create.fc-aluno', compact('schools', 'schoolClasses'));
    }

    public function store(StoreStudentRequest $request)
    {
        $validated = $request->validated();
        try {
            $this->studentService->store($validated);
        } catch (QueryException $e) {
            Log::error('StudentController@store DB error', [
                'message' => $e->getMessage(),
                'sql'     => $e->getSql(),
            ]);
            return redirect()->back()->with('error', 'Erro ao cadastrar aluno. Tente novamente.')->withInput();
        }

        return redirect()->route('aluno.index')->with('success', 'Aluno cadastrado com sucesso!');
    }

    public function edit(Student $aluno)
    {
        $schools = $this->schoolService->getForSelect(auth()->user());
        // In a real app we'd load only classes for the selected school or load them via JS.
        $schoolClasses = $this->schoolClassService->getAll(auth()->user()); 

        return view('pages.forms.edit.fe-aluno', compact('aluno', 'schools', 'schoolClasses'));
    }

    public function update(UpdateStudentRequest $request, Student $aluno)
    {
        $validated = $request->validated();
        try {
            $this->studentService->update($aluno, $validated);
        } catch (QueryException $e) {
            Log::error('StudentController@update DB error', [
                'message' => $e->getMessage(),
                'sql'     => $e->getSql(),
            ]);
            return redirect()->back()->with('error', 'Erro ao atualizar aluno. Tente novamente.')->withInput();
        }

        return redirect()->route('aluno.index')->with('success', 'Aluno atualizado com sucesso!');
    }

    public function destroy(Student $aluno)
    {
        try {
            $this->studentService->destroy($aluno->id);
        } catch (QueryException $e) {
            Log::error('StudentController@destroy DB error', [
                'message' => $e->getMessage(),
                'sql'     => $e->getSql(),
            ]);
            return redirect()->back()->with('error', 'Erro ao excluir aluno. Tente novamente.');
        }

        return redirect()->route('aluno.index')->with('success', 'Aluno excluído com sucesso!');
    }
}
