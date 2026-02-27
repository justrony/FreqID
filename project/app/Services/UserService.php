<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class UserService
{
    public function createUser(array $data)
    {
        DB::beginTransaction();

        try {
            $defaultPassword = "12345678";

            $user = User::create(array_merge($data, [
                'password' => Hash::make($defaultPassword),
            ]));

            DB::commit();

            return $user;

        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('Erro ao criar usuário: ' . $exception->getMessage());

            throw $exception;
        }
    }
}
