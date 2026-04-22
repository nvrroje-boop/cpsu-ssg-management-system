<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesSsgData;
use Tests\TestCase;

class ProfilePolicyTest extends TestCase
{
    use CreatesSsgData;
    use RefreshDatabase;

    public function test_student_can_update_profile_but_cannot_change_password(): void
    {
        $student = $this->createUserWithRole(User::ROLE_STUDENT, [
            'phone' => null,
            'course' => null,
        ]);

        $this->actingAs($student)
            ->patch(route('student.profile.update'), [
                'name' => 'Updated Student',
                'email' => $student->email,
                'phone' => '09171234567',
                'course' => 'BSIT',
            ])
            ->assertRedirect(route('student.profile'));

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'name' => 'Updated Student',
            'phone' => '09171234567',
            'course' => 'BSIT',
        ]);

        $this->actingAs($student)
            ->post(route('admin.profile.password'), [
                'current_password' => 'password',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ])
            ->assertForbidden();
    }
}
