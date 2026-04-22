<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'role_name',
    ];

    protected static function booted(): void
    {
        static::saving(function (Role $role): void {
            $canonicalName = $role->name ?: $role->role_name;

            $role->name = $canonicalName;
            $role->role_name = $canonicalName;
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getDisplayNameAttribute(): ?string
    {
        return $this->name ?: $this->role_name;
    }
}
