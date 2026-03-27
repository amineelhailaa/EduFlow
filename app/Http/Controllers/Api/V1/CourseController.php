<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCourseRequest;
use App\Http\Requests\Api\V1\UpdateCourseRequest;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CourseController extends Controller
{
    public function __construct(
        private CourseService $courseService,
    ) {
    }

    #[OA\Get(
        path: '/api/v1/courses',
        summary: 'List all courses',
        tags: ['Courses'],
        responses: [
            new OA\Response(response: 200, description: 'Courses list'),
        ]
    )]
    public function index(): JsonResponse
    {
        return response()->json($this->courseService->all());
    }

    #[OA\Get(
        path: '/api/v1/courses/favorites',
        summary: 'Get favorite courses for authenticated student',
        tags: ['Courses'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Favorite courses list'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
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

    #[OA\Get(
        path: '/api/v1/courses/matching-interests',
        summary: 'Get courses matching authenticated student interests',
        tags: ['Courses'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Matched courses list'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function matchingInterests(Request $request): JsonResponse
    {
        $user = $request->user('api');

        if ($user->role !== 'student') {
            return response()->json([
                'message' => 'Only students can access courses by interests.',
            ], 403);
        }

        return response()->json($this->courseService->byStudentInterests($user->id));
    }

    #[OA\Post(
        path: '/api/v1/courses',
        summary: 'Create a course',
        tags: ['Courses'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: 'object')
        ),
        responses: [
            new OA\Response(response: 201, description: 'Course created'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreCourseRequest $request): JsonResponse
    {
        $course = $this->courseService->create($request->validated());

        return response()->json([
            'message' => 'Course created successfully.',
            'course' => $course,
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/courses/{course}',
        summary: 'Show one course',
        tags: ['Courses'],
        parameters: [
            new OA\Parameter(name: 'course', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Course details'),
            new OA\Response(response: 404, description: 'Course not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        return response()->json($this->courseService->findOrFail($id));
    }

    #[OA\Put(
        path: '/api/v1/courses/{course}',
        summary: 'Update a course',
        tags: ['Courses'],
        parameters: [
            new OA\Parameter(name: 'course', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: 'object')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Course updated'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 404, description: 'Course not found'),
        ]
    )]
    public function update(UpdateCourseRequest $request, Course $course): JsonResponse
    {
        $course = $this->courseService->update($course, $request->validated());

        return response()->json([
            'message' => 'Course updated successfully.',
            'course' => $course,
        ]);
    }

    #[OA\Delete(
        path: '/api/v1/courses/{course}',
        summary: 'Delete a course',
        tags: ['Courses'],
        parameters: [
            new OA\Parameter(name: 'course', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Course deleted'),
            new OA\Response(response: 404, description: 'Course not found'),
        ]
    )]
    public function destroy(Course $course): JsonResponse
    {
        $this->courseService->delete($course);

        return response()->json([
            'message' => 'Course deleted successfully.',
        ]);
    }
}
