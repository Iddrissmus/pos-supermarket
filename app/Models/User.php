<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    //Role Constants
    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_BUSINESS_ADMIN = 'business_admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_CASHIER = 'cashier';

    const ROLES = [
        self::ROLE_SUPERADMIN,
        self::ROLE_BUSINESS_ADMIN,
        self::ROLE_MANAGER,
        self::ROLE_CASHIER,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'branch_id',
        'business_id',
        'branch_role_key',
    ];

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            // SuperAdmin doesn't need branch or business
            if ($user->role === self::ROLE_SUPERADMIN) {
                $user->branch_role_key = null;
                $user->branch_id = null;
                $user->business_id = null;
                return;
            }

            // Business Admin must have business_id (branch_id is optional)
            if ($user->role === self::ROLE_BUSINESS_ADMIN) {
                if (!$user->business_id) {
                    throw new \InvalidArgumentException('Business Administrators must be assigned to a business.');
                }
                // Business admin can optionally be assigned to a specific branch
                $user->branch_role_key = $user->branch_id 
                    ? "{$user->branch_id}:{$user->role}"
                    : null;
                return;
            }

            // Cashier must have branch_id
            if ($user->role === self::ROLE_CASHIER) {
                $user->branch_role_key = $user->branch_id
                    ? "{$user->branch_id}:{$user->role}"
                    : null;
                return;
            }

            // Manager handling
            if ($user->role === self::ROLE_MANAGER) {
                $user->branch_role_key = $user->branch_id
                    ? "{$user->branch_id}:{$user->role}"
                    : null;
                return;
            }

            $user->branch_role_key = null;
        });
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function managedBusiness()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'cashier_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Role checking methods
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    public function isBusinessAdmin(): bool 
    {
        return $this->role === self::ROLE_BUSINESS_ADMIN;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isCashier(): bool
    {
        return $this->role === self::ROLE_CASHIER;
    }

    // Permission checking methods
    public function canManageMultipleBusinesses(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canCreateBusiness(): bool
    {
        return $this->isSuperAdmin();
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function managesBranch(): bool
    {
        return $this->isManager() && $this->branch_id !== null;
    }

    public function cashiersBranch(): bool
    {
        return $this->isCashier() && $this->branch_id !== null;
    }

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
        ];
    }
}
