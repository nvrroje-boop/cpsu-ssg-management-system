<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Role;
use App\Models\Section;
use App\Models\User;
use App\Support\StudentSectionRules;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StudentService
{
    /**
     * @return array<int, string>
     */
    public static function manageableRoleNames(): array
    {
        return [
            User::ROLE_STUDENT,
            User::ROLE_OFFICER,
            User::ROLE_SSG_OFFICER,
        ];
    }

    public function getRoles(): array
    {
        $roleOrder = array_flip(self::manageableRoleNames());

        return Role::query()
            ->whereIn('role_name', self::manageableRoleNames())
            ->get(['id', 'role_name'])
            ->sortBy(fn (Role $role): int => $roleOrder[$role->role_name] ?? PHP_INT_MAX)
            ->map(function (Role $role): array {
                return [
                    'id' => $role->id,
                    'role_name' => $role->role_name,
                    'role_label' => $role->display_name ?? $role->role_name,
                ];
            })
            ->toArray();
    }

    public function getStudents(): array
    {
        return User::query()
            ->whereHas('role', fn ($roleQuery) => $roleQuery->whereIn('role_name', self::manageableRoleNames()))
            ->with(['role', 'department', 'section'])
            ->orderBy('name')
            ->get()
            ->map(fn (User $user): array => $this->mapStudent($user))
            ->all();
    }

    /**
     * Get students grouped by department
     */
    public function getStudentsGroupedByDepartment(?string $sortBy = 'name'): array
    {
        $students = $this->getStudents();

        // Group by department
        $grouped = collect($students)
            ->groupBy('department_name')
            ->sortKeys()
            ->toArray();

        // Sort students within each department
        foreach ($grouped as &$deptStudents) {
            usort($deptStudents, function ($a, $b) use ($sortBy) {
                return match ($sortBy) {
                    'student_number' => strcmp($a['student_number'], $b['student_number']),
                    'role' => strcmp($a['role_name'], $b['role_name']),
                    'section' => strcmp($a['section_name'], $b['section_name']),
                    'name' => strcmp($a['name'], $b['name']),
                    default => strcmp($a['name'], $b['name']),
                };
            });
        }

        return $grouped;
    }

    public function getDepartments(): array
    {
        return Department::query()
            ->orderBy('department_name')
            ->get(['id', 'department_name'])
            ->toArray();
    }

    public function getSections(): array
    {
        return Section::query()
            ->orderBy('department_id')
            ->orderBy('year_level')
            ->orderBy('section_name')
            ->get(['id', 'section_name', 'department_id', 'year_level'])
            ->map(function (Section $section): array {
                $parsed = StudentSectionRules::parseSectionName($section->section_name);

                return [
                    'id' => $section->id,
                    'section_name' => $section->section_name,
                    'department_id' => $section->department_id,
                    'year' => $section->year_level ?? ($parsed['year'] ?? null),
                    'letter' => $parsed['letter'] ?? null,
                    'department_code' => $parsed['department_code'] ?? null,
                ];
            })
            ->toArray();
    }

    public function findStudent(int $studentId): array
    {
        $student = User::query()
            ->whereHas('role', fn ($roleQuery) => $roleQuery->whereIn('role_name', self::manageableRoleNames()))
            ->with(['role', 'department', 'section'])
            ->find($studentId);

        if ($student === null) {
            throw new ModelNotFoundException();
        }

        return $this->mapStudent($student);
    }

    private function mapStudent(User $user): array
    {
        $parsedSection = StudentSectionRules::parseSectionName($user->section?->section_name);

        return [
            'id' => $user->id,
            'role_id' => $user->role_id,
            'role_name' => $user->role?->role_name ?? 'Unassigned',
            'department_id' => $user->department_id,
            'department_name' => $user->department?->department_name ?? '-',
            'section_id' => $user->section_id,
            'section_name' => $user->section?->section_name ?? '-',
            'year' => $parsedSection['year'] ?? null,
            'name' => $user->name,
            'student_number' => $user->student_number,
            'email' => $user->email,
            'phone' => $user->phone,
            'course' => $user->course,
        ];
    }
}
