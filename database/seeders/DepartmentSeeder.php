<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        foreach ([
            'BSIT',
            'BEED',
            'BSAB',
        ] as $departmentName) {
            Department::query()->firstOrCreate([
                'department_name' => $departmentName,
            ]);
        }
    }
}
