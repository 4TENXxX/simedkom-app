<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const ROLES = ['super_admin', 'admin', 'admin_media', 'admin_keuangan', 'media', 'jurnalis'];

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function isAdminArea(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'admin_media', 'admin_keuangan'], true);
    }

    public function hasPermission(string $permission): bool
    {
        return $this->role === 'super_admin'
            || $this->rolePermissions()->whereHas('permission', fn ($query) => $query->where('name', $permission))->exists();
    }

    public function rolePermissions(): HasMany
    {
        return $this->hasMany(RolePermission::class, 'role', 'role');
    }

    public function mediaProfile(): HasOne
    {
        return $this->hasOne(MediaProfile::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function journalist(): HasOne
    {
        return $this->hasOne(Journalist::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
