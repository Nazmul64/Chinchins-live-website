<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;

class PermissionService
{
    /**
     * Centralized 4-Level Permission Checker:
     * 1. Super Admin bypass (Always true)
     * 2. User Specific Override (Explicit allow -> true, explicit deny -> false)
     * 3. Role Permissions (Matches user's assigned role)
     * 4. Deny (Default false)
     */
    public static function hasPermission(?User $user, string $permissionSlug): bool
    {
        if (!$user) {
            return false;
        }

        // Check if user is active
        if ($user->status && $user->status !== 'active') {
            return false;
        }

        // 1. Super Admin Check
        if (static::isSuperAdmin($user)) {
            return true;
        }

        // 2. User Specific Custom Override Check
        if ($user->relationLoaded('userPermissions') || $user->userPermissions()->exists()) {
            $override = $user->userPermissions()
                ->whereHas('permission', fn($q) => $q->where('slug', $permissionSlug))
                ->first();

            if ($override) {
                return $override->type === 'allow';
            }
        }

        // 3. Role Permission Check
        $role = $user->role;
        if ($role) {
            if ($role->status !== 'active') {
                return false;
            }

            if ($role->slug === 'super-admin') {
                return true;
            }

            return $role->hasPermission($permissionSlug);
        }

        // 4. Deny Access
        return false;
    }

    /**
     * Check if user has a specific role or any role in array.
     */
    public static function hasRole(?User $user, string|array $roles): bool
    {
        if (!$user || !$user->role) {
            return false;
        }

        $roles = (array) $roles;

        return in_array($user->role->slug, $roles) || in_array($user->role->name, $roles);
    }

    /**
     * Check if user is Super Admin.
     */
    public static function isSuperAdmin(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->role && $user->role->slug === 'super-admin') {
            return true;
        }

        // Fallback for primary developer account with no role assigned yet
        return (bool) ($user->is_admin ?? false) && empty($user->role_id);
    }

    /**
     * Get all grouped permissions by module for UI display.
     */
    public static function getGroupedPermissions(): Collection
    {
        return Permission::all()->groupBy('module');
    }

    /**
     * Get all effective permissions for a user (including role + custom overrides).
     */
    public static function getUserEffectivePermissions(User $user): array
    {
        if (static::isSuperAdmin($user)) {
            return Permission::pluck('slug')->toArray();
        }

        $permissions = [];

        // Role permissions
        if ($user->role) {
            $permissions = $user->role->permissions()->pluck('slug')->toArray();
        }

        // Apply custom overrides
        $overrides = $user->userPermissions()->with('permission')->get();
        foreach ($overrides as $override) {
            $slug = $override->permission?->slug;
            if (!$slug) continue;

            if ($override->type === 'allow' && !in_array($slug, $permissions)) {
                $permissions[] = $slug;
            } elseif ($override->type === 'deny' && in_array($slug, $permissions)) {
                $permissions = array_values(array_diff($permissions, [$slug]));
            }
        }

        return $permissions;
    }

    /**
     * Get Role-Permission Matrix for admin overview table.
     */
    public static function getPermissionMatrix(): array
    {
        $roles = Role::with('permissions')->orderBy('id')->get();
        $groupedPermissions = static::getGroupedPermissions();

        $matrix = [];
        foreach ($groupedPermissions as $module => $permissions) {
            $matrix[$module] = [
                'module_label' => ucwords(str_replace(['_', '-'], ' ', $module)),
                'permissions'  => [],
            ];

            foreach ($permissions as $perm) {
                $roleAccess = [];
                foreach ($roles as $role) {
                    $roleAccess[$role->slug] = $role->slug === 'super-admin' || $role->permissions->contains('id', $perm->id);
                }

                $matrix[$module]['permissions'][] = [
                    'id'          => $perm->id,
                    'name'        => $perm->name,
                    'slug'        => $perm->slug,
                    'description' => $perm->description,
                    'role_access' => $roleAccess,
                ];
            }
        }

        return [
            'roles'  => $roles,
            'matrix' => $matrix,
        ];
    }
}
