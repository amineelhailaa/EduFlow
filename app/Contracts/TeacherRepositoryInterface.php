<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface TeacherRepositoryInterface
{
    public function coursesWithStudents(int $teacherId): Collection;

    public function coursesWithGroups(int $teacherId): Collection;

    public function coursesWithGroupsAndParticipants(int $teacherId): Collection;
}
