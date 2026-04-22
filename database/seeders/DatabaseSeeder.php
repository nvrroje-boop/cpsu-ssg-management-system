<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Department;
use App\Models\EventAttendance;
use App\Models\Event;
use App\Models\Role;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            DepartmentSeeder::class,
            SectionSeeder::class,
            AdminSeeder::class,
        ]);

        // Production system - no demo/test accounts created
        // Real accounts should be created through admin panel or registration

    }
}
