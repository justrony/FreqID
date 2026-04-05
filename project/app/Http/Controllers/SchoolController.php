<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSchoolRequest;
use App\Http\Requests\UpdateSchoolRequest;
use App\Services\SchoolService;
use App\Models\School;


class SchoolController extends Controller
{

    public function __construct(private SchoolService $schoolService) {

    }

    public function index()
    {
        $schools = $this->schoolService->getAll();
        return view('pages.index.escolas', compact('schools'));
    }

    public function create()
    {
        return view('pages.forms.create.fc-escola');
    }

    public function store(StoreSchoolRequest $request)
    {
        $validated = $request->validated();
        try{
            $this->schoolService->store($validated);
        }catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao cadastrar escola: ' . $e->getMessage());
        }

        return redirect()->route('escola.index')->with('success', 'Escola cadastrada com sucesso!');
    }

    public function edit(School $school)
    {
        return view('pages.forms.edit.fe-escola', compact('school'));
    }

    public function update(UpdateSchoolRequest $request, School $school)
    {
        $validated = $request->validated();
        try{
            $this->schoolService->update($school, $validated);
        }catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar escola: ' . $e->getMessage());
        }

        return redirect()->route('escola.index')->with('success', 'Escola atualizada com sucesso!');
    }

    public function destroy(School $school)
    {
        try{
            $this->schoolService->destroy($school->id);
        }catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao excluir escola: ' . $e->getMessage());
        }

        return redirect()->route('escola.index')->with('success', 'Escola excluída com sucesso!');
    }
}
