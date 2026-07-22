<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Traits\HasTenant;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasTenant;

    const ROLE_FOUNDER    = 'founder';
    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_ADMIN      = 'admin';
    const ROLE_HR         = 'hr';
    const ROLE_USER       = 'user';

    private const ROLE_HIERARCHY = [
        self::ROLE_USER       => 0,
        self::ROLE_HR         => 1,
        self::ROLE_ADMIN      => 2,
        self::ROLE_SUPERADMIN => 3,
        self::ROLE_FOUNDER    => 4,
    ];

    protected $fillable = ['name', 'email', 'password', 'role', 'tenant_id', 'company_id', 'locale'];
    protected $hidden   = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function hasMinRole(string $minRole): bool
    {
        $userLevel = self::ROLE_HIERARCHY[$this->role] ?? -1;
        $minLevel  = self::ROLE_HIERARCHY[$minRole]   ?? 999;
        return $userLevel >= $minLevel;
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
