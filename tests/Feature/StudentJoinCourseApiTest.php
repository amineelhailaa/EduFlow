<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Group;
use App\Models\Inscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentJoinCourseApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_joins_the_first_group_with_free_slots(): void
    {
        $student = User::query()->create([
            'name' => 'Student',
            'email' => 'student@example.com',
            'password' => 'password123',
        ]);

        $course = Course::query()->create([
            'name' => 'Laravel Basics',
        ]);
        $firstGroup = Group::query()->create([
            'cours_id' => $course->id,
        ]);
        Group::query()->create([
            'cours_id' => $course->id,
        ]);

        for ($index = 1; $index <= 24; $index++) {
            $existingStudent = User::query()->create([
                'name' => "Existing {$index}",
                'email' => "existing{$index}@example.com",
                'password' => 'password123',
            ]);

            Inscription::query()->create([
                'user_id' => $existingStudent->id,
                'cours_id' => $course->id,
                'group_id' => $firstGroup->id,
            ]);
        }

        $token = auth('api')->login($student);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson("/api/v1/courses/{$course->id}/join");

        $response
            ->assertCreated()
            ->assertJsonPath('inscription.group_id', $firstGroup->id);

        $this->assertDatabaseHas('inscriptions', [
            'user_id' => $student->id,
            'cours_id' => $course->id,
            'group_id' => $firstGroup->id,
        ]);
    }

    public function test_student_skips_full_groups_and_joins_the_next_group_with_space(): void
    {
        $student = User::query()->create([
            'name' => 'Student',
            'email' => 'student@example.com',
            'password' => 'password123',
        ]);

        $course = Course::query()->create([
            'name' => 'Laravel Basics',
        ]);
        $fullGroup = Group::query()->create([
            'cours_id' => $course->id,
        ]);
        $availableGroup = Group::query()->create([
            'cours_id' => $course->id,
        ]);

        for ($index = 1; $index <= 25; $index++) {
            $existingStudent = User::query()->create([
                'name' => "Existing {$index}",
                'email' => "existing{$index}@example.com",
                'password' => 'password123',
            ]);

            Inscription::query()->create([
                'user_id' => $existingStudent->id,
                'cours_id' => $course->id,
                'group_id' => $fullGroup->id,
            ]);
        }

        for ($index = 26; $index <= 30; $index++) {
            $existingStudent = User::query()->create([
                'name' => "Existing {$index}",
                'email' => "existing{$index}@example.com",
                'password' => 'password123',
            ]);

            Inscription::query()->create([
                'user_id' => $existingStudent->id,
                'cours_id' => $course->id,
                'group_id' => $availableGroup->id,
            ]);
        }

        $token = auth('api')->login($student);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson("/api/v1/courses/{$course->id}/join");

        $response
            ->assertCreated()
            ->assertJsonPath('inscription.group_id', $availableGroup->id);

        $this->assertDatabaseCount('groups', 2);
        $this->assertDatabaseHas('inscriptions', [
            'user_id' => $student->id,
            'cours_id' => $course->id,
            'group_id' => $availableGroup->id,
        ]);
    }

    public function test_student_gets_a_new_group_when_all_groups_are_full(): void
    {
        $student = User::query()->create([
            'name' => 'Student',
            'email' => 'student@example.com',
            'password' => 'password123',
        ]);

        $course = Course::query()->create([
            'name' => 'Laravel Basics',
        ]);
        $firstFullGroup = Group::query()->create([
            'cours_id' => $course->id,
        ]);
        $secondFullGroup = Group::query()->create([
            'cours_id' => $course->id,
        ]);

        foreach ([$firstFullGroup, $secondFullGroup] as $group) {
            for ($index = 1; $index <= 25; $index++) {
                $existingStudent = User::query()->create([
                    'name' => "{$group->id}-{$index}",
                    'email' => "{$group->id}-{$index}@example.com",
                    'password' => 'password123',
                ]);

                Inscription::query()->create([
                    'user_id' => $existingStudent->id,
                    'cours_id' => $course->id,
                    'group_id' => $group->id,
                ]);
            }
        }

        $token = auth('api')->login($student);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson("/api/v1/courses/{$course->id}/join");

        $response->assertCreated();

        $newGroupId = $response->json('inscription.group_id');

        $this->assertNotSame($firstFullGroup->id, $newGroupId);
        $this->assertNotSame($secondFullGroup->id, $newGroupId);
        $this->assertDatabaseCount('groups', 3);
        $this->assertDatabaseHas('groups', [
            'id' => $newGroupId,
            'cours_id' => $course->id,
        ]);
    }

    public function test_student_cannot_join_the_same_course_twice(): void
    {
        $student = User::query()->create([
            'name' => 'Student',
            'email' => 'student@example.com',
            'password' => 'password123',
        ]);

        $course = Course::query()->create([
            'name' => 'Laravel Basics',
        ]);
        $group = Group::query()->create([
            'cours_id' => $course->id,
        ]);

        Inscription::query()->create([
            'user_id' => $student->id,
            'cours_id' => $course->id,
            'group_id' => $group->id,
        ]);

        $token = auth('api')->login($student);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson("/api/v1/courses/{$course->id}/join");

        $response
            ->assertOk()
            ->assertJsonPath('message', 'User is already enrolled in this course.')
            ->assertJsonPath('inscription.group_id', $group->id);

        $this->assertDatabaseCount('inscriptions', 1);
    }

    public function test_student_can_leave_a_course(): void
    {
        $student = User::query()->create([
            'name' => 'Student',
            'email' => 'student@example.com',
            'password' => 'password123',
        ]);

        $course = Course::query()->create([
            'name' => 'Laravel Basics',
        ]);
        $group = Group::query()->create([
            'cours_id' => $course->id,
        ]);

        Inscription::query()->create([
            'user_id' => $student->id,
            'cours_id' => $course->id,
            'group_id' => $group->id,
        ]);

        $token = auth('api')->login($student);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->deleteJson("/api/v1/courses/{$course->id}/leave");

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Left course successfully.');

        $this->assertDatabaseMissing('inscriptions', [
            'user_id' => $student->id,
            'cours_id' => $course->id,
        ]);
    }

    public function test_leave_course_returns_message_when_student_is_not_enrolled(): void
    {
        $student = User::query()->create([
            'name' => 'Student',
            'email' => 'student@example.com',
            'password' => 'password123',
        ]);

        $course = Course::query()->create([
            'name' => 'Laravel Basics',
        ]);
        $token = auth('api')->login($student);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->deleteJson("/api/v1/courses/{$course->id}/leave");

        $response
            ->assertOk()
            ->assertJsonPath('message', 'User is not enrolled in this course.');
    }
}
