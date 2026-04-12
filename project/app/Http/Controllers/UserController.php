<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\SchoolService;
use App\Services\UserService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected SchoolService $schoolService,
    ) {}

    public function index()
    {
        $users = $this->userService->getAll();

        return view('pages.admin.usuarios', compact('users'));
    }

    public function create()
    {
        $schools = $this->schoolService->getAll(auth()->user());
        return view('pages.admin.fc-usuario', compact('schools'));
    }

    public function store(StoreUserRequest $request)
    {
        try {
            $this->userService->store($request->validated());

            return redirect()->route('usuario.index')->with('success', 'Usuário registrado com sucesso!');
        } catch (QueryException $e) {
            Log::error('UserController@store DB error', [
                'message' => $e->getMessage(),
                'sql'     => $e->getSql(),
            ]);

            return redirect()->back()->withInput()->with('error', 'Erro ao registrar usuário. Tente novamente.');
        }
    }

    public function edit(User $user)
    {
        $schools        = $this->schoolService->getAll(auth()->user());
        $linkedSchools  = $user->schools->pluck('id')->toArray();
        return view('pages.admin.fe-usuario', compact('user', 'schools', 'linkedSchools'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            $resetPassword = $request->filled('reset_password') && $request->reset_password === 'on';

            $this->userService->update($user, $request->validated(), $resetPassword);

            return redirect()->route('usuario.index')->with('success', 'Usuário atualizado com sucesso!');
        } catch (QueryException $e) {
            Log::error('UserController@update DB error', [
                'message' => $e->getMessage(),
                'sql'     => $e->getSql(),
            ]);

            return redirect()->back()->withInput()->with('error', 'Erro ao atualizar usuário. Tente novamente.');
        }
    }

    public function restore($id)
    {
        try {
            $this->userService->restore($id);

            return redirect()->route('usuario.index')->with('success', 'Usuário reativado com sucesso!');
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return redirect()->back()->with('error', 'Erro ao reativar: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->userService->destroy($id);

            return redirect()->route('usuario.index')->with('success', 'Usuário inativado com sucesso!');
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return redirect()->back()->with('error', 'Erro ao inativar: ' . $e->getMessage());
        }
    }
}
