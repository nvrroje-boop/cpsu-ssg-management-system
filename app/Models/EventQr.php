<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventQr extends Model
{
    use HasFactory;

    protected $table = 'event_qrs';

    protected $fillable = [
        'event_id',
        'user_id',
        'token',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Check if QR is still valid
     */
    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }

    /**
     * Mark QR as used
     */
    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    /**
     * Relationship to Event
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Relationship to User (Student)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Get valid QRs only
     */
    public function scopeValid(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->whereNull('used_at')
            ->where('expires_at', '>', now());
    }

    /**
     * Scope: Get expired QRs
     */
    public function scopeExpired(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope: Get used QRs
     */
    public function scopeUsed(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->whereNotNull('used_at');
    }
}
