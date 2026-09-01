<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class PlatformAdmin extends Authenticatable
{
    use Notifiable;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'name', 'email', 'password', 'status', 'platform_role_id', 'email_verified_at', 'last_login_at', 'failed_login_count', 'failed_login_window_started_at', 'locked_until', 'manual_blocked_at', 'manual_blocked_by', 'blocked_reason', 'deactivated_at'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'email_verified_at' => 'datetime', 'last_login_at' => 'datetime', 'failed_login_window_started_at' => 'datetime', 'locked_until' => 'datetime', 'manual_blocked_at' => 'datetime', 'deactivated_at' => 'datetime'];
    }

    public function role()
    {
        return $this->belongsTo(PlatformRole::class, 'platform_role_id');
    }

    public function hasPermission(string $permission): bool
    {
        return $this->role()->whereHas('permissions', fn ($query) => $query->where('code', $permission))->exists();
    }

    public function isAvailableForLogin(): bool
    {
        return $this->status === 'ativo' && ! $this->manual_blocked_at && ! $this->deactivated_at && (! $this->locked_until || $this->locked_until->isPast());
    }
}
