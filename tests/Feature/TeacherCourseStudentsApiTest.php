<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Inscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherCourseStudentsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_view_students_enrolled_in_his_courses(): void
    {
        $teacher = User::query()->create([
            'name' => 'Teacher One',
            'email' => 'teacher1@example.com',
            'password' => 'password123',
            'role' => 'teacher',
        ]);

        $otherTeacher = User::query()->create([
            'name' => 'Teacher Two',
            'email' => 'teacher2@example.com',
            'password' => 'password123',
            'role' => 'teacher',
        ]);

        $studentOne = User::query()->create([
            'name' => 'Student One',
            'email' => 'student1@example.com',
            'password' => 'password123',
            'role' => 'student',
        ]);

        $studentTwo = User::query()->create([
            'name' => 'Student Two',
            'email' => 'student2@example.com',
            'password' => 'password123',
            'role' => 'student',
        ]);

        $studentThree = User::query()->create([
            'name' => 'Student Three',
            'email' => 'student3@example.com',
            'password' => 'password123',
            'role' => 'student',
        ]);

        $teacherCourseOne = Course::query()->create([
            'name' => 'Laravel API',
            'teacher_id' => $teacher->id,
        ]);

        $teacherCourseTwo = Course::query()->create([
            'name' => 'PHP OOP',
            'teacher_id' => $teacher->id,
        ]);

        $otherTeacherCourse = Course::query()->create([
            'name' => 'Vue Basics',
            'teacher_id' => $otherTeacher->id,
        ]);

        Inscription::query()->create([
            'user_id' => $studentOne->id,
            'cours_id' => $teacherCourseOne->id,
            'payment_status' => Inscription::PAYMENT_PAID,
        ]);
        Inscription::query()->create([
            'user_id' => $studentTwo->id,
            'cours_id' => $teacherCourseOne->id,
            'payment_status' => Inscription::PAYMENT_PAID,
        ]);
        Inscription::query()->create([
            'user_id' => $studentThree->id,
            'cours_id' => $teacherCourseTwo->id,
            'payment_status' => Inscription::PAYMENT_PAID,
        ]);
        Inscription::query()->create([
            'user_id' => $studentOne->id,
            'cours_id' => $otherTeacherCourse->id,
            'payment_status' => Inscription::PAYMENT_PAID,
        ]);

        $token = auth('api')->login($teacher);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/v1/teacher/courses/students');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Enrolled students retrieved successfully.')
            ->assertJsonCount(2, 'courses');

        $courseIds = collect($response->json('courses'))
            ->pluck('id')
            ->all();

        $this->assertEqualsCanonicalizing(
            [$teacherCourseOne->id, $teacherCourseTwo->id],
            $courseIds
        );
        $this->assertNotContains($otherTeacherCourse->id, $courseIds);
    }

    public function test_student_cannot_access_teacher_students_endpoint(): void
    {
        $student = User::query()->create([
            'name' => 'Student',
            'email' => 'student@example.com',
            'password' => 'password123',
            'role' => 'student',
        ]);

        $token = auth('api')->login($student);

        $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/v1/teacher/courses/students')
            ->assertForbidden()
            ->assertJsonPath('message', 'Only teachers can access this resource.');
    }
}
