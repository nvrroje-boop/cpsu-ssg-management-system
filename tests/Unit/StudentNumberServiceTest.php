<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\StudentNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentNumberServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_the_next_student_number_for_the_current_year(): void
    {
        User::factory()->create([
            'student_number' => '2026-03000-N',
            'created_at' => '2026-01-10 08:00:00',
            'updated_at' => '2026-01-10 08:00:00',
        ]);

        User::factory()->create([
            'student_number' => '2026-03001-N',
            'created_at' => '2026-02-10 08:00:00',
            'updated_at' => '2026-02-10 08:00:00',
        ]);

        $service = new StudentNumberService();

        $this->assertSame('2026-03002-N', $service->generate(2026));
    }
}
