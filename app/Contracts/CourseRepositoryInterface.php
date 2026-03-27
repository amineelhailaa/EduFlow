<?php

namespace App\Contracts;

use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

interface CourseRepositoryInterface
{
    public function all(): Collection;

    public function favoriteCoursesByStudent(int $studentId): Collection;

    public function coursesMatchingStudentInterests(int $studentId): Collection;

    public function findOrFail(int $id): Course;

    public function create(array $attributes): Course;

    public function update(Course $course, array $attributes): Course;

    public function delete(Course $course): bool;
}
