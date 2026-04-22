<?php

namespace Tests;

use App\Models\Department;
use App\Models\Role;
use App\Models\Section;
use App\Models\User;

trait CreatesSsgData
{
    protected function createDepartment(string $name = 'BSIT'): Department
    {
        return Department::query()->firstOrCreate([
            'department_name' => $name,
        ]);
    }

    protected function createSection(Department $department, int $year = 1, string $letter = 'A'): Section
    {
        return Section::query()->firstOrCreate(
            [
                'section_name' => sprintf('%s %d%s', $department->department_name, $year, $letter),
            ],
            [
                'department_id' => $department->id,
                'year_level' => $year,
            ],
        );
    }

    protected function createUserWithRole(string $roleName, array $attributes = []): User
    {
        $role = Role::query()->firstOrCreate([
            'role_name' => $roleName,
        ]);

        return User::factory()->create(array_merge([
            'role_id' => $role->id,
            'password' => 'password',
        ], $attributes));
    }
}
