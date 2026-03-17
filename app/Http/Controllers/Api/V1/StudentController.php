<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct(
        private StudentService $studentService,
    ) {
    }

    public function joinCourse(Request $request, Course $course): JsonResponse
    {
        $result = $this->studentService->joinCourse($request->user('api'), $course->id);

        return response()->json([
            'message' => $result['created']
                ? 'Joined course successfully.'
                : 'User is already enrolled in this course.',
            'inscription' => $result['inscription'],
        ], $result['created'] ? 201 : 200);
    }
}
