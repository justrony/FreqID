<?php

namespace App\Services;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SchoolService
{

    public function getAll(User $user): Collection
    {
        if (Gate::forUser($user)->allows('access-admin')) {
            return School::orderBy('name')->get();
        }

        return $user->schools()->orderBy('name')->get();
    }

    public function getForSelect(User $user): Collection
    {
        if (Gate::forUser($user)->allows('access-admin')) {
            return School::orderBy('name')->get();
        }

        return $user->schools()->orderBy('name')->get();
    }

    public function store(array $validated): School
    {
        return DB::transaction(function () use ($validated) {
            return School::create($validated);
        });
    }

    public function update(School $school, array $validated): School
    {
        return DB::transaction(function () use ($school, $validated) {
            $data = $validated;

            $school->update($data);

            return $school;
        });
    }

    public function destroy(int $id): int
    {
        return DB::transaction(function () use ($id) {
            return School::findOrFail($id)->delete();
        });
    }
}
