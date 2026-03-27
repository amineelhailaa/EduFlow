<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteCourseApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_add_and_list_favorite_courses(): void
    {
        $student = User::query()->create([
            'name' => 'Student',
            'email' => 'student@example.com',
            'password' => 'password123',
            'role' => 'student',
        ]);

        $course = Course::query()->create([
            'name' => 'Laravel API',
        ]);

        $token = auth('api')->login($student);

        $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson("/api/v1/courses/{$course->id}/favorites")
            ->assertCreated()
            ->assertJsonPath('message', 'Course added to favorites.');

        $this->assertDatabaseHas('favorites', [
            'user_id' => $student->id,
            'cours_id' => $course->id,
        ]);

        $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson("/api/v1/courses/{$course->id}/favorites")
            ->assertOk()
            ->assertJsonPath('message', 'Course is already in favorites.');

        $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/v1/courses/favorites')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $course->id);
    }

    public function test_student_can_remove_favorite_course(): void
    {
        $student = User::query()->create([
            'name' => 'Student',
            'email' => 'student@example.com',
            'password' => 'password123',
            'role' => 'student',
        ]);

        $course = Course::query()->create([
            'name' => 'Laravel API',
        ]);

        $student->favCourses()->attach($course->id);
        $token = auth('api')->login($student);

        $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->deleteJson("/api/v1/courses/{$course->id}/favorites")
            ->assertOk()
            ->assertJsonPath('message', 'Course removed from favorites.');

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $student->id,
            'cours_id' => $course->id,
        ]);

        $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->deleteJson("/api/v1/courses/{$course->id}/favorites")
            ->assertOk()
            ->assertJsonPath('message', 'Course is not in favorites.');
    }

    public function test_teacher_cannot_manage_or_view_student_favorites(): void
    {
        $teacher = User::query()->create([
            'name' => 'Teacher',
            'email' => 'teacher@example.com',
            'password' => 'password123',
            'role' => 'teacher',
        ]);

        $course = Course::query()->create([
            'name' => 'Laravel API',
        ]);

        $token = auth('api')->login($teacher);

        $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson("/api/v1/courses/{$course->id}/favorites")
            ->assertForbidden()
            ->assertJsonPath('message', 'Only students can manage favorites.');

        $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/v1/courses/favorites')
            ->assertForbidden()
            ->assertJsonPath('message', 'Only students can access favorite courses.');
    }
}
