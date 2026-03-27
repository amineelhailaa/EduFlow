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

    public function coursesWithGroups(int $teacherId): Collection
    {
        return Course::query()
            ->where('teacher_id', $teacherId)
            ->with([
                'groups' => function ($query): void {
                    $query->orderBy('id');
                },
            ])
            ->orderBy('id')
            ->get();
    }

    public function coursesWithGroupsAndParticipants(int $teacherId): Collection
    {
        return Course::query()
            ->where('teacher_id', $teacherId)
            ->with([
                'groups' => function ($query): void {
                    $query->orderBy('id')
                        ->with([
                            'students' => function ($studentQuery): void {
                                $studentQuery->select('users.id', 'users.name', 'users.email');
                            },
                        ]);
                },
            ])
            ->orderBy('id')
            ->get();
    }
}
