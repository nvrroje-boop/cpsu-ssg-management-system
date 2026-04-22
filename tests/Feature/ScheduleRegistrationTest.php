<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ScheduleRegistrationTest extends TestCase
{
    public function test_scheduled_announcement_command_is_registered(): void
    {
        Artisan::call('schedule:list');

        $this->assertStringContainsString('announcements:process', Artisan::output());
    }
}
