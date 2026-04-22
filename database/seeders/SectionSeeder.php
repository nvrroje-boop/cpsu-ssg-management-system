<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Section;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    use WithoutModelEvents;

    private const YEAR_SECTION_MAP = [
        1 => ['A', 'B', 'C', 'D'],
        2 => ['A', 'B', 'C'],
        3 => ['A'],
        4 => ['A', 'B', 'C', 'D'],
    ];

    public function run(): void
    {
        $departments = ['BSIT', 'BEED', 'BSAB'];

        foreach ($departments as $deptName) {
            $department = Department::query()
                ->where('department_name', $deptName)
                ->first();

            if ($department) {
                foreach (self::YEAR_SECTION_MAP as $year => $letters) {
                    foreach ($letters as $letter) {
                        $sectionName = $deptName.' '.$year.$letter;
                        Section::query()->updateOrCreate(
                            ['section_name' => $sectionName],
                            [
                                'department_id' => $department->id,
                                'year_level' => $year,
                            ],
                        );
                    }
                }
            }
        }
    }
}
