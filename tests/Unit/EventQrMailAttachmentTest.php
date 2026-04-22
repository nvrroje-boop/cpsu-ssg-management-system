<?php

namespace Tests\Unit;

use App\Mail\EventQrMail;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\CreatesSsgData;
use Tests\TestCase;

class EventQrMailAttachmentTest extends TestCase
{
    use CreatesSsgData;
    use RefreshDatabase;

    public function test_it_attaches_a_downloadable_qr_image(): void
    {
        $department = $this->createDepartment('BSIT');
        $section = $this->createSection($department, 1, 'A');
        $admin = $this->createUserWithRole(User::ROLE_ADMIN, [
            'department_id' => $department->id,
        ]);
        $student = $this->createUserWithRole(User::ROLE_STUDENT, [
            'department_id' => $department->id,
            'section_id' => $section->id,
        ]);

        $event = Event::query()->create([
            'event_title' => 'Semester Assembly',
            'event_description' => 'Campus-wide student assembly',
            'event_date' => now()->addDay()->toDateString(),
            'event_time' => '09:00',
            'location' => 'Main Gym',
            'visibility' => 'public',
            'attendance_required' => true,
            'created_by_user_id' => $admin->id,
        ]);

        $mail = new EventQrMail($event, $student);
        $attachments = $mail->attachments();

        $this->assertCount(1, $attachments);
        $this->assertContains($attachments[0]->mime, ['image/png', 'image/svg+xml']);
        $this->assertMatchesRegularExpression('/\.(png|svg)$/', (string) $attachments[0]->as);

        [$binary, $meta] = $attachments[0]->attachWith(
            fn () => throw new RuntimeException('QR attachment should use in-memory data.'),
            fn ($data, $attachment) => [$data(), ['as' => $attachment->as, 'mime' => $attachment->mime]],
        );

        $this->assertNotSame('', $binary);
        $this->assertContains($meta['mime'], ['image/png', 'image/svg+xml']);
    }
}
