<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Interest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseMatchingInterestsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_get_courses_matching_his_interests(): void
    {
        $student = User::query()->create([
            'name' => 'Student',
            'email' => 'student@example.com',
            'password' => 'password123',
            'role' => 'student',
        ]);

        $interestOne = Interest::query()->create(['name' => 'Web']);
        $interestTwo = Interest::query()->create(['name' => 'Data']);
        $otherInterest = Interest::query()->create(['name' => 'DevOps']);

        $student->studentInterests()->attach([$interestOne->id, $interestTwo->id]);

        $courseOne = Course::query()->create([
            'name' => 'Laravel API',
            'interest_id' => $interestOne->id,
        ]);

        $courseTwo = Course::query()->create([
            'name' => 'Python Data',
            'interest_id' => $interestTwo->id,
        ]);

        $otherCourse = Course::query()->create([
            'name' => 'Kubernetes',
            'interest_id' => $otherInterest->id,
        ]);

        $token = auth('api')->login($student);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/v1/courses/matching-interests');

        $response->assertOk()->assertJsonCount(2);

        $courseIds = collect($response->json())->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$courseOne->id, $courseTwo->id], $courseIds);
        $this->assertNotContains($otherCourse->id, $courseIds);
    }

    public function test_teacher_cannot_access_matching_interests_endpoint(): void
    {
        $teacher = User::query()->create([
            'name' => 'Teacher',
            'email' => 'teacher@example.com',
            'password' => 'password123',
            'role' => 'teacher',
        ]);

        $token = auth('api')->login($teacher);

        $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/v1/courses/matching-interests')
            ->assertForbidden()
            ->assertJsonPath('message', 'Only students can access courses by interests.');
    }
}
