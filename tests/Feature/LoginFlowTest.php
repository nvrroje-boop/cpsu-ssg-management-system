<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesSsgData;
use Tests\TestCase;

class LoginFlowTest extends TestCase
{
    use CreatesSsgData;
    use RefreshDatabase;

    public function test_login_page_renders_with_password_toggle_ui(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Portal Login')
            ->assertSee('passwordToggle', false)
            ->assertSee('Show');
    }

    public function test_student_can_log_in_with_valid_credentials(): void
    {
        $student = $this->createUserWithRole(User::ROLE_STUDENT, [
            'email' => 'student@example.com',
            'password' => 'Campus2026!',
        ]);

        $this->post(route('login.attempt'), [
            'email' => $student->email,
            'password' => 'Campus2026!',
        ])->assertRedirect(route('student.dashboard'));

        $this->assertAuthenticatedAs($student);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $this->createUserWithRole(User::ROLE_STUDENT, [
            'email' => 'student@example.com',
            'password' => 'Campus2026!',
        ]);

        $this->from(route('login'))
            ->post(route('login.attempt'), [
                'email' => 'student@example.com',
                'password' => 'WrongPass123!',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
