<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\FavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class FavoriteController extends Controller
{
    public function __construct(
        private FavoriteService $favoriteService,
    ) {
    }

    #[OA\Post(
        path: '/api/v1/courses/{course}/favorites',
        summary: 'Add a course to favorites',
        tags: ['Favorites'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'course', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Favorite created',
                content: new OA\JsonContent(
                    example: ['message' => 'Course added to favorites.']
                )
            ),
            new OA\Response(
                response: 200,
                description: 'Already in favorites',
                content: new OA\JsonContent(
                    example: ['message' => 'Course is already in favorites.']
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden',
                content: new OA\JsonContent(
                    example: ['message' => 'Only students can manage favorites.']
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized',
                content: new OA\JsonContent(
                    example: ['message' => 'Unauthenticated.']
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
    public function store(Request $request, Course $course): JsonResponse
    {
        $user = $request->user('api');

        if ($user->role !== 'student') {
            return response()->json([
                'message' => 'Only students can manage favorites.',
            ], 403);
        }

        $created = $this->favoriteService->addFavorite($user, $course->id);

        return response()->json([
            'message' => $created
                ? 'Course added to favorites.'
                : 'Course is already in favorites.',
        ], $created ? 201 : 200);
    }

    #[OA\Delete(
        path: '/api/v1/courses/{course}/favorites',
        summary: 'Remove a course from favorites',
        tags: ['Favorites'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'course', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Favorite removed or already absent',
                content: new OA\JsonContent(
                    example: ['message' => 'Course removed from favorites.']
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden',
                content: new OA\JsonContent(
                    example: ['message' => 'Only students can manage favorites.']
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized',
                content: new OA\JsonContent(
                    example: ['message' => 'Unauthenticated.']
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
    public function destroy(Request $request, Course $course): JsonResponse
    {
        $user = $request->user('api');

        if ($user->role !== 'student') {
            return response()->json([
                'message' => 'Only students can manage favorites.',
            ], 403);
        }

        $removed = $this->favoriteService->removeFavorite($user, $course->id);

        return response()->json([
            'message' => $removed
                ? 'Course removed from favorites.'
                : 'Course is not in favorites.',
        ]);
    }
}
