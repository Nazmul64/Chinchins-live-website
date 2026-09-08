<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Role users relationship.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    /**
     * Role permissions relationship.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id')
            ->withTimestamps();
    }

    /**
     * Check if role has a specific permission slug.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->slug === 'super-admin') {
            return true;
        }

        return $this->permissions->contains('slug', $permissionSlug);
    }

    /**
     * Sync permissions by array of IDs or slugs.
     */
    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }

    /**
     * Mutator to ensure slug is formatted properly.
     */
    public function setSlugAttribute($value): void
    {
        $this->attributes['slug'] = Str::slug($value ?: $this->name);
    }

    /**
     * Scope for active roles.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Accessor for is_system compatibility (maps to is_default).
     */
    public function getIsSystemAttribute(): bool
    {
        return (bool) ($this->attributes['is_default'] ?? false);
    }

    /**
     * Mutator for is_system compatibility (maps to is_default).
     */
    public function setIsSystemAttribute($value): void
    {
        $this->attributes['is_default'] = (bool) $value;
    }

    /**
     * Accessor for is_active compatibility (maps to status == 'active').
     */
    public function getIsActiveAttribute(): bool
    {
        return ($this->attributes['status'] ?? 'active') === 'active';
    }

    /**
     * Mutator for is_active compatibility (maps to status).
     */
    public function setIsActiveAttribute($value): void
    {
        $this->attributes['status'] = $value ? 'active' : 'inactive';
    }
}
