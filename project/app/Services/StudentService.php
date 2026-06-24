<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class StudentService
{
    public function getAll(User $user): Collection
    {
        $query = Student::with(['school', 'schoolClass']);

        if (!Gate::forUser($user)->allows('access-admin')) {
            $schoolIds = $user->schools()->pluck('schools.id');
            $query->whereIn('school_id', $schoolIds);
        }

        return $query->orderBy('name')->get();
    }

    public function store(array $validated): Student
    {
        return DB::transaction(function () use ($validated) {
            return Student::create($validated);
        });
    }

    public function update(Student $student, array $validated): Student
    {
        return DB::transaction(function () use ($student, $validated) {
            $student->update($validated);
            return $student;
        });
    }

    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            Student::findOrFail($id)->delete();
        });
    }
}
