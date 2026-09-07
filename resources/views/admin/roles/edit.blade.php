@extends('layouts.admin')

@section('title', 'Edit Role - ' . $role->name)

@section('content')
<div class="container-fluid px-0" style="max-width: 800px;">
    <!-- Breadcrumb & Header -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <a href="{{ route('admin.roles.index') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Roles</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Edit {{ $role->name }}</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-shield-pen text-primary"></i>
                <span>Edit Role</span>
            </h1>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.roles.permissions', $role->id) }}" class="btn btn-warning" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-key me-1"></i> Edit Permissions
            </a>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Roles
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px;">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.roles.update', $role->id) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size: 13.5px;">Role Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $role->name) }}" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size: 13.5px;">Role Slug (System Key)</label>
                        <input type="text" value="{{ $role->slug }}" class="form-control" disabled>
                        <small class="text-muted">Role slugs cannot be changed once created.</small>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size: 13.5px;">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $role->description) }}</textarea>
                    </div>

                    @if(!$role->is_system)
                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ $role->is_active ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">Role is Active</label>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-light" style="border-radius: 8px;">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4" style="border-radius: 8px;">
                        <i class="fa-solid fa-check me-1"></i> Update Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
