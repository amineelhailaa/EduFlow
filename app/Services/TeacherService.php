<?php

namespace App\Services;

use App\Contracts\TeacherRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TeacherService
{
    public function __construct(
        private TeacherRepositoryInterface $teacherRepository,
    ) {
    }

    public function studentsInMyCourses(int $teacherId): Collection
    {
        return $this->teacherRepository->coursesWithStudents($teacherId);
    }
}
