<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StudentAnnouncementVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_only_see_published_announcements(): void
    {
        $studentRole = Role::query()->create(['role_name' => User::ROLE_STUDENT]);
        $adminRole = Role::query()->create(['role_name' => User::ROLE_ADMIN]);

        $student = User::factory()->create(['role_id' => $studentRole->id]);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        Announcement::query()->create([
            'title' => 'Draft Announcement',
            'description' => 'Draft description',
            'message' => 'This draft should never appear in the student portal.',
            'visibility' => 'public',
            'created_by_user_id' => $admin->id,
            'status' => 'draft',
        ]);

        Announcement::query()->create([
            'title' => 'Published Announcement',
            'description' => 'Published description',
            'message' => 'This published message should be visible to students.',
            'visibility' => 'public',
            'created_by_user_id' => $admin->id,
            'status' => 'sent',
            'sent_at' => Carbon::parse('2026-04-04 08:00:00'),
        ]);

        $response = $this->actingAs($student)->get(route('student.announcements.index'));

        $response->assertOk();
        $response->assertSee('Published Announcement');
        $response->assertDontSee('Draft Announcement');
    }
}
