<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSchoolClassRequest;
use App\Http\Requests\UpdateSchoolClassRequest;
use App\Models\School;
use App\Models\SchoolClass;
use App\Services\SchoolClassService;

class SchoolClassController extends Controller
{
    public function __construct(private SchoolClassService $schoolClassService) {}

    public function index()
    {
        $schoolClasses = $this->schoolClassService->getAll();
        return view('pages.index.turmas', compact('schoolClasses'));
    }

    public function create()
    {
        $schools = School::orderBy('name')->get();
        return view('pages.forms.create.fc-turma', compact('schools'));
    }

    public function store(StoreSchoolClassRequest $request)
    {
        $validated = $request->validated();
        try {
            $this->schoolClassService->store($validated);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao cadastrar turma: ' . $e->getMessage());
        }

        return redirect()->route('turma.index')->with('success', 'Turma cadastrada com sucesso!');
    }

    public function edit(SchoolClass $schoolClass)
    {
        $schools = School::orderBy('name')->get();
        return view('pages.forms.edit.fe-turma', compact('schoolClass', 'schools'));
    }

    public function update(UpdateSchoolClassRequest $request, SchoolClass $schoolClass)
    {
        $validated = $request->validated();
        try {
            $this->schoolClassService->update($schoolClass, $validated);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar turma: ' . $e->getMessage());
        }

        return redirect()->route('turma.index')->with('success', 'Turma atualizada com sucesso!');
    }

    public function destroy(SchoolClass $schoolClass)
    {
        try {
            $this->schoolClassService->destroy($schoolClass->id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao excluir turma: ' . $e->getMessage());
        }

        return redirect()->route('turma.index')->with('success', 'Turma excluída com sucesso!');
    }
}
