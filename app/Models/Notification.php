<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'announcement_id',
        'student_id',
        'status',
        'email',
        'error_message',
        'sent_at',
        'retry_count',
        'last_attempt_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'last_attempt_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Mark as sent
     */
    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'last_attempt_at' => now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
            'last_attempt_at' => now(),
        ]);
    }

    /**
     * Check if should retry
     */
    public function shouldRetry()
    {
        return $this->status === 'failed' && $this->retry_count < 3;
    }
}
