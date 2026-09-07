@extends('layouts.admin')

@section('title', 'Activity Audit Logs')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Audit Trail</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-clock-rotate-left text-primary"></i>
                <span>Activity Audit Logs</span>
            </h1>
            <p class="page-subtitle">Track every administrative action, configuration change, and authorization event in real time.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @hasPermission('login_history.view')
            <a href="{{ route('admin.login-history.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-right-to-bracket text-warning me-1"></i> Login History
            </a>
            @endhasPermission
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="Search description, IP...">
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
                    <select name="module" class="form-select">
                        <option value="">-- All Modules --</option>
                        @foreach($modules as $m)
                            <option value="{{ $m }}" {{ request('module') === $m ? 'selected' : '' }}>{{ ucfirst($m) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-1">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" title="From Date">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" title="To Date">
                </div>
                <div class="col-12 col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100" style="border-radius: 8px;">Filter</button>
                    @if(request()->hasAny(['search', 'user_id', 'module', 'date_from', 'date_to']))
                        <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-light" style="border-radius: 8px;"><i class="fa-solid fa-rotate-left"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card border-0 shadow-sm" style="border-radius: 14px; background: var(--bg-card, #ffffff); overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead style="background: rgba(0,0,0,0.02); font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4" style="width: 18%;">Timestamp</th>
                        <th style="width: 20%;">Staff User</th>
                        <th style="width: 12%;">Module & Action</th>
                        <th style="width: 32%;">Description</th>
                        <th class="text-end pe-4" style="width: 18%;">IP & Device</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold text-dark">{{ $log->created_at->diffForHumans() }}</div>
                                <small class="text-muted" style="font-size: 11px;">{{ $log->created_at->format('d M Y, h:i:s A') }}</small>
                            </td>
                            <td>
                                @if($log->user)
                                    <div class="fw-bold">{{ $log->user->name }}</div>
                                    <small class="text-muted" style="font-size: 11.5px;">{{ $log->user->email }}</small>
                                @else
                                    <span class="text-muted">System / Guest</span>
                                @endif
                            </td>
                            <td>
                                <div><span class="badge bg-primary-subtle text-primary">{{ ucfirst($log->module ?? 'General') }}</span></div>
                                <code class="text-muted" style="font-size: 11px;">{{ $log->action }}</code>
                            </td>
                            <td>
                                <div class="text-dark">{{ $log->description }}</div>
                                @if(!empty($log->properties))
                                    <details class="mt-1" style="font-size: 11px; color: #6b7280; cursor: pointer;">
                                        <summary class="text-primary">View Payload</summary>
                                        <pre class="bg-light p-2 rounded mt-1 mb-0" style="font-size: 10.5px; max-height: 150px; overflow-y: auto;">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </details>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div><code>{{ $log->ip_address ?? '127.0.0.1' }}</code></div>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 180px; font-size: 10.5px;" title="{{ $log->user_agent }}">
                                    {{ Str::limit($log->user_agent ?? 'Unknown Agent', 30) }}
                                </small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-list-check fs-1 mb-3 d-block text-secondary"></i>
                                No activity audit records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="card-footer bg-transparent border-0 p-3">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
