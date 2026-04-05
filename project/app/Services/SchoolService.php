<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SchoolService
{

    public function getAll(): Collection
    {
        return School::all();
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

    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            School::findOrFail($id)->delete();
        });
    }
}
