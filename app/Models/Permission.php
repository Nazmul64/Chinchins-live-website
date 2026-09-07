<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'module',
        'name',
        'slug',
        'description',
    ];

    /**
     * Roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permission_id', 'role_id')
            ->withTimestamps();
    }

    /**
     * User custom overrides.
     */
    public function userPermissions(): HasMany
    {
        return $this->hasMany(UserPermission::class, 'permission_id');
    }

    /**
     * Get human-readable module label.
     */
    public function getModuleLabelAttribute(): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $this->module));
    }
}
