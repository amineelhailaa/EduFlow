<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TeacherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function __construct(
        private TeacherService $teacherService,
    ) {
    }

    public function enrolledStudents(Request $request): JsonResponse
    {
        $teacher = $request->user('api');

        if ($teacher->role !== 'teacher') {
            return response()->json([
                'message' => 'Only teachers can access this resource.',
            ], 403);
        }

        return response()->json([
            'message' => 'Enrolled students retrieved successfully.',
            'courses' => $this->teacherService->studentsInMyCourses($teacher->id),
        ]);
    }
}
