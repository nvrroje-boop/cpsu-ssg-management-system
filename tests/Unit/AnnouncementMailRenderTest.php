<?php

namespace Tests\Unit;

use App\Mail\AnnouncementMail;
use Tests\TestCase;

class AnnouncementMailRenderTest extends TestCase
{
    public function test_it_renders_the_announcement_body_without_colliding_with_the_mail_message_object(): void
    {
        config()->set('app.url', 'https://portal.test');
        config()->set('services.ngrok.auto_detect', false);
        config()->set('mail.from.address', 'ssg@example.com');

        $html = (new AnnouncementMail(
            title: 'Campus Notice',
            announcementMessage: "Line one\nLine two",
            studentName: 'Juan Dela Cruz',
        ))->render();

        $this->assertStringContainsString('Campus Notice', $html);
        $this->assertStringContainsString('Line one', $html);
        $this->assertStringContainsString('Line two', $html);
        $this->assertStringContainsString('https://portal.test/student/announcements', $html);
    }
}
