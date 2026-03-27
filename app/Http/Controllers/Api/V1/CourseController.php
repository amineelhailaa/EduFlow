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
            new OA\Response(
                response: 200,
                description: 'Courses list',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(type: 'object'),
                    example: [[
                        'id' => 1,
                        'name' => 'Laravel API',
                        'description' => 'Build REST APIs with Laravel.',
                        'teacher_id' => 2,
                        'interest_id' => 1,
                        'price' => 300,
                    ]]
                )
            ),
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
            new OA\Response(
                response: 200,
                description: 'Favorite courses list',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(type: 'object'),
                    example: [[
                        'id' => 3,
                        'name' => 'PHP OOP',
                        'description' => null,
                        'teacher_id' => 5,
                        'interest_id' => 2,
                        'price' => 0,
                    ]]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'Only students can access favorite courses.',
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized',
                content: new OA\JsonContent(
                    example: ['message' => 'Unauthenticated.']
                )
            ),
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
            new OA\Response(
                response: 200,
                description: 'Matched courses list',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(type: 'object'),
                    example: [[
                        'id' => 7,
                        'name' => 'Python Data',
                        'description' => 'Data analysis basics.',
                        'teacher_id' => 4,
                        'interest_id' => 2,
                        'price' => 200,
                    ]]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'Only students can access courses by interests.',
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized',
                content: new OA\JsonContent(
                    example: ['message' => 'Unauthenticated.']
                )
            ),
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
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Laravel API'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Build REST APIs with Laravel.'),
                    new OA\Property(property: 'teacher_id', type: 'integer', nullable: true, example: 2),
                    new OA\Property(property: 'interest_id', type: 'integer', nullable: true, example: 1),
                    new OA\Property(property: 'price', type: 'integer', example: 300),
                ],
                example: [
                    'name' => 'Laravel API',
                    'description' => 'Build REST APIs with Laravel.',
                    'teacher_id' => 2,
                    'interest_id' => 1,
                    'price' => 300,
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Course created',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'Course created successfully.',
                        'course' => [
                            'id' => 1,
                            'name' => 'Laravel API',
                            'description' => 'Build REST APIs with Laravel.',
                            'teacher_id' => 2,
                            'interest_id' => 1,
                            'price' => 300,
                        ],
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'The given data was invalid.',
                        'errors' => [
                            'name' => ['The name field is required.'],
                        ],
                    ]
                )
            ),
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
            new OA\Response(
                response: 200,
                description: 'Course details',
                content: new OA\JsonContent(
                    example: [
                        'id' => 1,
                        'name' => 'Laravel API',
                        'description' => 'Build REST APIs with Laravel.',
                        'teacher_id' => 2,
                        'interest_id' => 1,
                        'price' => 300,
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Course not found',
                content: new OA\JsonContent(
                    example: ['message' => 'No query results for model [App\\Models\\Course] 999']
                )
            ),
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
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Laravel API Advanced'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Advanced API topics.'),
                    new OA\Property(property: 'teacher_id', type: 'integer', nullable: true, example: 2),
                    new OA\Property(property: 'interest_id', type: 'integer', nullable: true, example: 1),
                    new OA\Property(property: 'price', type: 'integer', example: 400),
                ],
                example: [
                    'name' => 'Laravel API Advanced',
                    'description' => 'Advanced API topics.',
                    'price' => 400,
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Course updated',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'Course updated successfully.',
                        'course' => [
                            'id' => 1,
                            'name' => 'Laravel API Advanced',
                            'description' => 'Advanced API topics.',
                            'teacher_id' => 2,
                            'interest_id' => 1,
                            'price' => 400,
                        ],
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'The given data was invalid.',
                        'errors' => [
                            'price' => ['The price must be an integer.'],
                        ],
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Course not found',
                content: new OA\JsonContent(
                    example: ['message' => 'No query results for model [App\\Models\\Course] 999']
                )
            ),
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
            new OA\Response(
                response: 200,
                description: 'Course deleted',
                content: new OA\JsonContent(
                    example: ['message' => 'Course deleted successfully.']
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Course not found',
                content: new OA\JsonContent(
                    example: ['message' => 'No query results for model [App\\Models\\Course] 999']
                )
            ),
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
