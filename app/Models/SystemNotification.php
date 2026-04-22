<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class SystemNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'target_role',
        'event_id',
        'link',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function scopeVisibleToUser(Builder $query, User $user): Builder
    {
        $roleKey = $user->notificationRoleKey();

        return $query->where(function (Builder $builder) use ($user, $roleKey): void {
            $builder->where('user_id', $user->id);

            $builder->orWhere(function (Builder $broadcast) use ($roleKey): void {
                $broadcast->whereNull('user_id');

                if ($roleKey !== null) {
                    $broadcast->where(function (Builder $roleQuery) use ($roleKey): void {
                        $roleQuery
                            ->whereNull('target_role')
                            ->orWhere('target_role', $roleKey);
                    });
                }
            });
        });
    }
}
