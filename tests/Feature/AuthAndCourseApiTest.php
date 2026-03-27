<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Interest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndCourseApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_a_jwt_token(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Amine',
            'email' => 'amine@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email'],
                'token',
                'token_type',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'amine@example.com',
        ]);
    }

    public function test_user_can_login_and_receive_a_jwt_token(): void
    {
        User::query()->create([
            'name' => 'Amine',
            'email' => 'amine@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'amine@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email'],
                'token',
                'token_type',
            ]);
    }

    public function test_course_crud_endpoints_work(): void
    {
        $teacher = User::query()->create([
            'name' => 'Teacher',
            'email' => 'teacher@example.com',
            'password' => 'password123',
        ]);

        $interest = Interest::query()->create();

        $storeResponse = $this->postJson('/api/v1/courses', [
            'teacher_id' => $teacher->id,
            'interest_id' => $interest->id,
        ]);

        $storeResponse
            ->assertCreated()
            ->assertJsonPath('course.teacher_id', $teacher->id)
            ->assertJsonPath('course.interest_id', $interest->id);

        $courseId = $storeResponse->json('course.id');

        $this->getJson('/api/v1/courses')
            ->assertOk()
            ->assertJsonCount(1);

        $this->getJson("/api/v1/courses/{$courseId}")
            ->assertOk()
            ->assertJsonPath('id', $courseId);

        $updatedTeacher = User::query()->create([
            'name' => 'Updated Teacher',
            'email' => 'updated-teacher@example.com',
            'password' => 'password123',
        ]);

        $this->putJson("/api/v1/courses/{$courseId}", [
            'teacher_id' => $updatedTeacher->id,
            'interest_id' => null,
        ])
            ->assertOk()
            ->assertJsonPath('course.teacher_id', $updatedTeacher->id)
            ->assertJsonPath('course.interest_id', null);

        $this->deleteJson("/api/v1/courses/{$courseId}")
            ->assertOk()
            ->assertJsonPath('message', 'Course deleted successfully.');

        $this->assertDatabaseMissing('cours', [
            'id' => $courseId,
        ]);
    }

    public function test_invalid_login_is_rejected(): void
    {
        User::query()->create([
            'name' => 'Amine',
            'email' => 'amine@example.com',
            'password' => 'password123',
        ]);

        $this->postJson('/api/v1/login', [
            'email' => 'amine@example.com',
            'password' => 'wrong-password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid credentials.');
    }

    public function test_user_can_request_reset_password_token(): void
    {
        User::query()->create([
            'name' => 'Amine',
            'email' => 'amine@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/forgot-password', [
            'email' => 'amine@example.com',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'If the email exists, reset token was generated.')
            ->assertJsonStructure([
                'message',
                'reset_token',
                'expires_in_minutes',
            ]);

        $this->assertNotNull($response->json('reset_token'));
    }

    public function test_user_can_reset_password(): void
    {
        User::query()->create([
            'name' => 'Amine',
            'email' => 'amine@example.com',
            'password' => 'password123',
        ]);

        $forgotResponse = $this->postJson('/api/v1/forgot-password', [
            'email' => 'amine@example.com',
        ]);

        $token = $forgotResponse->json('reset_token');

        $this->assertNotNull($token);

        $this->postJson('/api/v1/reset-password', [
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Password reset successful.');

        $this->postJson('/api/v1/login', [
            'email' => 'amine@example.com',
            'password' => 'newpassword123',
        ])->assertOk();
    }
}
