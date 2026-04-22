<?php

namespace Tests\Feature;

use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesSsgData;
use Tests\TestCase;

class NotificationVisibilityTest extends TestCase
{
    use CreatesSsgData;
    use RefreshDatabase;

    public function test_student_notification_feed_includes_personal_and_student_broadcast_only(): void
    {
        $student = $this->createUserWithRole(User::ROLE_STUDENT);
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);

        SystemNotification::query()->create([
            'user_id' => null,
            'title' => 'Student Broadcast',
            'message' => 'Visible to students.',
            'type' => 'event',
            'target_role' => 'student',
            'link' => '/student/events',
        ]);

        SystemNotification::query()->create([
            'user_id' => $student->id,
            'title' => 'Personal Attendance',
            'message' => 'Attendance confirmed.',
            'type' => 'attendance',
            'target_role' => 'student',
            'link' => '/student/events/1',
        ]);

        SystemNotification::query()->create([
            'user_id' => null,
            'title' => 'Admin Broadcast',
            'message' => 'Visible to admins only.',
            'type' => 'system',
            'target_role' => 'admin',
            'link' => '/admin/dashboard',
        ]);

        SystemNotification::query()->create([
            'user_id' => $admin->id,
            'title' => 'Admin Personal',
            'message' => 'Admin-only personal message.',
            'type' => 'system',
            'target_role' => 'admin',
            'link' => '/admin/dashboard',
        ]);

        $this->actingAs($student)
            ->getJson(route('notifications.index'))
            ->assertOk()
            ->assertJsonFragment(['title' => 'Student Broadcast'])
            ->assertJsonFragment(['title' => 'Personal Attendance'])
            ->assertJsonMissing(['title' => 'Admin Broadcast'])
            ->assertJsonMissing(['title' => 'Admin Personal']);
    }
}
