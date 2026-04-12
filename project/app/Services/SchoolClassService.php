<?php

namespace App\Services;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SchoolClassService
{
    public function getAll(User $user): Collection
    {
        if (Gate::forUser($user)->allows('access-admin')) {
            return SchoolClass::with('school')->orderBy('name')->get();
        }

        $schoolIds = $user->schools()->pluck('schools.id');

        return SchoolClass::with('school')
            ->whereIn('school_id', $schoolIds)
            ->orderBy('name')
            ->get();
    }

    public function getAllBySchool(int $schoolId): Collection
    {
        return SchoolClass::where('school_id', $schoolId)->orderBy('name')->get();
    }

    public function store(array $validated): SchoolClass
    {
        return DB::transaction(function () use ($validated) {
            return SchoolClass::create($validated);
        });
    }

    public function update(SchoolClass $schoolClass, array $validated): SchoolClass
    {
        return DB::transaction(function () use ($schoolClass, $validated) {
            $schoolClass->update($validated);
            return $schoolClass;
        });
    }

    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            SchoolClass::findOrFail($id)->delete();
        });
    }
}
