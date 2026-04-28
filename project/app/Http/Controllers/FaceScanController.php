<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\AttendanceService;
use App\Services\FaceScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FaceScanController extends Controller
{
    public function __construct(
        protected FaceScanService   $faceScanService,
        protected AttendanceService $attendanceService,
    ) {}


    public function receiveScan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'landmark_hash' => ['required', 'string', 'size:64'],
            'class_id'      => ['required', 'integer', 'exists:classes,id'],
        ]);

        $faceFeature = $this->faceScanService->findByHash($validated['landmark_hash']);

        if (!$faceFeature || !$faceFeature->student_id) {
            return response()->json([
                'message' => 'Aluno não identificado. Biometria não cadastrada.',
            ], Response::HTTP_NOT_FOUND);
        }

        $attendance = $this->attendanceService->registerFrequency([
            'student_id' => $faceFeature->student_id,
            'class_id'   => $validated['class_id'],
        ]);

        return response()->json([
            'message'    => 'Presença registrada com sucesso',
            'student_id' => $faceFeature->student_id,
            'attendance' => [
                'id'        => $attendance->id,
                'class_id'  => $attendance->class_id,
                'marked_at' => $attendance->marked_at,
                'status'    => $attendance->status,
            ],
        ], Response::HTTP_CREATED);
    }

    public function registerFace(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id'    => ['required', 'integer', 'exists:students,id'],
            'landmark_hash' => ['required', 'string', 'size:64'],
            'landmarks'     => ['nullable', 'array'],
        ]);

        $student = Student::findOrFail($validated['student_id']);
        $user    = $request->user();

        $hasAccess = $user->schools()->where('schools.id', $student->school_id)->exists();

        if (!$hasAccess) {
            return response()->json([
                'message' => 'Acesso negado. Você não leciona nesta escola.',
            ], Response::HTTP_FORBIDDEN);
        }

        $faceFeature = $this->faceScanService->registerFace($validated);

        return response()->json([
            'message' => 'Rosto cadastrado com sucesso',
            'data'    => [
                'id'            => $faceFeature->id,
                'student_id'    => $faceFeature->student_id,
                'landmark_hash' => $faceFeature->landmark_hash,
            ],
        ], Response::HTTP_CREATED);
    }
}
