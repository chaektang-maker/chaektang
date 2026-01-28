<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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
        'source_id',
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
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * สำนักที่ user นี้เป็นเจ้าของ (ถ้ามี)
     */
    public function source(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function userVotes(): HasMany
    {
        return $this->hasMany(UserVote::class);
    }

    public function userFollows(): HasMany
    {
        return $this->hasMany(UserFollow::class);
    }

    public function isVip(): bool
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->exists();
    }

    /**
     * User นี้เป็นเจ้าของสำนักหรือไม่
     */
    public function isSourceOwner(): bool
    {
        return (bool) $this->source_id;
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is staff.
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    /**
     * Get the permissions for the user.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->withTimestamps();
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        // Admin มี permission ทั้งหมด
        if ($this->isAdmin()) {
            return true;
        }

        // Staff ต้องเช็คจาก permissions ที่กำหนด
        return $this->permissions()->where('slug', $permissionSlug)->exists();
    }

    /**
     * Check if user can access a route.
     */
    public function canAccessRoute(string $routeName): bool
    {
        // Admin เข้าถึงได้ทุก route
        if ($this->isAdmin()) {
            return true;
        }

        // Staff ต้องเช็คจาก permissions ที่มี route_name ตรงกัน
        // เช็คทั้ง exact match และ wildcard match (เช่น backoffice.sources.*)
        return $this->permissions()
            ->where(function ($query) use ($routeName) {
                // Exact match
                $query->where('route_name', $routeName)
                    // Wildcard match (เช่น backoffice.sources.* ตรงกับ backoffice.sources.index)
                    ->orWhere(function ($q) use ($routeName) {
                        $q->where('route_name', 'like', '%*')
                            ->whereRaw('? LIKE REPLACE(route_name, "*", "%")', [$routeName]);
                    });
            })
            ->exists();
    }
}