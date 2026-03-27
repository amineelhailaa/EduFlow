<?php

namespace App\Services;

use App\Contracts\FavoriteRepositoryInterface;
use App\Models\User;

class FavoriteService
{
    public function __construct(
        private FavoriteRepositoryInterface $favoriteRepository,
    ) {
    }

    public function addFavorite(User $user, int $courseId): bool
    {
        $this->favoriteRepository->findCourseOrFail($courseId);

        return $this->favoriteRepository->addFavorite($user->id, $courseId);
    }

    public function removeFavorite(User $user, int $courseId): bool
    {
        $this->favoriteRepository->findCourseOrFail($courseId);

        return $this->favoriteRepository->removeFavorite($user->id, $courseId);
    }
}
