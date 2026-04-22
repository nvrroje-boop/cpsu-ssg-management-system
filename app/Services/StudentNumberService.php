<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class StudentNumberService
{
    public function generate(?int $year = null): string
    {
        $year ??= now()->year;

        // Use transaction with locking to prevent race conditions
        return DB::transaction(function () use ($year) {
            $lastStudent = User::query()
                ->whereYear('created_at', $year)
                ->where('student_number', 'like', $year.'-%')
                ->orderByDesc('student_number')
                ->lockForUpdate() // Lock the rows to prevent concurrent generation
                ->first();

            $nextNumber = 3000;

            if ($lastStudent?->student_number !== null) {
                $nextNumber = ((int) substr($lastStudent->student_number, 5, 5)) + 1;
            }

            // Generate the candidate number
            $candidateNumber = sprintf('%s-%05d-N', $year, $nextNumber);

            // Ensure it doesn't already exist (failsafe check)
            $maxAttempts = 10;
            $attempt = 0;
            while ($attempt < $maxAttempts && User::query()->where('student_number', $candidateNumber)->exists()) {
                $nextNumber++;
                $candidateNumber = sprintf('%s-%05d-N', $year, $nextNumber);
                $attempt++;
            }

            return $candidateNumber;
        });
    }
}
