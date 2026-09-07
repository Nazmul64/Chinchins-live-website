@extends('layouts.admin')

@section('title', 'Roles & Permissions')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Roles & Permissions</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-shield-halved text-primary"></i>
                <span>Roles & Permission Management</span>
            </h1>
            <p class="page-subtitle">Define administrative roles, manage access boundaries, and assign module-level capabilities.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @hasPermission('staff.view')
            <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-users text-primary me-1"></i> Staff List
            </a>
            @endhasPermission
            @hasPermission('roles.create')
            <a href="{{ route('admin.roles.create') }}" class="btn-ch-primary">
                <i class="fa-solid fa-plus"></i> Create New Role
            </a>
            @endhasPermission
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert" style="border-radius: 12px;">
            <i class="fa-solid fa-circle-check fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert" style="border-radius: 12px;">
            <i class="fa-solid fa-triangle-exclamation fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2" role="alert" style="border-radius: 12px;">
            <i class="fa-solid fa-circle-info fs-5"></i>
            <div>{{ session('info') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        @foreach($roles as $role)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: var(--bg-card, #ffffff); position: relative; overflow: hidden;">
                    @if($role->slug === 'super-admin')
                        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #ef4444, #f59e0b);"></div>
                    @elseif($role->slug === 'sub-admin')
                        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: #3b82f6;"></div>
                    @elseif($role->slug === 'manager')
                        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: #10b981;"></div>
                    @else
                        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: #8b5cf6;"></div>
                    @endif

                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title fw-bold mb-1 d-flex align-items-center gap-2">
                                    {{ $role->name }}
                                    @if($role->is_system)
                                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 10.5px;">System Default</span>
                                    @endif
                                </h5>
                                <code class="text-muted" style="font-size: 12px;">{{ $role->slug }}</code>
                            </div>
                            <span class="badge" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; font-size: 12px; border-radius: 8px; padding: 6px 10px;">
                                <i class="fa-solid fa-users me-1"></i> {{ $role->users_count }} Staff
                            </span>
                        </div>

                        <p class="text-muted mb-4" style="font-size: 13px; min-height: 38px;">
                            {{ $role->description ?: 'No description provided.' }}
                        </p>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 12px;">
                                <span>Active Permissions</span>
                                <span class="fw-bold text-dark">
                                    @if($role->slug === 'super-admin')
                                        All (Full Access)
                                    @else
                                        {{ $role->permissions_count }} / 60+
                                    @endif
                                </span>
                            </div>
                            <div class="progress" style="height: 6px; border-radius: 4px;">
                                <div class="progress-bar {{ $role->slug === 'super-admin' ? 'bg-danger' : ($role->slug === 'sub-admin' ? 'bg-primary' : 'bg-success') }}" 
                                     role="progressbar" 
                                     style="width: {{ $role->slug === 'super-admin' ? '100%' : min(100, ($role->permissions_count / 60) * 100) }}%">
                                </div>
                            </div>
                        </div>

                        <div class="mt-auto d-flex gap-2 pt-3 border-top">
                            @if($role->slug !== 'super-admin')
                                @hasPermission('roles.edit')
                                <a href="{{ route('admin.roles.permissions', $role->id) }}" class="btn btn-sm btn-outline-primary flex-grow-1" style="border-radius: 8px;">
                                    <i class="fa-solid fa-key me-1"></i> Permissions
                                </a>
                                <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-light border" style="border-radius: 8px;" title="Edit Role Details">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                @endhasPermission
                            @else
                                <span class="btn btn-sm btn-light border text-muted flex-grow-1 disabled" style="border-radius: 8px;">
                                    <i class="fa-solid fa-lock me-1"></i> Permanent Super Admin
                                </span>
                            @endif

                            @if(!$role->is_system)
                                @hasPermission('roles.delete')
                                <form method="POST" action="{{ route('admin.roles.destroy', $role->id) }}" onsubmit="return confirm('Are you sure you want to delete this custom role?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border text-danger" style="border-radius: 8px;" title="Delete Role">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                @endhasPermission
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
