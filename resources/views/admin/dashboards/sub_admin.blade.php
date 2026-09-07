@extends('layouts.admin')

@section('title', 'Sub Admin Dashboard')

@section('content')
<div class="container-fluid px-0">
    <!-- Welcome Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary fw-bold" style="font-size: 12px; border-radius: 6px;">SUB ADMIN PORTAL</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-gauge-high text-primary"></i>
                <span>Operations & Control Dashboard</span>
            </h1>
            <p class="page-subtitle">Welcome back, {{ auth()->user()->name }}. Here is an overview of platform activity and pending queues.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @hasPermission('deposits.view')
            <a href="{{ route('admin.deposits.index', ['status' => 'pending']) }}" class="btn btn-warning" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-clock me-1"></i> Pending Deposits ({{ $pendingDeposits }})
            </a>
            @endhasPermission
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted fw-semibold" style="font-size: 13px;">Total Users</span>
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1">{{ number_format($totalUsers) }}</h3>
                <span class="text-success" style="font-size: 12px;"><i class="fa-solid fa-arrow-trend-up"></i> Registered</span>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted fw-semibold" style="font-size: 13px;">Pending Deposits</span>
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1" style="color: {{ $pendingDeposits > 0 ? '#f59e0b' : '#10b981' }}">{{ number_format($pendingDeposits) }}</h3>
                <span class="text-muted" style="font-size: 12px;">Needs Verification</span>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted fw-semibold" style="font-size: 13px;">Pending Withdrawals</span>
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1" style="color: {{ $pendingWithdrawals > 0 ? '#ef4444' : '#10b981' }}">{{ number_format($pendingWithdrawals) }}</h3>
                <span class="text-muted" style="font-size: 12px;">Awaiting Processing</span>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted fw-semibold" style="font-size: 13px;">Today Calls</span>
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-phone"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1" style="color: #10b981">{{ number_format($totalCallsToday) }}</h3>
                <span class="text-muted" style="font-size: 12px;">Calls Established Today</span>
            </div>
        </div>
    </div>

    <!-- Recent Lists Grid -->
    <div class="row g-4">
        <!-- Recent Pending Deposits -->
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
                <div class="card-header bg-transparent border-0 p-3 px-4 d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-6"><i class="fa-solid fa-clock text-warning me-2"></i> Recent Deposits</span>
                    @hasPermission('deposits.view')
                    <a href="{{ route('admin.deposits.index') }}" class="btn btn-sm btn-link text-decoration-none">View All</a>
                    @endhasPermission
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                            <thead style="background: rgba(0,0,0,0.02);">
                                <tr>
                                    <th class="ps-4">User</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentDeposits as $d)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold">{{ $d->user->name ?? 'User #' . $d->user_id }}</div>
                                            <small class="text-muted">{{ $d->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td class="fw-semibold text-success">৳ {{ number_format($d->amount, 2) }}</td>
                                        <td><span class="badge bg-light text-dark">{{ $d->paymentMethod->name ?? 'Manual' }}</span></td>
                                        <td>
                                            @if($d->status === 'approved')
                                                <span class="badge bg-success-subtle text-success">Approved</span>
                                            @elseif($d->status === 'pending')
                                                <span class="badge bg-warning-subtle text-warning">Pending</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Rejected</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted">No recent deposit records.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Audit Trail -->
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
                <div class="card-header bg-transparent border-0 p-3 px-4 d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-6"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Recent Activity Audit</span>
                    @hasPermission('activity_logs.view')
                    <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-sm btn-link text-decoration-none">View All</a>
                    @endhasPermission
                </div>
                <div class="card-body p-3">
                    <div class="d-flex flex-column gap-3">
                        @forelse($recentActivities as $act)
                            <div class="d-flex align-items-start gap-3 p-2 rounded" style="background: rgba(0,0,0,0.015);">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #3b82f6; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold; flex-shrink: 0;">
                                    {{ strtoupper(substr($act->user->name ?? 'S', 0, 1)) }}
                                </div>
                                <div class="flex-grow-1" style="font-size: 12.5px;">
                                    <div><strong>{{ $act->user->name ?? 'System' }}</strong>: {{ $act->description }}</div>
                                    <div class="text-muted mt-1" style="font-size: 11px;">
                                        <span class="badge bg-light text-secondary me-1">{{ $act->action }}</span>
                                        {{ $act->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">No activity records yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
