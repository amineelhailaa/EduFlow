<?php

namespace App\Repositories;

use App\Contracts\TeacherRepositoryInterface;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

class TeacherRepository implements TeacherRepositoryInterface
{
    public function coursesWithStudents(int $teacherId): Collection
    {
        return Course::query()
            ->where('teacher_id', $teacherId)
            ->with([
                'students' => function ($query): void {
                    $query->select('users.id', 'users.name', 'users.email');
                },
            ])
            ->orderBy('id')
            ->get();
    }
}
