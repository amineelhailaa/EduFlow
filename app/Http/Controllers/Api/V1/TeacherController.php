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
            new OA\Response(response: 200, description: 'Students grouped by courses'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 401, description: 'Unauthorized'),
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
            new OA\Response(response: 200, description: 'Course groups'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 401, description: 'Unauthorized'),
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
            new OA\Response(response: 200, description: 'Group participants'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 401, description: 'Unauthorized'),
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
