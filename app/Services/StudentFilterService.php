<?php

namespace App\Services;

use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StudentFilterService
{
    public function filterStudents(
        ?int $departmentId = null,
        ?int $year = null,
        ?int $sectionId = null
    ): Builder {
        return User::query()
            ->select(['users.id', 'users.name', 'users.email', 'users.department_id', 'users.section_id', 'users.student_number'])
            ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('role_name', User::ROLE_STUDENT))
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->when($departmentId, fn (Builder $query) => $query->where('department_id', $departmentId))
            ->when($year, function (Builder $query, int $selectedYear): void {
                $query->whereHas('section', function (Builder $sectionQuery) use ($selectedYear): void {
                    $sectionQuery->where('year_level', $selectedYear);
                });
            })
            ->when($sectionId, fn (Builder $query) => $query->where('section_id', $sectionId))
            ->with([
                'department:id,department_name',
                'section:id,section_name,department_id',
                'role:id,role_name',
            ]);
    }

    public function getFilteredStudentsPaginated(
        ?int $departmentId = null,
        ?int $year = null,
        ?int $sectionId = null,
        int $perPage = 50
    ): \Illuminate\Pagination\LengthAwarePaginator {
        return $this->filterStudents($departmentId, $year, $sectionId)
            ->orderBy('users.name')
            ->paginate($perPage);
    }

    public function getFilteredStudentsCount(
        ?int $departmentId = null,
        ?int $year = null,
        ?int $sectionId = null
    ): int {
        return $this->filterStudents($departmentId, $year, $sectionId)->count();
    }

    public function getPreviewStudents(
        ?int $departmentId = null,
        ?int $year = null,
        ?int $sectionId = null,
        int $limit = 10
    ): Collection {
        return $this->filterStudents($departmentId, $year, $sectionId)
            ->orderBy('users.name')
            ->limit($limit)
            ->get();
    }

    public function chunkFilteredStudents(
        callable $callback,
        ?int $departmentId = null,
        ?int $year = null,
        ?int $sectionId = null,
        int $chunkSize = 100
    ): void {
        $this->filterStudents($departmentId, $year, $sectionId)
            ->chunkById($chunkSize, $callback, 'users.id', 'id');
    }

    public function getFilteredEmails(
        ?int $departmentId = null,
        ?int $year = null,
        ?int $sectionId = null
    ): array {
        return $this->filterStudents($departmentId, $year, $sectionId)
            ->pluck('email')
            ->toArray();
    }

    public static function isValidYear(?int $year): bool
    {
        return $year === null || in_array($year, [1, 2, 3, 4], true);
    }

    public function getSections(?int $departmentId = null, ?int $year = null): Collection
    {
        return Section::query()
            ->when($departmentId, fn (Builder $query) => $query->where('department_id', $departmentId))
            ->when($year, fn (Builder $query) => $query->where('year_level', $year))
            ->orderBy('section_name')
            ->get(['id', 'section_name', 'department_id', 'year_level']);
    }
}
