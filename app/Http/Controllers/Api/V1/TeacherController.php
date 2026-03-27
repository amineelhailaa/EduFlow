<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TeacherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TeacherController extends Controller
{
    public function __construct(
        private TeacherService $teacherService,
    ) {
    }

    #[OA\Get(
        path: '/api/v1/teacher/courses/students',
        summary: 'Get enrolled students in teacher courses',
        tags: ['Teachers'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Students grouped by courses',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'Enrolled students retrieved successfully.',
                        'courses' => [[
                            'id' => 1,
                            'name' => 'Laravel API',
                            'students' => [[
                                'id' => 10,
                                'name' => 'Student One',
                                'email' => 'student1@example.com',
                            ]],
                        ]],
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden',
                content: new OA\JsonContent(
                    example: ['message' => 'Only teachers can access this resource.']
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

    #[OA\Get(
        path: '/api/v1/teacher/courses/groups',
        summary: 'Get groups for teacher courses',
        tags: ['Teachers'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Course groups',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'Course groups retrieved successfully.',
                        'courses' => [[
                            'id' => 1,
                            'name' => 'Laravel API',
                            'groups' => [
                                ['id' => 1, 'cours_id' => 1],
                                ['id' => 2, 'cours_id' => 1],
                            ],
                        ]],
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden',
                content: new OA\JsonContent(
                    example: ['message' => 'Only teachers can access this resource.']
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
    public function courseGroups(Request $request): JsonResponse
    {
        $teacher = $request->user('api');

        if ($teacher->role !== 'teacher') {
            return response()->json([
                'message' => 'Only teachers can access this resource.',
            ], 403);
        }

        return response()->json([
            'message' => 'Course groups retrieved successfully.',
            'courses' => $this->teacherService->groupsInMyCourses($teacher->id),
        ]);
    }

    #[OA\Get(
        path: '/api/v1/teacher/courses/groups/participants',
        summary: 'Get participants in each group of teacher courses',
        tags: ['Teachers'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Group participants',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'Group participants retrieved successfully.',
                        'courses' => [[
                            'id' => 1,
                            'name' => 'Laravel API',
                            'groups' => [[
                                'id' => 1,
                                'cours_id' => 1,
                                'students' => [[
                                    'id' => 10,
                                    'name' => 'Student One',
                                    'email' => 'student1@example.com',
                                ]],
                            ]],
                        ]],
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden',
                content: new OA\JsonContent(
                    example: ['message' => 'Only teachers can access this resource.']
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
    public function groupParticipants(Request $request): JsonResponse
    {
        $teacher = $request->user('api');

        if ($teacher->role !== 'teacher') {
            return response()->json([
                'message' => 'Only teachers can access this resource.',
            ], 403);
        }

        return response()->json([
            'message' => 'Group participants retrieved successfully.',
            'courses' => $this->teacherService->participantsByGroupInMyCourses($teacher->id),
        ]);
    }
}
