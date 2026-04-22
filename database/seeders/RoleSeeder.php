<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        foreach (['Admin', 'Officer', 'Student', 'SSG Officer'] as $roleName) {
            Role::query()->firstOrCreate([
                'role_name' => $roleName,
            ]);
        }
    }
}
