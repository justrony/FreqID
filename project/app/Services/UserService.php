<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    private string $defaultPassword = '12345678';

    public function getAll(): Collection
    {
        return User::withTrashed()->get();
    }

    public function store(array $validated): User
    {
        return DB::transaction(function () use ($validated) {
            $schoolIds = $validated['school_ids'] ?? [];
            unset($validated['school_ids']);

            $user = User::create(array_merge($validated, [
                'password' => Hash::make($this->defaultPassword),
            ]));

            $user->schools()->sync($schoolIds);

            return $user;
        });
    }

    public function update(User $user, array $validated, bool $resetPassword = false): User
    {
        return DB::transaction(function () use ($user, $validated, $resetPassword) {
            $schoolIds = $validated['school_ids'] ?? [];
            unset($validated['school_ids']);

            $data = $resetPassword
                ? array_merge($validated, ['password' => Hash::make($this->defaultPassword)])
                : $validated;

            $user->update($data);
            $user->schools()->sync($schoolIds);

            return $user;
        });
    }

    public function restore(int $id): User
    {
        return DB::transaction(function () use ($id) {
            $user = User::withTrashed()->findOrFail($id);
            $user->restore();
            return $user;
        });
    }

    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            User::findOrFail($id)->delete();
        });
    }
}
