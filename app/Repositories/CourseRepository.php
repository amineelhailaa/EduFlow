<?php

namespace App\Repositories;

use App\Contracts\CourseRepositoryInterface;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

class CourseRepository implements CourseRepositoryInterface
{
    private array $relations = [
        'teacher',
        'interest',
        'groups',
        'students',
    ];

    public function all(): Collection
    {
        return Course::query()
            ->with($this->relations)
            ->get();
    }

    public function favoriteCoursesByStudent(int $studentId): Collection
    {
        return Course::query()
            ->whereHas('favUsers', function ($query) use ($studentId): void {
                $query->where('users.id', $studentId);
            })
            ->with($this->relations)
            ->get();
    }

    public function coursesMatchingStudentInterests(int $studentId): Collection
    {
        return Course::query()
            ->whereIn('interest_id', function ($query) use ($studentId): void {
                $query->select('interest_id')
                    ->from('student_interests')
                    ->where('user_id', $studentId);
            })
            ->with($this->relations)
            ->get();
    }

    public function findOrFail(int $id): Course
    {
        return Course::query()
            ->with($this->relations)
            ->findOrFail($id);
    }

    public function create(array $attributes): Course
    {
        $course = Course::query()->create($attributes);

        return $course->load($this->relations);
    }

    public function update(Course $course, array $attributes): Course
    {
        $course->update($attributes);

        return $course->load($this->relations);
    }

    public function delete(Course $course): bool
    {
        return (bool) $course->delete();
    }
}
