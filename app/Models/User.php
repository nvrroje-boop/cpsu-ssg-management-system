<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    public const ROLE_ADMIN = 'Admin';
    public const ROLE_OFFICER = 'Officer';
    public const ROLE_SSG_OFFICER = 'SSG Officer';
    public const ROLE_STUDENT = 'Student';

    protected $fillable = [
        'role_id',
        'department_id',
        'section_id',
        'name',
        'student_number',
        'email',
        'phone',
        'course',
        'password',
        'qr_token',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'deleted_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function createdAnnouncements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'created_by_user_id');
    }

    public function createdEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by_user_id');
    }

    public function eventAttendances(): HasMany
    {
        return $this->hasMany(EventAttendance::class, 'user_id');
    }

    public function attendances(): HasMany
    {
        return $this->eventAttendances();
    }

    public function eventQrs(): HasMany
    {
        return $this->hasMany(EventQr::class);
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    public function systemNotifications(): HasMany
    {
        return $this->hasMany(SystemNotification::class);
    }

    public function notificationRoleKey(): ?string
    {
        if ($this->isAdmin()) {
            return 'admin';
        }

        if ($this->isOfficer()) {
            return 'ssg';
        }

        if ($this->isStudentPortalUser()) {
            return 'student';
        }

        return null;
    }

    public function hasRole(string|array $roleNames): bool
    {
        $roleName = $this->role?->display_name ?? $this->role?->role_name;

        if ($roleName === null) {
            return false;
        }

        $normalizedRoleName = $this->normalizeRoleName($roleName);

        return collect((array) $roleNames)
            ->map(fn (string $candidate): string => $this->normalizeRoleName($candidate))
            ->contains($normalizedRoleName);
    }

    public function isAdminPortalUser(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function isOfficer(): bool
    {
        return $this->hasRole([self::ROLE_OFFICER, self::ROLE_SSG_OFFICER]);
    }

    public function isOfficerPortalUser(): bool
    {
        return $this->isOfficer();
    }

    public function isStudentPortalUser(): bool
    {
        return $this->hasRole(self::ROLE_STUDENT);
    }

    private function normalizeRoleName(string $roleName): string
    {
        return strtolower(str_replace(['_', '-'], ' ', trim($roleName)));
    }
}
