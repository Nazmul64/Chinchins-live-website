<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleManagementController extends Controller
{
    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Display a listing of roles.
     */
    public function index()
    {
        $roles = Role::withCount(['users', 'permissions'])->orderBy('is_system', 'desc')->get();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $matrix = $this->permissionService->getPermissionMatrix();
        return view('admin.roles.create', compact('matrix'));
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $slug = Str::slug($request->name);

        // Ensure unique slug
        $baseSlug = $slug;
        $counter = 1;
        while (Role::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $role = Role::create([
            'name'        => $request->name,
            'slug'        => $slug,
            'description' => $request->description,
            'is_system'   => false,
            'is_active'   => true,
        ]);

        // Sync permissions
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        ActivityLog::record(
            'role_create',
            'roles',
            "Created new role '{$role->name}' ({$role->slug}) with " . count($request->permissions ?? []) . ' permissions',
            $role
        );

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$role->name}' created successfully.");
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        return view('admin.roles.edit', compact('role'));
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:100', Rule::unique('roles')->ignore($role->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $updates = [
            'name'        => $request->name,
            'description' => $request->description,
        ];

        // System roles cannot be deactivated
        if (!$role->is_system) {
            $updates['is_active'] = $request->boolean('is_active', true);
        }

        $role->update($updates);

        ActivityLog::record(
            'role_update',
            'roles',
            "Updated role '{$role->name}' details",
            $role
        );

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$role->name}' updated successfully.");
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return back()->with('error', 'Default system roles cannot be deleted.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete role while staff members are currently assigned to it. Reassign them first.');
        }

        $name = $role->name;
        $role->permissions()->detach();
        $role->delete();

        ActivityLog::record(
            'role_delete',
            'roles',
            "Deleted custom role '{$name}'"
        );

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$name}' has been deleted.");
    }

    /**
     * Show permission matrix for role.
     */
    public function editPermissions(Role $role)
    {
        $matrix = $this->permissionService->getPermissionMatrix();
        $assignedPermissionIds = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.permissions', compact('role', 'matrix', 'assignedPermissionIds'));
    }

    /**
     * Update assigned permissions for role.
     */
    public function updatePermissions(Request $request, Role $role)
    {
        if ($role->slug === 'super-admin') {
            return back()->with('info', 'Super Admin role automatically inherits all permissions across the platform.');
        }

        $permissionIds = $request->input('permissions', []);
        $role->permissions()->sync($permissionIds);

        ActivityLog::record(
            'role_permission_sync',
            'roles',
            "Updated permissions for role '{$role->name}' (" . count($permissionIds) . ' permissions assigned)',
            $role
        );

        return redirect()->route('admin.roles.index')
            ->with('success', "Permissions for '{$role->name}' updated successfully.");
    }
}
