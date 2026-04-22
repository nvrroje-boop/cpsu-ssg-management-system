<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Concern extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_RESOLVED = 'resolved';

    protected $fillable = [
        'title',
        'source_type',
        'source_id',
        'description',
        'reply_message',
        'status',
        'submitted_by_user_id',
        'assigned_to_user_id',
        'replied_by_user_id',
        'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'replied_at' => 'datetime',
        ];
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id')
            ->withTrashed()
            ->withDefault([
                'name' => 'Unknown submitter',
                'email' => null,
            ]);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function replier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by_user_id');
    }

    public function source(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'source_type', 'source_id');
    }

    public function workflowStatus(): string
    {
        return filled($this->reply_message) || $this->status === self::STATUS_RESOLVED
            ? 'replied'
            : 'pending';
    }
}
