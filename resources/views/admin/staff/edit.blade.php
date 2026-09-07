@extends('layouts.admin')

@section('title', 'Edit Staff Member')

@section('content')
<div class="container-fluid px-0" style="max-width: 900px;">
    <!-- Breadcrumb & Header -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <a href="{{ route('admin.staff.index') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Staff</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Edit {{ $staff->name }}</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-user-pen text-primary"></i>
                <span>Edit Staff Member</span>
            </h1>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.staff.permissions', $staff->id) }}" class="btn btn-warning" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-key me-1"></i> Custom Permissions
            </a>
            <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to List
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
            <form method="POST" action="{{ route('admin.staff.update', $staff->id) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" style="font-size: 13.5px;">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $staff->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" style="font-size: 13.5px;">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $staff->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" style="font-size: 13.5px;">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $staff->phone) }}" class="form-control @error('phone') is-invalid @enderror">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" style="font-size: 13.5px;">Password (Leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" style="font-size: 13.5px;">Assign Role <span class="text-danger">*</span></label>
                        <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required {{ $staff->isSuperAdmin() && !auth()->user()->isSuperAdmin() ? 'disabled' : '' }}>
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}" {{ old('role_id', $staff->role_id) == $r->id ? 'selected' : '' }}>
                                    {{ $r->name }} ({{ $r->description ?? $r->slug }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" style="font-size: 13.5px;">Account Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required {{ $staff->isSuperAdmin() ? 'disabled' : '' }}>
                            <option value="active" {{ old('status', $staff->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $staff->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ old('status', $staff->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('admin.staff.index') }}" class="btn btn-light" style="border-radius: 8px;">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4" style="border-radius: 8px;">
                        <i class="fa-solid fa-check me-1"></i> Update Staff Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
