<?php

namespace Tests\Feature;

use App\Mail\EventQrMail;
use App\Models\Event;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\CreatesSsgData;
use Tests\TestCase;

class EventQrResendTest extends TestCase
{
    use CreatesSsgData;
    use RefreshDatabase;

    public function test_admin_resend_qr_emails_sends_the_mail_immediately(): void
    {
        Mail::fake();

        $department = $this->createDepartment('BSIT');
        $section = $this->createSection($department, 1, 'A');
        $admin = $this->createUserWithRole(User::ROLE_ADMIN, [
            'department_id' => $department->id,
        ]);
        $student = $this->createUserWithRole(User::ROLE_STUDENT, [
            'department_id' => $department->id,
            'section_id' => $section->id,
            'email' => 'student.qr@example.com',
        ]);

        $event = Event::query()->create([
            'event_title' => 'Resend QR Event',
            'event_description' => 'Used to test QR resend delivery.',
            'event_date' => now()->addDay()->toDateString(),
            'event_time' => '09:00',
            'location' => 'Main Hall',
            'visibility' => 'public',
            'attendance_required' => true,
            'created_by_user_id' => $admin->id,
        ]);

        app(QrCodeService::class)->generateOrGetQr($event, $student, true);

        $this->actingAs($admin)
            ->post(route('admin.events.resend-qr-emails', $event))
            ->assertRedirect(route('admin.events.show', $event))
            ->assertSessionHas('success', 'QR email resend completed successfully.');

        Mail::assertSent(EventQrMail::class, function (EventQrMail $mail) use ($event, $student): bool {
            return $mail->event->is($event) && $mail->student->is($student);
        });
    }
}
