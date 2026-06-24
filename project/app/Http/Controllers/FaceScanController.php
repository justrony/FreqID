<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\AttendanceService;
use App\Services\FaceScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FaceScanController extends Controller
{
    public function __construct(
        protected FaceScanService   $faceScanService,
        protected AttendanceService $attendanceService,
    ) {}

    public function registerFace(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'embedding'  => ['required', 'array'],
        ]);

        $student = Student::findOrFail($validated['student_id']);
        $user    = $request->user();

        $hasAccess = $user->schools()->where('schools.id', $student->school_id)->exists();

        if (!$hasAccess) {
            return response()->json([
                'message' => 'Acesso negado. Você não leciona nesta escola.',
            ], ResponseAlias::HTTP_FORBIDDEN);
        }

        $faceFeature = $this->faceScanService->registerFace($validated);

        return response()->json([
            'message' => 'Rosto cadastrado com sucesso',
            'data'    => [
                'id'         => $faceFeature->id,
                'student_id' => $faceFeature->student_id,
            ],
        ], ResponseAlias::HTTP_CREATED);
    }
}
