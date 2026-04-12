<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSchoolRequest;
use App\Http\Requests\UpdateSchoolRequest;
use App\Services\SchoolService;
use App\Models\School;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;


class SchoolController extends Controller
{

    public function __construct(private SchoolService $schoolService) {

    }

    public function index()
    {
        $schools = $this->schoolService->getAll(auth()->user());
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
        } catch (QueryException $e) {
            Log::error('SchoolController@store DB error', [
                'message' => $e->getMessage(),
                'sql'     => $e->getSql(),
            ]);
            return redirect()->back()->with('error', 'Erro ao cadastrar escola. Tente novamente.');
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
        } catch (QueryException $e) {
            Log::error('SchoolController@update DB error', [
                'message' => $e->getMessage(),
                'sql'     => $e->getSql(),
            ]);
            return redirect()->back()->with('error', 'Erro ao atualizar escola. Tente novamente.');
        }

        return redirect()->route('escola.index')->with('success', 'Escola atualizada com sucesso!');
    }

    public function destroy(School $school)
    {
        try{
            $this->schoolService->destroy($school->id);
        } catch (QueryException $e) {
            Log::error('SchoolController@destroy DB error', [
                'message' => $e->getMessage(),
                'sql'     => $e->getSql(),
            ]);
            return redirect()->back()->with('error', 'Erro ao excluir escola. Tente novamente.');
        }

        return redirect()->route('escola.index')->with('success', 'Escola excluída com sucesso!');
    }
}
