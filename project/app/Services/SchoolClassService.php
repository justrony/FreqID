<?php

namespace App\Services;

use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SchoolClassService
{
    public function getAll(): Collection
    {
        return SchoolClass::with('school')->orderBy('name')->get();
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
