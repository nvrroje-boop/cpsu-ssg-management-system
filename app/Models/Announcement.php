<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'message',
        'visibility',
        'department_id',
        'created_by_user_id',
        'target_filters',
        'send_at',
        'sent_at',
        'status',
        'total_recipients',
        'sent_count',
        'failed_count',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
            'archived_at' => 'datetime',
            'send_at' => 'datetime',
            'sent_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'target_filters' => 'array',
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

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'announcement_id');
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

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'sent')
            ->whereNotNull('sent_at');
    }

    public function scopeNotArchived(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    /**
     * Get target students based on filters
     */
    public function getTargetStudents()
    {
        $filters = $this->target_filters ?? [];

        return User::query()
            ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('role_name', User::ROLE_STUDENT))
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->when($filters['department_id'] ?? null, fn (Builder $query, int $departmentId) => $query->where('department_id', $departmentId))
            ->when($filters['year'] ?? null, fn (Builder $query, int $year) => $query->whereHas('section', fn (Builder $sectionQuery) => $sectionQuery->where('year_level', $year)))
            ->when($filters['section_id'] ?? null, fn (Builder $query, int $sectionId) => $query->where('section_id', $sectionId));
    }

    /**
     * Get notification statistics
     */
    public function getStats()
    {
        return [
            'total' => $this->total_recipients,
            'sent' => $this->sent_count,
            'failed' => $this->failed_count,
            'queued' => max(0, $this->total_recipients - $this->sent_count - $this->failed_count),
            'sent_percentage' => $this->total_recipients > 0 ? round(($this->sent_count / $this->total_recipients) * 100, 2) : 0,
        ];
    }

    /**
     * Archive this announcement
     */
    public function archive(): bool
    {
        return $this->update(['archived_at' => now()]);
    }

    /**
     * Unarchive this announcement
     */
    public function unarchive(): bool
    {
        return $this->update(['archived_at' => null]);
    }

    /**
     * Check if announcement is archived
     */
    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }
}
