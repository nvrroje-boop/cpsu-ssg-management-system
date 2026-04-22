<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Concern;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\CreatesSsgData;
use Tests\TestCase;

class PortalPageSmokeTest extends TestCase
{
    use CreatesSsgData;
    use RefreshDatabase;

    public function test_welcome_page_renders_with_database_content(): void
    {
        [$announcement, $event] = $this->seedPortalFixtures();

        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee($announcement->title)
            ->assertSee($event->event_title);
    }

    public function test_admin_portal_pages_render(): void
    {
        [$announcement, $event, $concern, $student, $admin] = $this->seedPortalFixtures();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.profile'))->assertOk();
        $this->actingAs($admin)->get(route('admin.students.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.students.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.students.show', $student))->assertOk();
        $this->actingAs($admin)->get(route('admin.students.edit', $student))->assertOk();
        $this->actingAs($admin)->get(route('admin.announcements.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.announcements.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.announcements.show', $announcement))->assertOk();
        $this->actingAs($admin)->get(route('admin.announcements.edit', $announcement))->assertOk();
        $this->actingAs($admin)->get(route('admin.events.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.events.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.events.show', $event))->assertOk();
        $this->actingAs($admin)->get(route('admin.events.edit', $event))->assertOk();
        $this->actingAs($admin)->get(route('admin.concerns.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.concerns.show', $concern))->assertOk();
        $this->actingAs($admin)->get(route('admin.attendance.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.reports.index'))->assertOk();
    }

    public function test_officer_portal_pages_render(): void
    {
        [$announcement, $event, $concern, , , $officer] = $this->seedPortalFixtures();

        $this->actingAs($officer)->get(route('officer.dashboard'))->assertOk();
        $this->actingAs($officer)->get(route('officer.profile'))->assertOk();
        $this->actingAs($officer)->get(route('officer.announcements.index'))->assertOk();
        $this->actingAs($officer)->get(route('officer.announcements.create'))->assertOk();
        $this->actingAs($officer)->get(route('officer.announcements.show', $announcement))->assertOk();
        $this->actingAs($officer)->get(route('officer.announcements.edit', $announcement))->assertOk();
        $this->actingAs($officer)->get(route('officer.events.index'))->assertOk();
        $this->actingAs($officer)->get(route('officer.events.create'))->assertOk();
        $this->actingAs($officer)->get(route('officer.events.show', $event))->assertOk();
        $this->actingAs($officer)->get(route('officer.events.edit', $event))->assertOk();
        $this->actingAs($officer)->get(route('officer.concerns.index'))->assertOk();
        $this->actingAs($officer)->get(route('officer.concerns.show', $concern))->assertOk();
        $this->actingAs($officer)->get(route('officer.attendance.index'))->assertOk();
    }

    public function test_student_portal_pages_render(): void
    {
        [$announcement, $event, $concern, $student] = $this->seedPortalFixtures();

        $this->actingAs($student)->get(route('student.dashboard'))->assertOk();
        $this->actingAs($student)->get(route('student.profile'))->assertOk();
        $this->actingAs($student)->get(route('student.announcements.index'))->assertOk();
        $this->actingAs($student)->get(route('student.announcements.show', $announcement))->assertOk();
        $this->actingAs($student)->get(route('student.events.index'))->assertOk();
        $this->actingAs($student)->get(route('student.events.show', $event))->assertOk();
        $this->actingAs($student)->get(route('student.concerns.index'))->assertOk();
        $this->actingAs($student)->get(route('student.concerns.create'))->assertOk();
        $this->actingAs($student)->get(route('student.concerns.show', $concern))->assertOk();
    }

    public function test_admin_account_management_supports_officer_records_but_not_admin_records(): void
    {
        [, , , , $admin, $officer] = $this->seedPortalFixtures();

        $this->actingAs($admin)
            ->get(route('admin.students.show', $officer))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.students.show', $admin))
            ->assertNotFound();
    }

    public function test_admin_can_create_officer_accounts_from_the_account_form(): void
    {
        Mail::fake();

        $department = $this->createDepartment('BEED');
        $section = $this->createSection($department, 1, 'A');
        $admin = $this->createUserWithRole(User::ROLE_ADMIN, [
            'department_id' => $department->id,
        ]);
        $officerRole = Role::query()->firstOrCreate([
            'role_name' => User::ROLE_OFFICER,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.students.create'))
            ->assertOk()
            ->assertSee('Officer');

        $this->actingAs($admin)
            ->post(route('admin.students.store'), [
                'role_id' => $officerRole->id,
                'department_id' => $department->id,
                'year' => 1,
                'section_id' => $section->id,
                'name' => 'Officer Account',
                'email' => 'officer.account@example.com',
                'password' => 'SecurePass123!',
                'student_number' => '',
            ])
            ->assertRedirect(route('admin.students.index'));

        $officer = User::query()
            ->where('email', 'officer.account@example.com')
            ->firstOrFail();

        $this->assertSame($officerRole->id, $officer->role_id);

        $this->actingAs($admin)
            ->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee('Officer Account')
            ->assertSee('Officer');
    }

    /**
     * @return array{0: Announcement, 1: Event, 2: Concern, 3: User, 4: User, 5: User}
     */
    private function seedPortalFixtures(): array
    {
        $department = $this->createDepartment('BSIT');
        $section = $this->createSection($department, 1, 'A');

        $admin = $this->createUserWithRole(User::ROLE_ADMIN, [
            'department_id' => $department->id,
        ]);

        $officer = $this->createUserWithRole(User::ROLE_OFFICER, [
            'department_id' => $department->id,
        ]);

        $student = $this->createUserWithRole(User::ROLE_STUDENT, [
            'department_id' => $department->id,
            'section_id' => $section->id,
            'student_number' => '2026-03005-N',
        ]);

        $announcement = Announcement::query()->create([
            'title' => 'Portal Smoke Announcement',
            'message' => 'This published announcement exists to verify portal page rendering across the public and student surfaces.',
            'description' => 'Smoke announcement',
            'visibility' => 'public',
            'created_by_user_id' => $admin->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $event = Event::query()->create([
            'event_title' => 'Portal Smoke Event',
            'event_description' => 'This public event exists to verify event pages, attendance views, and student visibility.',
            'event_date' => now()->addDay()->toDateString(),
            'event_time' => '10:00',
            'location' => 'Main Hall',
            'visibility' => 'public',
            'attendance_required' => true,
            'created_by_user_id' => $admin->id,
        ]);

        $concern = Concern::query()->create([
            'title' => $announcement->title,
            'source_type' => Announcement::class,
            'source_id' => $announcement->id,
            'description' => 'Smoke concern body for page rendering coverage.',
            'submitted_by_user_id' => $student->id,
        ]);

        return [$announcement, $event, $concern, $student, $admin, $officer];
    }
}
