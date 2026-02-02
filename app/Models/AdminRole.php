<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AdminRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($role) {
            if (empty($role->slug)) {
                $role->slug = Str::slug($role->name);
            }
        });
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(AdminPermission::class, 'admin_role_permission', 'role_id', 'permission_id')
            ->withTimestamps();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'admin_role_id');
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->slug === 'super-admin') {
            return true;
        }

        return $this->permissions()->where('slug', $permissionSlug)->exists();
    }

    public function hasAnyPermission(array $permissionSlugs): bool
    {
        if ($this->slug === 'super-admin') {
            return true;
        }

        return $this->permissions()->whereIn('slug', $permissionSlugs)->exists();
    }

    public function hasAllPermissions(array $permissionSlugs): bool
    {
        if ($this->slug === 'super-admin') {
            return true;
        }

        $count = $this->permissions()->whereIn('slug', $permissionSlugs)->count();
        return $count === count($permissionSlugs);
    }

    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }

    public function grantPermission(int $permissionId): void
    {
        $this->permissions()->attach($permissionId);
    }

    public function revokePermission(int $permissionId): void
    {
        $this->permissions()->detach($permissionId);
    }

    public function getPermissionsByModule(): array
    {
        return $this->permissions()
            ->get()
            ->groupBy('module')
            ->toArray();
    }

    public function isSuperAdmin(): bool
    {
        return $this->slug === 'super-admin';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }
}
