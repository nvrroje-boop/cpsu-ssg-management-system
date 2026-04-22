<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesSsgData;
use Tests\TestCase;

class AttendanceQrAccessTest extends TestCase
{
    use CreatesSsgData;
    use RefreshDatabase;

    public function test_public_qr_routes_render_and_download_for_a_valid_token(): void
    {
        $department = $this->createDepartment('BSIT');
        $section = $this->createSection($department, 1, 'A');
        $admin = $this->createUserWithRole(User::ROLE_ADMIN, [
            'department_id' => $department->id,
        ]);
        $student = $this->createUserWithRole(User::ROLE_STUDENT, [
            'department_id' => $department->id,
            'section_id' => $section->id,
            'name' => 'QR Test Student',
        ]);

        $event = Event::query()->create([
            'event_title' => 'QR Access Event',
            'event_description' => 'Used to test public QR access routes.',
            'event_date' => now()->addDays(2)->toDateString(),
            'event_time' => '08:00',
            'location' => 'Campus Hall',
            'visibility' => 'public',
            'attendance_required' => true,
            'created_by_user_id' => $admin->id,
        ]);

        $eventQr = app(QrCodeService::class)->generateOrGetQr($event, $student, true);

        $this->get(route('attendance.qr.show', $eventQr->token))
            ->assertOk()
            ->assertSee('Download QR Image');

        $this->get(route('attendance.qr.image', $eventQr->token))
            ->assertOk()
            ->assertHeader('Content-Type');

        $this->get(route('attendance.qr.download', $eventQr->token))
            ->assertOk()
            ->assertHeader('Content-Disposition');
    }
}
