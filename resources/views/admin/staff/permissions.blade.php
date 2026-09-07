@extends('layouts.admin')

@section('title', 'Staff Permissions Override - ' . $staff->name)

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Header -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <a href="{{ route('admin.staff.index') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Staff</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Permissions</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-sliders text-primary"></i>
                <span>Custom Permission Overrides: {{ $staff->name }}</span>
            </h1>
            <p class="page-subtitle">
                Base Role: <strong class="text-primary">{{ $staff->role ? $staff->role->name : 'None' }}</strong>. 
                Individual overrides supersede role-based permissions (Allow &gt; Role &gt; Deny).
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Staff List
            </a>
        </div>
    </div>

    @if($staff->isSuperAdmin())
        <div class="alert alert-info d-flex align-items-center gap-2" style="border-radius: 12px;">
            <i class="fa-solid fa-crown fs-5 text-warning"></i>
            <div>This staff member has the <strong>Super Admin</strong> role. Super Admins bypass all authorization checks and have permanent full access to every module and action.</div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.staff.permissions.update', $staff->id) }}">
        @csrf

        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
            <div class="card-header bg-transparent border-0 p-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="fw-bold fs-6">Permission Matrix (Module by Module)</span>
                <div class="d-flex gap-2 text-muted" style="font-size: 12.5px;">
                    <span><span class="badge bg-secondary-subtle text-secondary">Inherit</span> = Use Role Default</span>
                    <span><span class="badge bg-success-subtle text-success">Allow</span> = Explicit Grant</span>
                    <span><span class="badge bg-danger-subtle text-danger">Deny</span> = Explicit Block</span>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                        <thead style="background: rgba(0,0,0,0.02); font-weight: 600; text-transform: uppercase; font-size: 11.5px; letter-spacing: 0.5px;">
                            <tr>
                                <th class="ps-4" style="width: 25%;">Module Name</th>
                                <th style="width: 35%;">Permission Action</th>
                                <th style="width: 15%;">Role Default</th>
                                <th class="text-end pe-4" style="width: 25%;">Override Setting</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($matrix as $module => $permissions)
                                <tr style="background: rgba(59, 130, 246, 0.03);">
                                    <td colspan="4" class="ps-4 py-2 fw-bold text-primary">
                                        <i class="fa-solid fa-folder-open me-2"></i> {{ strtoupper(str_replace('_', ' ', $module)) }} MODULE ({{ count($permissions) }})
                                    </td>
                                </tr>
                                @foreach($permissions as $p)
                                    @php
                                        $isRoleGranted = in_array($p->slug, $rolePermissions);
                                        $overrideEffect = isset($overrides[$p->id]) ? $overrides[$p->id]->effect : 'inherit';
                                    @endphp
                                    <tr>
                                        <td class="ps-4 text-muted">{{ $p->module }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $p->name }}</div>
                                            <code class="text-muted" style="font-size: 11px;">{{ $p->slug }}</code>
                                        </td>
                                        <td>
                                            @if($isRoleGranted)
                                                <span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i> Granted</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary"><i class="fa-solid fa-xmark me-1"></i> Denied</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <input type="radio" class="btn-check" name="permissions[{{ $p->id }}]" id="perm_{{ $p->id }}_inherit" value="inherit" {{ $overrideEffect === 'inherit' ? 'checked' : '' }}>
                                                <label class="btn btn-outline-secondary" for="perm_{{ $p->id }}_inherit" style="font-size: 11.5px;">Inherit</label>

                                                <input type="radio" class="btn-check" name="permissions[{{ $p->id }}]" id="perm_{{ $p->id }}_allow" value="allow" {{ $overrideEffect === 'allow' ? 'checked' : '' }}>
                                                <label class="btn btn-outline-success" for="perm_{{ $p->id }}_allow" style="font-size: 11.5px;"><i class="fa-solid fa-check"></i> Allow</label>

                                                <input type="radio" class="btn-check" name="permissions[{{ $p->id }}]" id="perm_{{ $p->id }}_deny" value="deny" {{ $overrideEffect === 'deny' ? 'checked' : '' }}>
                                                <label class="btn btn-outline-danger" for="perm_{{ $p->id }}_deny" style="font-size: 11.5px;"><i class="fa-solid fa-ban"></i> Deny</label>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-transparent border-0 p-3 px-4 d-flex justify-content-end gap-2 border-top">
                <a href="{{ route('admin.staff.index') }}" class="btn btn-light" style="border-radius: 8px;">Cancel</a>
                <button type="submit" class="btn btn-primary px-4" style="border-radius: 8px;">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Overrides
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
