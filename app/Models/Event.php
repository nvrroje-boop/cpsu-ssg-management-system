<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'event_title',
        'event_description',
        'event_date',
        'event_time',
        'location',
        'visibility',
        'attendance_required',
        'attendance_token',
        'attendance_token_expires_at',
        'attendance_time_in_starts_at',
        'attendance_time_in_ends_at',
        'attendance_time_out_starts_at',
        'attendance_time_out_ends_at',
        'attendance_late_after',
        'attendance_active',
        'attendance_started_at',
        'attendance_stopped_at',
        'attendance_started_by_user_id',
        'event_reminder_sent_at',
        'attendance_open_notified_at',
        'attendance_closing_notified_at',
        'attendance_closed_notified_at',
        'department_id',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'attendance_required' => 'boolean',
            'attendance_token_expires_at' => 'datetime',
            'attendance_active' => 'boolean',
            'attendance_started_at' => 'datetime',
            'attendance_stopped_at' => 'datetime',
            'event_reminder_sent_at' => 'datetime',
            'attendance_open_notified_at' => 'datetime',
            'attendance_closing_notified_at' => 'datetime',
            'attendance_closed_notified_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function attendanceStarter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attendance_started_by_user_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(EventAttendance::class);
    }

    public function eventAttendances(): HasMany
    {
        return $this->attendances();
    }

    public function eventQrs(): HasMany
    {
        return $this->hasMany(EventQr::class);
    }

    public function scopeVisibleToUser(Builder $query, ?User $user): Builder
    {
        return $query->where(function (Builder $visibilityQuery) use ($user): void {
            $visibilityQuery->where('visibility', 'public');

            if ($user?->department_id !== null) {
                $visibilityQuery->orWhere(function (Builder $privateQuery) use ($user): void {
                    $privateQuery
                        ->where('visibility', 'private')
                        ->where('department_id', $user->department_id);
                });
            }
        });
    }
}
