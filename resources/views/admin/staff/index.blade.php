@extends('layouts.admin')

@section('title', 'Staff & Administrators')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Staff & Admin Management</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-user-shield text-primary"></i>
                <span>Staff & Administrative Users</span>
            </h1>
            <p class="page-subtitle">Manage administrative team members, assign predefined or custom roles, and fine-tune individual permissions.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @hasPermission('roles.view')
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-shield-halved text-warning me-1"></i> Manage Roles
            </a>
            @endhasPermission
            @hasPermission('staff.create')
            <a href="{{ route('admin.staff.create') }}" class="btn-ch-primary">
                <i class="fa-solid fa-user-plus"></i> Add New Staff
            </a>
            @endhasPermission
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert" style="border-radius: 12px; font-size: 14px;">
            <i class="fa-solid fa-circle-check fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert" style="border-radius: 12px; font-size: 14px;">
            <i class="fa-solid fa-triangle-exclamation fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filters Bar -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.staff.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="Search staff by name, email, phone...">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <select name="role_id" class="form-select">
                        <option value="">-- All Roles --</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}" {{ request('role_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <select name="status" class="form-select">
                        <option value="">-- All Statuses --</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100" style="border-radius: 8px;">Filter</button>
                    @if(request()->hasAny(['search', 'role_id', 'status']))
                        <a href="{{ route('admin.staff.index') }}" class="btn btn-light" style="border-radius: 8px;" title="Clear Filters"><i class="fa-solid fa-rotate-left"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Staff Table -->
    <div class="card border-0 shadow-sm" style="border-radius: 14px; background: var(--bg-card, #ffffff); overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                <thead style="background: rgba(0,0,0,0.02); font-weight: 600; text-transform: uppercase; font-size: 11.5px; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4">Staff Member</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Failed Attempts</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staffMembers as $staff)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background: #3b82f6; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                        @if($staff->avatar_url)
                                            <img src="{{ $staff->avatar_url }}" alt="" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                                        @else
                                            {{ strtoupper(substr($staff->name ?? 'A', 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $staff->name }}</div>
                                        <div class="text-muted" style="font-size: 12px;">{{ $staff->email }} {{ $staff->phone ? '• ' . $staff->phone : '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($staff->role)
                                    <span class="badge" style="background: {{ $staff->role->slug === 'super-admin' ? 'rgba(239, 68, 68, 0.12)' : ($staff->role->slug === 'sub-admin' ? 'rgba(59, 130, 246, 0.12)' : 'rgba(16, 185, 129, 0.12)') }}; color: {{ $staff->role->slug === 'super-admin' ? '#ef4444' : ($staff->role->slug === 'sub-admin' ? '#3b82f6' : '#10b981') }}; font-weight: 600; font-size: 12px; padding: 6px 12px; border-radius: 8px;">
                                        <i class="fa-solid fa-shield-halved me-1"></i> {{ $staff->role->name }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-secondary">No Role</span>
                                @endif

                                @if($staff->userPermissions->count() > 0)
                                    <span class="badge bg-warning-subtle text-warning border border-warning" style="font-size: 10.5px; border-radius: 6px;" title="{{ $staff->userPermissions->count() }} Custom Permission Overrides">
                                        {{ $staff->userPermissions->count() }} Overrides
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($staff->status === 'active')
                                    <span class="badge bg-success-subtle text-success px-2 py-1" style="border-radius: 6px;"><i class="fa-solid fa-circle-check me-1"></i> Active</span>
                                @elseif($staff->status === 'inactive')
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1" style="border-radius: 6px;"><i class="fa-solid fa-circle-pause me-1"></i> Inactive</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger px-2 py-1" style="border-radius: 6px;"><i class="fa-solid fa-ban me-1"></i> Suspended</span>
                                @endif
                            </td>
                            <td>
                                @if($staff->last_login_at)
                                    <div class="text-dark">{{ \Carbon\Carbon::parse($staff->last_login_at)->diffForHumans() }}</div>
                                    <small class="text-muted" style="font-size: 11px;">{{ \Carbon\Carbon::parse($staff->last_login_at)->format('d M Y, h:i A') }}</small>
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td>
                                @if(($staff->failed_login_attempts ?? 0) > 0)
                                    <span class="badge bg-danger-subtle text-danger">{{ $staff->failed_login_attempts }} Attempts</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 8px;">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius: 10px; font-size: 13px;">
                                        @hasPermission('staff.edit')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.staff.edit', $staff->id) }}">
                                                <i class="fa-solid fa-pen-to-square text-primary me-2"></i> Edit Profile & Role
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.staff.permissions', $staff->id) }}">
                                                <i class="fa-solid fa-key text-warning me-2"></i> Custom Permissions
                                            </a>
                                        </li>
                                        @endhasPermission

                                        @hasPermission('staff.status_toggle')
                                        @if(!$staff->isSuperAdmin())
                                            <li><hr class="dropdown-divider"></li>
                                            @if($staff->status !== 'active')
                                                <li>
                                                    <form method="POST" action="{{ route('admin.staff.status', $staff->id) }}">
                                                        @csrf
                                                        <input type="hidden" name="status" value="active">
                                                        <button type="submit" class="dropdown-item text-success"><i class="fa-solid fa-check me-2"></i> Activate</button>
                                                    </form>
                                                </li>
                                            @endif
                                            @if($staff->status !== 'inactive')
                                                <li>
                                                    <form method="POST" action="{{ route('admin.staff.status', $staff->id) }}">
                                                        @csrf
                                                        <input type="hidden" name="status" value="inactive">
                                                        <button type="submit" class="dropdown-item text-secondary"><i class="fa-solid fa-pause me-2"></i> Deactivate</button>
                                                    </form>
                                                </li>
                                            @endif
                                            @if($staff->status !== 'suspended')
                                                <li>
                                                    <form method="POST" action="{{ route('admin.staff.status', $staff->id) }}">
                                                        @csrf
                                                        <input type="hidden" name="status" value="suspended">
                                                        <button type="submit" class="dropdown-item text-warning"><i class="fa-solid fa-ban me-2"></i> Suspend</button>
                                                    </form>
                                                </li>
                                            @endif
                                        @endif
                                        @endhasPermission

                                        @hasPermission('staff.delete')
                                        @if(!$staff->isSuperAdmin() && $staff->id !== auth()->id())
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="{{ route('admin.staff.destroy', $staff->id) }}" onsubmit="return confirm('Are you sure you want to delete this staff member? This action cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger"><i class="fa-solid fa-trash me-2"></i> Delete Staff</button>
                                                </form>
                                            </li>
                                        @endif
                                        @endhasPermission
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-user-xmark fs-1 mb-3 d-block text-secondary"></i>
                                No staff members found matching your search.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($staffMembers->hasPages())
            <div class="card-footer bg-transparent border-0 p-3">
                {{ $staffMembers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
