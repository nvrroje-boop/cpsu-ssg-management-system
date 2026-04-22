<?php

namespace App\Support;

use App\Models\Section;
use Illuminate\Support\Collection;

class StudentSectionRules
{
    public const MAP = [
        1 => ['A', 'B', 'C', 'D'],
        2 => ['A', 'B', 'C'],
        3 => ['A'],
        4 => ['A', 'B', 'C', 'D'],
    ];

    /**
     * @return array<int, array<int, string>>
     */
    public static function map(): array
    {
        return self::MAP;
    }

    /**
     * @return array<int, string>
     */
    public static function lettersFor(?int $year): array
    {
        return self::MAP[$year] ?? [];
    }

    public static function isValidYear(?int $year): bool
    {
        return $year !== null && array_key_exists($year, self::MAP);
    }

    public static function isValidCombination(?int $year, ?string $letter): bool
    {
        if (! self::isValidYear($year) || $letter === null) {
            return false;
        }

        return in_array(strtoupper($letter), self::lettersFor($year), true);
    }

    /**
     * @return array{department_code: string, year: int, letter: string}|null
     */
    public static function parseSectionName(?string $sectionName): ?array
    {
        if (! is_string($sectionName) || $sectionName === '') {
            return null;
        }

        if (! preg_match('/^([A-Z]+)\s+([1-4])([A-D])$/', strtoupper(trim($sectionName)), $matches)) {
            return null;
        }

        return [
            'department_code' => $matches[1],
            'year' => (int) $matches[2],
            'letter' => $matches[3],
        ];
    }

    public static function sectionMatchesYear(?Section $section, ?int $year): bool
    {
        if ($section === null || ! self::isValidYear($year)) {
            return false;
        }

        if ($section->year_level !== null) {
            return (int) $section->year_level === $year;
        }

        $parsed = self::parseSectionName($section->section_name);

        return $parsed !== null
            && $parsed['year'] === $year
            && self::isValidCombination($year, $parsed['letter']);
    }

    /**
     * @param \Illuminate\Support\Collection<int, \App\Models\Section> $sections
     * @return \Illuminate\Support\Collection<int, \App\Models\Section>
     */
    public static function filterSections(Collection $sections, ?int $year = null, ?string $departmentCode = null): Collection
    {
        return $sections
            ->filter(function (Section $section) use ($year, $departmentCode): bool {
                $parsed = self::parseSectionName($section->section_name);

                if ($parsed === null) {
                    return false;
                }

                if ($year !== null && $parsed['year'] !== $year) {
                    return false;
                }

                if ($departmentCode !== null && strtoupper($departmentCode) !== $parsed['department_code']) {
                    return false;
                }

                return self::isValidCombination($parsed['year'], $parsed['letter']);
            })
            ->values();
    }
}
