@extends('layouts.admin')

@section('title', 'Login History & Security Logs')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Security & Access</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-right-to-bracket text-primary"></i>
                <span>Login History & Security Logs</span>
            </h1>
            <p class="page-subtitle">Inspect successful logins, invalid credential attempts, security lockouts, and client IP addresses.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @hasPermission('activity_logs.view')
            <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-clock-rotate-left text-primary me-1"></i> Activity Audit Logs
            </a>
            @endhasPermission
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.login-history.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="Search identifier, IP, device...">
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <select name="user_id" class="form-select">
                        <option value="">-- All Staff --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <select name="status" class="form-select">
                        <option value="">-- All Statuses --</option>
                        <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Successful Only</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed Only</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-1">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" title="From Date">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" title="To Date">
                </div>
                <div class="col-12 col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100" style="border-radius: 8px;">Filter</button>
                    @if(request()->hasAny(['search', 'user_id', 'status', 'date_from', 'date_to']))
                        <a href="{{ route('admin.login-history.index') }}" class="btn btn-light" style="border-radius: 8px;"><i class="fa-solid fa-rotate-left"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Login Table -->
    <div class="card border-0 shadow-sm" style="border-radius: 14px; background: var(--bg-card, #ffffff); overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead style="background: rgba(0,0,0,0.02); font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4" style="width: 18%;">Timestamp</th>
                        <th style="width: 22%;">User / Identifier</th>
                        <th style="width: 15%;">Result</th>
                        <th style="width: 25%;">Failure Reason / Note</th>
                        <th class="text-end pe-4" style="width: 20%;">IP & Client Device</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($histories as $h)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold text-dark">{{ $h->created_at->diffForHumans() }}</div>
                                <small class="text-muted" style="font-size: 11px;">{{ $h->created_at->format('d M Y, h:i:s A') }}</small>
                            </td>
                            <td>
                                @if($h->user)
                                    <div class="fw-bold">{{ $h->user->name }}</div>
                                    <small class="text-muted" style="font-size: 11.5px;">{{ $h->user->email }}</small>
                                @else
                                    <div class="fw-bold text-secondary">{{ $h->email_or_phone ?? 'Unknown' }}</div>
                                    <small class="text-muted" style="font-size: 11px;">Unmatched Identifier</small>
                                @endif
                            </td>
                            <td>
                                @if($h->is_successful)
                                    <span class="badge bg-success-subtle text-success px-2 py-1" style="border-radius: 6px;">
                                        <i class="fa-solid fa-circle-check me-1"></i> Success
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger px-2 py-1" style="border-radius: 6px;">
                                        <i class="fa-solid fa-circle-xmark me-1"></i> Failed
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted">{{ $h->failure_reason ?: '—' }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <div><code>{{ $h->ip_address ?? '127.0.0.1' }}</code></div>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 200px; font-size: 10.5px;" title="{{ $h->user_agent }}">
                                    {{ $h->device_type ? $h->device_type . ' • ' : '' }} {{ Str::limit($h->user_agent ?? 'Unknown Agent', 25) }}
                                </small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-shield-halved fs-1 mb-3 d-block text-secondary"></i>
                                No login history records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($histories->hasPages())
            <div class="card-footer bg-transparent border-0 p-3">
                {{ $histories->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
