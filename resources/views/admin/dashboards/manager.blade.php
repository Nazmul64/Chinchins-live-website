@extends('layouts.admin')

@section('title', 'Manager Dashboard')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success-subtle text-success fw-bold" style="font-size: 12px; border-radius: 6px;">MANAGER DASHBOARD</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-briefcase text-success"></i>
                <span>Operations & Review Queue</span>
            </h1>
            <p class="page-subtitle">Welcome back, {{ auth()->user()->name }}. Review pending approvals and supervise operations.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @hasPermission('deposits.approve')
            <a href="{{ route('admin.deposits.index', ['status' => 'pending']) }}" class="btn btn-primary" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-check-double me-1"></i> Review Deposits ({{ $pendingDeposits }})
            </a>
            @endhasPermission
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted fw-semibold" style="font-size: 13px;">Pending Deposits</span>
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1" style="color: #f59e0b;">{{ number_format($pendingDeposits) }}</h3>
                <span class="text-muted" style="font-size: 12px;">Waiting Approval</span>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted fw-semibold" style="font-size: 13px;">Pending Withdrawals</span>
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1" style="color: #ef4444;">{{ number_format($pendingWithdrawals) }}</h3>
                <span class="text-muted" style="font-size: 12px;">Needs Payment</span>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted fw-semibold" style="font-size: 13px;">Total Calls Logged</span>
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-phone"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1">{{ number_format($totalCalls) }}</h3>
                <span class="text-muted" style="font-size: 12px;">All Time</span>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted fw-semibold" style="font-size: 13px;">Active App Users</span>
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1" style="color: #10b981;">{{ number_format($activeUsers) }}</h3>
                <span class="text-muted" style="font-size: 12px;">Non-locked Accounts</span>
            </div>
        </div>
    </div>

    <!-- Review Queues -->
    <div class="row g-4">
        <!-- Deposits Queue -->
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
                <div class="card-header bg-transparent border-0 p-3 px-4 d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-6"><i class="fa-solid fa-money-check text-primary me-2"></i> Pending Deposits Queue</span>
                    @hasPermission('deposits.view')
                    <a href="{{ route('admin.deposits.index', ['status' => 'pending']) }}" class="btn btn-sm btn-link text-decoration-none">View All</a>
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
                                    <th class="text-end pe-4">Action</th>
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
                                        <td class="text-end pe-4">
                                            @hasPermission('deposits.approve')
                                            <a href="{{ route('admin.deposits.index') }}" class="btn btn-sm btn-outline-primary" style="border-radius: 6px;">Review</a>
                                            @endhasPermission
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted">No pending deposit requests.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Withdrawals Queue -->
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: var(--bg-card, #ffffff);">
                <div class="card-header bg-transparent border-0 p-3 px-4 d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-6"><i class="fa-solid fa-hand-holding-dollar text-danger me-2"></i> Pending Withdrawals Queue</span>
                    @hasPermission('withdrawals.view')
                    <a href="{{ route('admin.withdrawals.index', ['status' => 'pending']) }}" class="btn btn-sm btn-link text-decoration-none">View All</a>
                    @endhasPermission
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                            <thead style="background: rgba(0,0,0,0.02);">
                                <tr>
                                    <th class="ps-4">User</th>
                                    <th>Payable</th>
                                    <th>Method</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentWithdrawals as $w)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold">{{ $w->user->name ?? 'User #' . $w->user_id }}</div>
                                            <small class="text-muted">{{ $w->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td class="fw-semibold text-danger">৳ {{ number_format($w->net_payable_amount, 2) }}</td>
                                        <td><span class="badge bg-light text-dark">{{ $w->paymentMethod->name ?? 'Manual' }}</span></td>
                                        <td class="text-end pe-4">
                                            @hasPermission('withdrawals.approve')
                                            <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-sm btn-outline-danger" style="border-radius: 6px;">Process</a>
                                            @endhasPermission
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted">No pending withdrawal requests.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
