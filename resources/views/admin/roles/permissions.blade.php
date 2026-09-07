@extends('layouts.admin')

@section('title', 'Manage Permissions - ' . $role->name)

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Header -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <a href="{{ route('admin.roles.index') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Roles</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">{{ $role->name }} Permissions</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-key text-primary"></i>
                <span>Permission Matrix: {{ $role->name }}</span>
            </h1>
            <p class="page-subtitle">Configure the specific capabilities granted to users holding the <strong class="text-primary">{{ $role->name }}</strong> role.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Roles
            </a>
        </div>
    </div>

    @if($role->slug === 'super-admin')
        <div class="alert alert-info d-flex align-items-center gap-2 mb-4" style="border-radius: 12px;">
            <i class="fa-solid fa-crown fs-5 text-warning"></i>
            <div>The <strong>Super Admin</strong> role has global unrestricted access to all modules, actions, and settings across the entire platform.</div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.roles.permissions.update', $role->id) }}">
        @csrf

        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
            <div class="card-header bg-transparent border-0 p-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="fw-bold fs-6">Module Permissions</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="selectAllBtn" onclick="toggleSelectAll()">Select / Deselect All</button>
            </div>

            <div class="card-body p-4 pt-0">
                <div class="row g-4">
                    @foreach($matrix as $module => $permissions)
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="p-3 border rounded-3 h-100" style="background: rgba(0,0,0,0.01);">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <span class="fw-bold text-primary" style="font-size: 13.5px;">
                                        <i class="fa-solid fa-folder me-1"></i> {{ strtoupper(str_replace('_', ' ', $module)) }}
                                    </span>
                                    <button type="button" class="btn btn-link p-0 text-decoration-none text-muted" style="font-size: 11px;" onclick="toggleModuleCheckboxes('{{ $module }}')">Toggle All</button>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    @foreach($permissions as $p)
                                        <div class="form-check">
                                            <input class="form-check-input module-check-{{ $module }}" 
                                                   type="checkbox" 
                                                   name="permissions[]" 
                                                   value="{{ $p->id }}" 
                                                   id="perm_check_{{ $p->id }}"
                                                   {{ in_array($p->id, $assignedPermissionIds) ? 'checked' : '' }}
                                                   {{ $role->slug === 'super-admin' ? 'disabled' : '' }}>
                                            <label class="form-check-label" for="perm_check_{{ $p->id }}" style="font-size: 13px; cursor: pointer;">
                                                <div class="fw-semibold">{{ $p->name }}</div>
                                                <code class="text-muted" style="font-size: 10.5px;">{{ $p->slug }}</code>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if($role->slug !== 'super-admin')
                <div class="card-footer bg-transparent border-0 p-3 px-4 d-flex justify-content-end gap-2 border-top">
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-light" style="border-radius: 8px;">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4" style="border-radius: 8px;">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Permissions
                    </button>
                </div>
            @endif
        </div>
    </form>
</div>

<script>
    let allSelected = false;
    function toggleSelectAll() {
        allSelected = !allSelected;
        document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = allSelected);
    }

    function toggleModuleCheckboxes(module) {
        const checkboxes = document.querySelectorAll('.module-check-' + module);
        const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
        checkboxes.forEach(cb => cb.checked = anyUnchecked);
    }
</script>
@endsection
