<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Concern;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesSsgData;
use Tests\TestCase;

class ConcernSubmissionTest extends TestCase
{
    use CreatesSsgData;
    use RefreshDatabase;

    public function test_student_concern_dropdown_and_submission_use_visible_database_records(): void
    {
        $bsit = $this->createDepartment('BSIT');
        $beed = $this->createDepartment('BEED');
        $section = $this->createSection($bsit, 1, 'A');
        $creator = $this->createUserWithRole(User::ROLE_ADMIN);
        $student = $this->createUserWithRole(User::ROLE_STUDENT, [
            'department_id' => $bsit->id,
            'section_id' => $section->id,
            'student_number' => '2026-03001-N',
        ]);

        $announcement = Announcement::query()->create([
            'title' => 'Campus Wi-Fi Maintenance',
            'message' => 'The published Wi-Fi maintenance notice is visible to all students today.',
            'description' => 'Published campus notice',
            'visibility' => 'public',
            'created_by_user_id' => $creator->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        Announcement::query()->create([
            'title' => 'Draft Hidden Notice',
            'message' => 'This draft notice should not appear in the concern title dropdown.',
            'description' => 'Draft hidden notice',
            'visibility' => 'public',
            'created_by_user_id' => $creator->id,
            'status' => 'draft',
        ]);

        Event::query()->create([
            'event_title' => 'Student Assembly',
            'event_description' => 'A public student assembly for all enrolled students this week.',
            'event_date' => now()->addDay()->toDateString(),
            'event_time' => '09:00',
            'location' => 'Gymnasium',
            'visibility' => 'public',
            'attendance_required' => true,
            'created_by_user_id' => $creator->id,
        ]);

        Event::query()->create([
            'event_title' => 'BEED Closed Meeting',
            'event_description' => 'A department-only event that must stay hidden from BSIT students.',
            'event_date' => now()->addDays(2)->toDateString(),
            'event_time' => '10:00',
            'location' => 'Conference Room',
            'visibility' => 'private',
            'department_id' => $beed->id,
            'attendance_required' => true,
            'created_by_user_id' => $creator->id,
        ]);

        $this->actingAs($student)
            ->get(route('student.concerns.create'))
            ->assertOk()
            ->assertSee('Campus Wi-Fi Maintenance')
            ->assertSee('Student Assembly')
            ->assertDontSee('Draft Hidden Notice')
            ->assertDontSee('BEED Closed Meeting');

        $this->actingAs($student)
            ->post(route('student.concerns.store'), [
                'source_reference' => 'announcement:'.$announcement->id,
                'description' => 'I need clarification about the maintenance schedule for the campus network.',
            ])
            ->assertRedirect(route('student.concerns.index'));

        $this->assertDatabaseHas('concerns', [
            'title' => 'Campus Wi-Fi Maintenance',
            'submitted_by_user_id' => $student->id,
        ]);

        $this->assertSame(1, Concern::query()->count());
    }
}
