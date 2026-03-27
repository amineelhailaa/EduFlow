<?php

namespace App\Contracts;

use App\Models\Course;

interface FavoriteRepositoryInterface
{
    public function findCourseOrFail(int $courseId): Course;

    public function addFavorite(int $userId, int $courseId): bool;

    public function removeFavorite(int $userId, int $courseId): bool;
}
