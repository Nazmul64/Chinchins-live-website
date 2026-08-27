@extends('layouts.admin')

@section('title', 'Withdrawal Requests')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Withdrawals</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-hand-holding-dollar text-primary"></i>
                <span>User Withdrawal Requests</span>
            </h1>
            <p class="page-subtitle">Review and manage user cash out requests. Approving a request immediately deducts coins from the user's wallet.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.withdrawals.settings') }}" class="btn btn-outline-primary" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-sliders text-primary me-1"></i> Withdrawal Settings
            </a>
            <a href="{{ route('admin.transactions.index') }}" class="btn-ch-primary">
                <i class="fa-solid fa-receipt"></i> Coin Ledger
            </a>
        </div>
    </div>

    <!-- Metric Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #f59e0b;">
                <div class="stat-icon-box stat-icon-gold">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Pending Approval</span>
                    <h3 class="stat-count-value" style="color: #d97706;">{{ number_format($stats['pending']) }}</h3>
                    @if($stats['pending'] > 0)
                        <span class="stat-badge-chip" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                            <span class="status-pulsing-dot"></span> Needs Review
                        </span>
                    @else
                        <span class="stat-badge-chip" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                            <i class="fa-solid fa-check"></i> All Clear
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card">
                <div class="stat-icon-box stat-icon-green">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Approved Payouts</span>
                    <h3 class="stat-count-value" style="color: #10b981;">{{ number_format($stats['approved']) }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i class="fa-solid fa-check-double"></i> Completed
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card">
                <div class="stat-icon-box stat-icon-blue">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Net Paid Out (BDT)</span>
                    <h3 class="stat-count-value">৳ {{ number_format($stats['total_paid'], 2) }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                        <i class="fa-solid fa-coins"></i> {{ number_format($stats['total_coins']) }} Coins
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card">
                <div class="stat-icon-box stat-icon-purple">
                    <i class="fa-solid fa-percent"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Platform Commission</span>
                    <h3 class="stat-count-value" style="color: #8b5cf6;">৳ {{ number_format($stats['total_commission'], 2) }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                        <i class="fa-solid fa-chart-pie"></i> Revenue Earned
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Navigation Tabs & Search -->
    <div class="filter-card-wrapper mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <!-- Filter Tabs -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('admin.withdrawals.index', ['status' => 'all', 'search' => request('search')]) }}" 
                   class="filter-pill-tab {{ $status === 'all' ? 'active' : '' }}">
                    All Requests
                    <span class="filter-pill-badge">{{ $stats['total'] }}</span>
                </a>
                <a href="{{ route('admin.withdrawals.index', ['status' => 'pending', 'search' => request('search')]) }}" 
                   class="filter-pill-tab {{ $status === 'pending' ? 'active' : '' }}">
                    <i class="fa-solid fa-clock text-warning me-1"></i> Pending
                    @if($stats['pending'] > 0)
                        <span class="filter-pill-badge" style="background: #ef4444; color: #fff;">{{ $stats['pending'] }}</span>
                    @else
                        <span class="filter-pill-badge">0</span>
                    @endif
                </a>
                <a href="{{ route('admin.withdrawals.index', ['status' => 'approved', 'search' => request('search')]) }}" 
                   class="filter-pill-tab {{ $status === 'approved' ? 'active' : '' }}">
                    <i class="fa-solid fa-circle-check text-success me-1"></i> Approved
                    <span class="filter-pill-badge">{{ $stats['approved'] }}</span>
                </a>
                <a href="{{ route('admin.withdrawals.index', ['status' => 'rejected', 'search' => request('search')]) }}" 
                   class="filter-pill-tab {{ $status === 'rejected' ? 'active' : '' }}">
                    <i class="fa-solid fa-circle-xmark text-danger me-1"></i> Rejected
                    <span class="filter-pill-badge">{{ $stats['rejected'] }}</span>
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('admin.withdrawals.index') }}" method="GET" class="d-flex align-items-center gap-2">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="search-input-wrapper">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" name="search" class="form-control search-input" 
                           placeholder="Search by phone, TrxID, User ID..." 
                           value="{{ request('search') }}">
                    @if(request('search'))
                        <a href="{{ route('admin.withdrawals.index', ['status' => $status]) }}" class="search-clear-btn" title="Clear">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
                <button type="submit" class="btn btn-secondary" style="border-radius: 10px; font-weight: 600; padding: 8px 14px; font-size: 13px;">
                    Search
                </button>
            </form>
        </div>
    </div>

    <!-- Withdrawals Table Card -->
    <div class="premium-table-card">
        <div class="table-responsive">
            <table class="table align-middle ch-datatable mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>User Profile</th>
                        <th>Requested Coins</th>
                        <th>Net Payable BDT</th>
                        <th>Payout Gateway</th>
                        <th>Status</th>
                        <th>Date & Details</th>
                        <th style="width: 180px;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($withdrawals as $item)
                        <tr>
                            <!-- Request ID -->
                            <td>
                                <span class="fw-bold text-muted" style="font-size: 13px;">#{{ $item->id }}</span>
                            </td>

                            <!-- User Info -->
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar-small">
                                        @if($item->user && $item->user->avatar_url)
                                            <img src="{{ $item->user->avatar_url }}" alt="avatar">
                                        @else
                                            <div class="avatar-placeholder">{{ strtoupper(substr($item->user->name ?? 'U', 0, 1)) }}</div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 13px;">{{ $item->user->display_name ?? 'Unknown User' }}</div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            ID: <code>{{ $item->user->account_id ?? 'N/A' }}</code> &bull; Bal: <span class="fw-semibold text-primary">{{ number_format($item->user->coins ?? 0) }} coins</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Requested Coins -->
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <i class="fa-solid fa-coins text-warning" style="font-size: 14px;"></i>
                                    <span class="fw-bold text-dark" style="font-size: 14px;">{{ number_format($item->coins) }}</span>
                                </div>
                                <div class="text-muted" style="font-size: 11px;">
                                    Gross: ৳{{ number_format($item->gross_amount, 2) }}
                                </div>
                            </td>

                            <!-- Net Payable BDT & Commission -->
                            <td>
                                <div class="fw-bold text-success" style="font-size: 14px;">
                                    ৳ {{ number_format($item->net_payable_amount, 2) }}
                                </div>
                                <div class="text-muted" style="font-size: 11px;">
                                    Fee: ৳{{ number_format($item->commission_amount, 2) }} ({{ $item->commission_percent }}%)
                                </div>
                            </td>

                            <!-- Payout Gateway & Account Number -->
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($item->payment_method_icon_url)
                                        <img src="{{ $item->payment_method_icon_url }}" alt="icon" style="width: 24px; height: 24px; object-fit: contain; border-radius: 4px;">
                                    @else
                                        <i class="fa-solid fa-building-columns text-primary"></i>
                                    @endif
                                    <div>
                                        <span class="badge bg-light text-dark fw-semibold" style="font-size: 11px; border: 1px solid #e2e8f0;">
                                            {{ $item->payment_method_name ?: 'bKash / Nagad' }}
                                        </span>
                                        <div class="fw-bold mt-1 text-dark" style="font-size: 12px; font-family: monospace;">
                                            {{ $item->account_number }}
                                            <span class="badge bg-secondary-subtle text-secondary" style="font-size: 9px;">{{ $item->account_type }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td>
                                @if($item->status === 'approved')
                                    <span class="badge badge-soft-success">
                                        <i class="fa-solid fa-circle-check me-1"></i> Approved
                                    </span>
                                @elseif($item->status === 'rejected')
                                    <span class="badge badge-soft-danger">
                                        <i class="fa-solid fa-circle-xmark me-1"></i> Rejected
                                    </span>
                                @else
                                    <span class="badge badge-soft-warning">
                                        <i class="fa-solid fa-clock me-1"></i> Pending
                                    </span>
                                @endif

                                @if($item->transaction_id)
                                    <div class="text-muted mt-1" style="font-size: 11px;">
                                        TrxID: <code>{{ $item->transaction_id }}</code>
                                    </div>
                                @endif
                            </td>

                            <!-- Date & Notes -->
                            <td>
                                <div class="text-dark" style="font-size: 12px;">
                                    <i class="fa-regular fa-calendar text-muted me-1"></i> {{ $item->created_at->format('M d, Y h:i A') }}
                                </div>
                                @if($item->user_note)
                                    <div class="text-muted text-truncate mt-1" style="max-width: 140px; font-size: 11px;" title="User Note: {{ $item->user_note }}">
                                        <i class="fa-regular fa-comment-dots text-info"></i> {{ $item->user_note }}
                                    </div>
                                @endif
                                @if($item->admin_note)
                                    <div class="text-muted text-truncate mt-1" style="max-width: 140px; font-size: 11px;" title="Admin: {{ $item->admin_note }}">
                                        <i class="fa-solid fa-shield text-warning"></i> {{ $item->admin_note }}
                                    </div>
                                @endif
                            </td>

                            <!-- Action Buttons -->
                            <td class="text-end">
                                @if($item->status === 'pending')
                                    <div class="d-flex justify-content-end gap-1">
                                        <button type="button" class="btn btn-sm btn-success px-2 py-1" 
                                                style="border-radius: 8px; font-size: 12px; font-weight: 600;"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#approveModal{{ $item->id }}"
                                                title="Approve and Deduct Coins">
                                            <i class="fa-solid fa-check"></i> Approve
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger px-2 py-1" 
                                                style="border-radius: 8px; font-size: 12px; font-weight: 600;"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#rejectModal{{ $item->id }}"
                                                title="Reject Request">
                                            <i class="fa-solid fa-xmark"></i> Reject
                                        </button>
                                    </div>

                                    <!-- Approve Modal -->
                                    <div class="modal fade" id="approveModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
                                                <form action="{{ route('admin.withdrawals.approve', $item->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title fw-bold text-success">
                                                            <i class="fa-solid fa-circle-check me-2"></i> Approve Withdrawal #{{ $item->id }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-start pt-3">
                                                        <div class="p-3 mb-3" style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                                                            <div class="d-flex justify-content-between mb-2">
                                                                <span class="text-muted">User:</span>
                                                                <strong class="text-dark">{{ $item->user->display_name ?? 'N/A' }} (ID: {{ $item->user->account_id ?? 'N/A' }})</strong>
                                                            </div>
                                                            <div class="d-flex justify-content-between mb-2">
                                                                <span class="text-muted">Coins to Deduct:</span>
                                                                <strong class="text-primary fs-6">{{ number_format($item->coins) }} Coins</strong>
                                                            </div>
                                                            <div class="d-flex justify-content-between mb-2">
                                                                <span class="text-muted">User Current Balance:</span>
                                                                <strong class="{{ ($item->user->coins ?? 0) >= $item->coins ? 'text-success' : 'text-danger' }}">
                                                                    {{ number_format($item->user->coins ?? 0) }} Coins
                                                                </strong>
                                                            </div>
                                                            <div class="d-flex justify-content-between mb-2">
                                                                <span class="text-muted">Net Payout to Send:</span>
                                                                <strong class="text-success fs-5">৳ {{ number_format($item->net_payable_amount, 2) }} BDT</strong>
                                                            </div>
                                                            <div class="d-flex justify-content-between">
                                                                <span class="text-muted">Send To Account:</span>
                                                                <strong class="text-dark" style="font-family: monospace;">{{ $item->payment_method_name }} &bull; {{ $item->account_number }} ({{ $item->account_type }})</strong>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold text-dark" style="font-size: 13px;">Payout Transaction ID / Reference (Optional)</label>
                                                            <input type="text" name="transaction_id" class="form-control" placeholder="e.g. bKash TrxID BK928374928" style="border-radius: 8px;">
                                                            <small class="text-muted">The transaction ID sent to the user after completing the mobile payment.</small>
                                                        </div>

                                                        <div class="mb-2">
                                                            <label class="form-label fw-bold text-dark" style="font-size: 13px;">Admin Remark (Optional)</label>
                                                            <textarea name="admin_note" class="form-control" rows="2" placeholder="e.g. Paid via bKash Personal successfully" style="border-radius: 8px;"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                                                        <button type="submit" class="btn btn-success fw-bold" style="border-radius: 8px; padding: 8px 18px;">
                                                            <i class="fa-solid fa-check me-1"></i> Confirm & Deduct Coins
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
                                                <form action="{{ route('admin.withdrawals.reject', $item->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title fw-bold text-danger">
                                                            <i class="fa-solid fa-circle-xmark me-2"></i> Reject Withdrawal #{{ $item->id }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-start pt-3">
                                                        <p class="text-muted" style="font-size: 13px;">Are you sure you want to reject this withdrawal request of <strong>{{ number_format($item->coins) }} coins (৳{{ number_format($item->net_payable_amount, 2) }})</strong> for <strong>{{ $item->user->display_name ?? 'User' }}</strong>? Coins will NOT be deducted from the user.</p>

                                                        <div class="mb-2">
                                                            <label class="form-label fw-bold text-dark" style="font-size: 13px;">Reason for Rejection</label>
                                                            <textarea name="admin_note" class="form-control" rows="3" placeholder="e.g. Invalid account number provided, or suspicious activity" style="border-radius: 8px;" required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                                                        <button type="submit" class="btn btn-danger fw-bold" style="border-radius: 8px; padding: 8px 18px;">
                                                            <i class="fa-solid fa-ban me-1"></i> Reject Request
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted" style="font-size: 12px;">
                                        @if($item->approver)
                                            by <strong class="text-dark">{{ $item->approver->name }}</strong>
                                        @else
                                            Processed
                                        @endif
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="py-4">
                                    <i class="fa-solid fa-hand-holding-dollar text-muted mb-3" style="font-size: 48px; opacity: 0.3;"></i>
                                    <h5 class="fw-bold text-muted">No withdrawal requests found</h5>
                                    <p class="text-muted mb-0" style="font-size: 13px;">When users submit cash out requests from the app, they will appear here for review.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($withdrawals->hasPages())
            <div class="p-3 border-top">
                {{ $withdrawals->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
