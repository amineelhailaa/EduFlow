<?php

namespace App\Services;

use App\Contracts\StudentRepositoryInterface;
use App\Models\Inscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StudentService
{
    private const MAX_GROUP_SIZE = 25;

    public function __construct(
        private StudentRepositoryInterface $studentRepository,
    ) {
    }

    public function joinCourse(User $user, int $courseId): array
    {
        return DB::transaction(function () use ($user, $courseId): array {
            $course = $this->studentRepository->findCourseOrFail($courseId);

            $existingInscription = $this->studentRepository->findInscription($user->id, $course->id);

            if ($existingInscription !== null) {
                return [
                    'inscription' => $existingInscription->load(['course', 'group']),
                    'created' => false,
                ];
            }

            $group = null;

            foreach ($this->studentRepository->groupsForCourse($course->id) as $existingGroup) {
                if ($this->studentRepository->countGroupStudents($existingGroup->id) < self::MAX_GROUP_SIZE) {
                    $group = $existingGroup;
                    break;
                }
            }

            if ($group === null) {
                $group = $this->studentRepository->createGroup($course->id);
            }

            $inscription = $this->studentRepository->createInscription([
                'user_id' => $user->id,
                'cours_id' => $course->id,
                'group_id' => $group->id,
            ]);

            return [
                'inscription' => $inscription,
                'created' => true,
            ];
        });
    }
}
