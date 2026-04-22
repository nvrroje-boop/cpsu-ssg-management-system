<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesSsgData;
use Tests\TestCase;

class AnnouncementVisibilityTest extends TestCase
{
    use CreatesSsgData;
    use RefreshDatabase;

    public function test_student_only_sees_published_announcements_for_their_audience(): void
    {
        $bsit = $this->createDepartment('BSIT');
        $beed = $this->createDepartment('BEED');
        $section = $this->createSection($bsit, 1, 'A');
        $creator = $this->createUserWithRole(User::ROLE_ADMIN);
        $student = $this->createUserWithRole(User::ROLE_STUDENT, [
            'department_id' => $bsit->id,
            'section_id' => $section->id,
            'student_number' => '2026-03000-N',
        ]);

        Announcement::query()->create([
            'title' => 'Visible Public Announcement',
            'message' => 'This announcement is published and public for the entire campus.',
            'description' => 'Published public announcement',
            'visibility' => 'public',
            'created_by_user_id' => $creator->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        Announcement::query()->create([
            'title' => 'Hidden Draft Announcement',
            'message' => 'This announcement is still a draft and must stay hidden from students.',
            'description' => 'Draft announcement',
            'visibility' => 'public',
            'created_by_user_id' => $creator->id,
            'status' => 'draft',
        ]);

        Announcement::query()->create([
            'title' => 'Visible Private Announcement',
            'message' => 'This announcement targets the BSIT department and is already published.',
            'description' => 'Published private announcement',
            'visibility' => 'private',
            'department_id' => $bsit->id,
            'created_by_user_id' => $creator->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        Announcement::query()->create([
            'title' => 'Hidden Other Department Announcement',
            'message' => 'This announcement targets another department and must stay hidden.',
            'description' => 'Other department announcement',
            'visibility' => 'private',
            'department_id' => $beed->id,
            'created_by_user_id' => $creator->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('student.announcements.index'))
            ->assertOk()
            ->assertSee('Visible Public Announcement')
            ->assertSee('Visible Private Announcement')
            ->assertDontSee('Hidden Draft Announcement')
            ->assertDontSee('Hidden Other Department Announcement');
    }
}
