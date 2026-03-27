<?php

namespace App\Repositories;

use App\Contracts\FavoriteRepositoryInterface;
use App\Models\Course;
use App\Models\User;

class FavoriteRepository implements FavoriteRepositoryInterface
{
    public function findCourseOrFail(int $courseId): Course
    {
        return Course::query()->findOrFail($courseId);
    }

    public function addFavorite(int $userId, int $courseId): bool
    {
        $user = User::query()->findOrFail($userId);
        $result = $user->favCourses()->syncWithoutDetaching([$courseId]);

        return $result['attached'] !== [];
    }

    public function removeFavorite(int $userId, int $courseId): bool
    {
        $user = User::query()->findOrFail($userId);

        return $user->favCourses()->detach([$courseId]) > 0;
    }
}
