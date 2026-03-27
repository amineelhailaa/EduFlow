<?php

namespace App\Services;

use App\Contracts\CourseRepositoryInterface;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

class CourseService
{
    public function __construct(
        private CourseRepositoryInterface $courseRepository,
    ) {
    }

    public function all(): Collection
    {
        return $this->courseRepository->all();
    }

    public function favoritesByStudent(int $studentId): Collection
    {
        return $this->courseRepository->favoriteCoursesByStudent($studentId);
    }

    public function byStudentInterests(int $studentId): Collection
    {
        return $this->courseRepository->coursesMatchingStudentInterests($studentId);
    }

    public function findOrFail(int $id): Course
    {
        return $this->courseRepository->findOrFail($id);
    }

    public function create(array $attributes): Course
    {
        return $this->courseRepository->create($attributes);
    }

    public function update(Course $course, array $attributes): Course
    {
        return $this->courseRepository->update($course, $attributes);
    }

    public function delete(Course $course): bool
    {
        return $this->courseRepository->delete($course);
    }
}
