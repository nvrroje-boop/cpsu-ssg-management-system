<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventAttendance extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (self $attendance): void {
            if (blank($attendance->user_id) && filled($attendance->student_id)) {
                $attendance->user_id = $attendance->student_id;
            }

            if (blank($attendance->student_id) && filled($attendance->user_id)) {
                $attendance->student_id = $attendance->user_id;
            }

            if ($attendance->time_in !== null && $attendance->scanned_at === null) {
                $attendance->scanned_at = $attendance->time_in;
            }

            if ($attendance->last_scanned_at === null) {
                $attendance->last_scanned_at = $attendance->time_out ?? $attendance->time_in ?? $attendance->scanned_at;
            }

            if (blank($attendance->recorded_by_user_id) && filled($attendance->scanned_by_user_id)) {
                $attendance->recorded_by_user_id = $attendance->scanned_by_user_id;
            }

            if (blank($attendance->scanned_by_user_id) && filled($attendance->recorded_by_user_id)) {
                $attendance->scanned_by_user_id = $attendance->recorded_by_user_id;
            }
        });
    }

    protected $fillable = [
        'event_id',
        'user_id',
        'student_id',
        'token',
        'time_in',
        'time_out',
        'status',
        'attendance_method',
        'recorded_by_user_id',
        'last_scanned_at',
        'scanned_by_user_id',
        'scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'time_in' => 'datetime',
            'time_out' => 'datetime',
            'last_scanned_at' => 'datetime',
            'scanned_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function scanner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
