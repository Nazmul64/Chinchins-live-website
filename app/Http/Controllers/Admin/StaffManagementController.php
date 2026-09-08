<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffManagementController extends Controller
{
    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Display a listing of administrative staff members.
     */
    public function index(Request $request)
    {
        $query = User::with('role')->whereNotNull('role_id');

        // Search
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('account_id', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $staffMembers = $query->latest()->paginate(15)->withQueryString();
        $roles = Role::active()->get();

        return view('admin.staff.index', compact('staffMembers', 'roles'));
    }

    /**
     * Show the form for creating a new staff member.
     */
    public function create()
    {
        $roles = Role::active()->get();
        return view('admin.staff.create', compact('roles'));
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:30', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6'],
            'role_id'  => ['required', 'exists:roles,id'],
            'status'   => ['required', 'in:active,inactive,suspended'],
        ]);

        $role = Role::findOrFail($request->role_id);

        $user = User::create([
            'name'                  => $request->name,
            'email'                 => $request->email,
            'phone'                 => $request->phone,
            'password'              => Hash::make($request->password),
            'role_id'               => $role->id,
            'status'                => $request->status,
            'is_active'             => $request->status === 'active',
            'is_verified'           => true,
            'failed_login_attempts' => 0,
        ]);

        ActivityLog::record(
            'staff_create',
            'staff',
            "Created staff member {$user->name} ({$user->email}) with role {$role->name}",
            $user,
            ['role_id' => $role->id, 'status' => $request->status]
        );

        return redirect()->route('admin.staff.index')
            ->with('success', "Staff member '{$user->name}' created successfully.");
    }

    /**
     * Show the form for editing the specified staff member.
     */
    public function edit(User $staff)
    {
        $roles = Role::active()->get();
        return view('admin.staff.edit', compact('staff', 'roles'));
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, User $staff)
    {
        $currentUser = Auth::user();

        // Prevent non-super admins from modifying Super Admin users
        if ($staff->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
            abort(403, 'Only Super Admin can edit Super Admin accounts.');
        }

        $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'string', 'email', 'max:150', Rule::unique('users')->ignore($staff->id)],
            'phone'    => ['nullable', 'string', 'max:30', Rule::unique('users')->ignore($staff->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role_id'  => ['required', 'exists:roles,id'],
            'status'   => ['required', 'in:active,inactive,suspended'],
        ]);

        $role = Role::findOrFail($request->role_id);

        $updates = [
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'role_id'   => $role->id,
            'status'    => $request->status,
            'is_active' => $request->status === 'active',
        ];

        if ($request->filled('password')) {
            $updates['password'] = Hash::make($request->password);
        }

        $staff->update($updates);

        ActivityLog::record(
            'staff_update',
            'staff',
            "Updated staff member {$staff->name} ({$staff->email})",
            $staff,
            ['role_id' => $role->id, 'status' => $request->status]
        );

        return redirect()->route('admin.staff.index')
            ->with('success', "Staff member '{$staff->name}' updated successfully.");
    }

    /**
     * Remove the specified staff member.
     */
    public function destroy(User $staff)
    {
        if ($staff->isSuperAdmin()) {
            return back()->with('error', 'Super Admin accounts cannot be deleted.');
        }

        if ($staff->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $staff->name;
        $email = $staff->email;

        // Clean up individual user permissions
        $staff->userPermissions()->delete();
        $staff->delete();

        ActivityLog::record(
            'staff_delete',
            'staff',
            "Deleted staff member {$name} ({$email})"
        );

        return redirect()->route('admin.staff.index')
            ->with('success', "Staff member '{$name}' has been deleted.");
    }

    /**
     * Quick status toggle.
     */
    public function updateStatus(Request $request, User $staff)
    {
        $request->validate([
            'status' => ['required', 'in:active,inactive,suspended'],
        ]);

        if ($staff->isSuperAdmin()) {
            return back()->with('error', 'Super Admin status cannot be altered.');
        }

        $staff->update([
            'status'    => $request->status,
            'is_active' => $request->status === 'active',
        ]);

        ActivityLog::record(
            'staff_status_change',
            'staff',
            "Changed status of {$staff->name} to {$request->status}",
            $staff
        );

        return back()->with('success', "Status updated to '{$request->status}' for {$staff->name}.");
    }

    /**
     * Show the user-level permission override matrix.
     */
    public function editPermissions(User $staff)
    {
        $matrix = $this->permissionService->getPermissionMatrix();
        $effectivePermissions = $this->permissionService->getUserEffectivePermissions($staff);
        $rolePermissions = $staff->role ? $staff->role->permissions->pluck('slug')->toArray() : [];
        $overrides = $staff->userPermissions->keyBy('permission_id');

        return view('admin.staff.permissions', compact('staff', 'matrix', 'effectivePermissions', 'rolePermissions', 'overrides'));
    }

    /**
     * Save user-level permission overrides.
     */
    public function updatePermissions(Request $request, User $staff)
    {
        if ($staff->isSuperAdmin()) {
            return back()->with('error', 'Super Admin already has full unrestricted permissions.');
        }

        // Request contains array of permission_id => 'allow' | 'deny' | 'inherit'
        $permissionsInput = $request->input('permissions', []);

        // Delete existing user overrides
        UserPermission::where('user_id', $staff->id)->delete();

        $savedCount = 0;
        foreach ($permissionsInput as $permissionId => $effect) {
            if (in_array($effect, ['allow', 'deny'])) {
                UserPermission::create([
                    'user_id'       => $staff->id,
                    'permission_id' => (int) $permissionId,
                    'effect'        => $effect,
                ]);
                $savedCount++;
            }
        }

        ActivityLog::record(
            'user_permission_override',
            'permissions',
            "Updated custom permission overrides for {$staff->name} ({$savedCount} customized)",
            $staff
        );

        return redirect()->route('admin.staff.index')
            ->with('success', "Custom permission overrides saved successfully for {$staff->name}.");
    }
}
