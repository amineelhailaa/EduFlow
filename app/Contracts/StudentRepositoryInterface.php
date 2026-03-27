<?php

namespace App\Contracts;

use App\Models\Course;
use App\Models\Group;
use App\Models\Inscription;
use Illuminate\Database\Eloquent\Collection;

interface StudentRepositoryInterface
{
    public function findCourseOrFail(int $courseId): Course;

    public function findInscription(int $userId, int $courseId): ?Inscription;

    public function findInscriptionByStripeSessionId(string $sessionId): ?Inscription;

    public function groupsForCourse(int $courseId): Collection;

    public function countGroupStudents(int $groupId): int;

    public function createGroup(int $courseId): Group;

    public function createInscription(array $attributes): Inscription;

    public function updateInscription(Inscription $inscription, array $attributes): Inscription;
}
