<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLE_CUSTOMER = 'customer';

    public const ROLE_UMKM = 'umkm';

    public const ROLE_ADMIN = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'avatar',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'is_active' => 'boolean',
        ];
    }

    /* ---------------------------------------------------------------------
     |  Query Scopes
     --------------------------------------------------------------------- */

    /**
     * Batasi query pada satu atau beberapa role.
     */
    public function scopeRole(Builder $query, string ...$roles): Builder
    {
        return $query->whereIn('role', $roles);
    }

    /**
     * Hanya akun yang tidak diblokir.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->role(self::ROLE_ADMIN);
    }

    public function scopeUmkmOwners(Builder $query): Builder
    {
        return $query->role(self::ROLE_UMKM);
    }

    /* ---------------------------------------------------------------------
     |  Role Helpers — dipakai RoleMiddleware, Policy, dan Blade
     --------------------------------------------------------------------- */

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isUmkm(): bool
    {
        return $this->role === self::ROLE_UMKM;
    }

    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Label role untuk badge pada UI.
     */
    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_UMKM => 'Mitra UMKM',
            default => 'Pembeli',
        };
    }

    /**
     * Kelas Tailwind untuk badge role (konsisten di seluruh panel).
     */
    public function roleBadgeClass(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'bg-brand-primary text-white',
            self::ROLE_UMKM => 'bg-brand-accent text-white',
            default => 'bg-slate-200 text-slate-700',
        };
    }

    /* ---------------------------------------------------------------------
     |  Relasi domain (umkmProfile, orders, articles) ditambahkan pada FASE 3
     |  bersamaan dengan pembuatan model-model terkait.
     --------------------------------------------------------------------- */
}
