<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSchoolClassRequest;
use App\Http\Requests\UpdateSchoolClassRequest;
use App\Models\SchoolClass;
use App\Services\SchoolClassService;
use App\Services\SchoolService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class SchoolClassController extends Controller
{
    public function __construct(
        private SchoolClassService $schoolClassService,
        private SchoolService $schoolService
    ) {}

    public function index()
    {
        $schoolClasses = $this->schoolClassService->getAll(auth()->user());
        return view('pages.index.turmas', compact('schoolClasses'));
    }

    public function create()
    {
        $schools = $this->schoolService->getForSelect(auth()->user());
        return view('pages.forms.create.fc-turma', compact('schools'));
    }

    public function store(StoreSchoolClassRequest $request)
    {
        $validated = $request->validated();
        try {
            $this->schoolClassService->store($validated);
        } catch (QueryException $e) {
            Log::error('SchoolClassController@store DB error', [
                'message' => $e->getMessage(),
                'sql'     => $e->getSql(),
            ]);
            return redirect()->back()->with('error', 'Erro ao cadastrar turma. Tente novamente.');
        }

        return redirect()->route('turma.index')->with('success', 'Turma cadastrada com sucesso!');
    }

    public function edit(SchoolClass $schoolClass)
    {
        $schools = $this->schoolService->getForSelect(auth()->user());
        return view('pages.forms.edit.fe-turma', compact('schoolClass', 'schools'));
    }

    public function update(UpdateSchoolClassRequest $request, SchoolClass $schoolClass)
    {
        $validated = $request->validated();
        try {
            $this->schoolClassService->update($schoolClass, $validated);
        } catch (QueryException $e) {
            Log::error('SchoolClassController@update DB error', [
                'message' => $e->getMessage(),
                'sql'     => $e->getSql(),
            ]);
            return redirect()->back()->with('error', 'Erro ao atualizar turma. Tente novamente.');
        }

        return redirect()->route('turma.index')->with('success', 'Turma atualizada com sucesso!');
    }

    public function destroy(SchoolClass $schoolClass)
    {
        try {
            $this->schoolClassService->destroy($schoolClass->id);
        } catch (QueryException $e) {
            Log::error('SchoolClassController@destroy DB error', [
                'message' => $e->getMessage(),
                'sql'     => $e->getSql(),
            ]);
            return redirect()->back()->with('error', 'Erro ao excluir turma. Tente novamente.');
        }

        return redirect()->route('turma.index')->with('success', 'Turma excluída com sucesso!');
    }
}
