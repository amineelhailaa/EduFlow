<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\FavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct(
        private FavoriteService $favoriteService,
    ) {
    }

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
