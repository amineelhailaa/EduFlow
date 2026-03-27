<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCourseRequest;
use App\Http\Requests\Api\V1\UpdateCourseRequest;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct(
        private CourseService $courseService,
    ) {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->courseService->all());
    }

    public function favorites(Request $request): JsonResponse
    {
        $user = $request->user('api');

        if ($user->role !== 'student') {
            return response()->json([
                'message' => 'Only students can access favorite courses.',
            ], 403);
        }

        return response()->json($this->courseService->favoritesByStudent($user->id));
    }

    public function store(StoreCourseRequest $request): JsonResponse
    {
        $course = $this->courseService->create($request->validated());

        return response()->json([
            'message' => 'Course created successfully.',
            'course' => $course,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->courseService->findOrFail($id));
    }

    public function update(UpdateCourseRequest $request, Course $course): JsonResponse
    {
        $course = $this->courseService->update($course, $request->validated());

        return response()->json([
            'message' => 'Course updated successfully.',
            'course' => $course,
        ]);
    }

    public function destroy(Course $course): JsonResponse
    {
        $this->courseService->delete($course);

        return response()->json([
            'message' => 'Course deleted successfully.',
        ]);
    }
}
