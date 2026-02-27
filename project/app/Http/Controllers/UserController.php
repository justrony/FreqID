<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Services\UserService;
use Exception;

class UserController extends Controller
{

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    public function index(){
        $users = User::withTrashed()->get();

        return view('pages.admin.users', compact('users'));
    }

    public function showUserForm(){
        return view('pages.admin.user-form');
    }

    public function store(StoreUserRequest $request)
    {
        try {
            $this->userService->createUser($request->validated());
            return redirect()
                ->route('usuario.index')
                ->with('success', 'Usuário registrado com sucesso!');

        } catch (Exception $exception) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors('error', 'Erro ao criar usuário!');

        }
    }

}
