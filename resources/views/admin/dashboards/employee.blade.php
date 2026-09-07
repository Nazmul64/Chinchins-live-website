@extends('layouts.admin')

@section('title', 'Employee Workspace')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-secondary-subtle text-secondary fw-bold" style="font-size: 12px; border-radius: 6px;">EMPLOYEE WORKSPACE</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-user-gear text-primary"></i>
                <span>Staff Portal & Task Dashboard</span>
            </h1>
            <p class="page-subtitle">Welcome back, {{ $user->name }}. You have role-restricted access tailored to your designated duties.</p>
        </div>
    </div>

    <!-- Quick Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted fw-semibold" style="font-size: 13px;">Assigned Role</span>
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-shield"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1">{{ $user->role ? $user->role->name : 'Employee' }}</h4>
                <span class="text-success" style="font-size: 12px;"><i class="fa-solid fa-circle-check"></i> Active Status</span>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted fw-semibold" style="font-size: 13px;">Pending Deposits</span>
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1" style="color: #f59e0b;">{{ number_format($pendingDeposits) }}</h4>
                <span class="text-muted" style="font-size: 12px;">Tasks in Queue</span>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted fw-semibold" style="font-size: 13px;">Pending Withdrawals</span>
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1" style="color: #ef4444;">{{ number_format($pendingWithdrawals) }}</h4>
                <span class="text-muted" style="font-size: 12px;">Tasks in Queue</span>
            </div>
        </div>
    </div>

    <!-- Quick Shortcuts & Recent Actions -->
    <div class="row g-4">
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
                <div class="card-header bg-transparent border-0 p-3 px-4">
                    <span class="fw-bold fs-6"><i class="fa-solid fa-bolt text-warning me-2"></i> Available Quick Actions</span>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="d-grid gap-2">
                        @hasPermission('users.view')
                        <a href="{{ route('admin.users.index') }}" class="btn btn-light text-start p-3 border d-flex align-items-center gap-3" style="border-radius: 10px;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Users Directory</div>
                                <small class="text-muted">Search users and inspect balances</small>
                            </div>
                        </a>
                        @endhasPermission

                        @hasPermission('deposits.view')
                        <a href="{{ route('admin.deposits.index') }}" class="btn btn-light text-start p-3 border d-flex align-items-center gap-3" style="border-radius: 10px;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-wallet"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Deposit Requests</div>
                                <small class="text-muted">Verify incoming transaction slips</small>
                            </div>
                        </a>
                        @endhasPermission

                        @hasPermission('calls.view')
                        <a href="{{ route('admin.calls.index') }}" class="btn btn-light text-start p-3 border d-flex align-items-center gap-3" style="border-radius: 10px;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Call History Logs</div>
                                <small class="text-muted">Monitor call sessions and duration</small>
                            </div>
                        </a>
                        @endhasPermission
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
                <div class="card-header bg-transparent border-0 p-3 px-4">
                    <span class="fw-bold fs-6"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Your Recent Activity</span>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex flex-column gap-3">
                        @forelse($myRecentActivities as $act)
                            <div class="p-2 rounded border" style="background: rgba(0,0,0,0.015); font-size: 12.5px;">
                                <div>{{ $act->description }}</div>
                                <div class="text-muted mt-1" style="font-size: 11px;">
                                    <span class="badge bg-light text-secondary me-1">{{ $act->action }}</span>
                                    {{ $act->created_at->diffForHumans() }}
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">No activity records logged in this session yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
