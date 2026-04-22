<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $adminRole = Role::query()->firstOrCreate([
            'role_name' => User::ROLE_ADMIN,
        ]);

        $deptBSIT = Department::query()->where('department_name', 'BSIT')->first();

        if ($deptBSIT && $adminRole) {
            User::query()->firstOrCreate(
                ['email' => env('ADMIN_EMAIL', 'admin@ssg.local')],
                [
                    'role_id' => $adminRole->id,
                    'department_id' => $deptBSIT->id,
                    'section_id' => null,
                    'name' => 'System Administrator',
                    'student_number' => null,
                    'password' => Hash::make(env('ADMIN_PASSWORD', 'ChangeMe123!')),
                    'qr_token' => null,
                ]
            );
        }
    }
}
