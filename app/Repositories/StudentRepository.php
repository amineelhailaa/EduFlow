<?php

namespace App\Repositories;

use App\Contracts\StudentRepositoryInterface;
use App\Models\Course;
use App\Models\Group;
use App\Models\Inscription;
use Illuminate\Database\Eloquent\Collection;

class StudentRepository implements StudentRepositoryInterface
{
    public function findCourseOrFail(int $courseId): Course
    {
        return Course::query()->findOrFail($courseId);
    }

    public function findInscription(int $userId, int $courseId): ?Inscription
    {
        return Inscription::query()
            ->where('user_id', $userId)
            ->where('cours_id', $courseId)
            ->first();
    }

    public function findInscriptionByStripeSessionId(string $sessionId): ?Inscription
    {
        return Inscription::query()
            ->where('stripe_session_id', $sessionId)
            ->first();
    }

    public function groupsForCourse(int $courseId): Collection
    {
        return Group::query()
            ->where('cours_id', $courseId)
            ->orderBy('id')
            ->get();
    }

    public function countGroupStudents(int $groupId): int
    {
        return Inscription::query()
            ->where('group_id', $groupId)
            ->count();
    }

    public function createGroup(int $courseId): Group
    {
        return Group::query()->create([
            'cours_id' => $courseId,
        ]);
    }

    public function createInscription(array $attributes): Inscription
    {
        return Inscription::query()
            ->create($attributes)
            ->load(['course', 'group']);
    }

    public function updateInscription(Inscription $inscription, array $attributes): Inscription
    {
        $inscription->update($attributes);

        return $inscription->fresh(['course', 'group']);
    }

    public function deleteInscription(Inscription $inscription): bool
    {
        return (bool) $inscription->delete();
    }
}
