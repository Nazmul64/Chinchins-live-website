@extends('layouts.admin')

@section('title', 'Manual Deposit Requests')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Deposit Requests</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-money-bill-transfer text-warning"></i>
                <span>Manual Deposit Requests</span>
            </h1>
            <p class="page-subtitle">Verify user payments sent via bKash, Nagad, and Rocket. Approve requests to instantly credit coins.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-credit-card text-success me-1"></i> Payment Gateways
            </a>
            <a href="{{ route('admin.transactions.index') }}" class="btn-ch-primary">
                <i class="fa-solid fa-receipt"></i> Transactions Ledger
            </a>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-3 p-3 mb-4 rounded-4 shadow-sm border-0" style="background: rgba(16, 185, 129, 0.12); color: #047857;">
            <i class="fa-solid fa-circle-check fa-lg"></i>
            <div class="fw-semibold">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-3 p-3 mb-4 rounded-4 shadow-sm border-0" style="background: rgba(239, 68, 68, 0.12); color: #b91c1c;">
            <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
            <div class="fw-semibold">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

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
                    <span class="stat-title-label">Approved Deposits</span>
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
                    <span class="stat-title-label">Deposited Volume</span>
                    <h3 class="stat-count-value">৳ {{ number_format($stats['total_amount'], 2) }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                        <i class="fa-solid fa-vault"></i> BDT Collected
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card">
                <div class="stat-icon-box stat-icon-purple">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Coins Credited</span>
                    <h3 class="stat-count-value" style="color: #8b5cf6;">{{ number_format($stats['total_coins']) }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                        <i class="fa-solid fa-coins"></i> Total Granted
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Navigation Tabs & Search -->
    <div class="filter-card-wrapper">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <!-- Filter Tabs -->
            <div class="filter-nav-tabs">
                <a href="{{ route('admin.deposits.index', ['status' => 'all']) }}" class="filter-tab-btn {{ $status === 'all' ? 'active' : '' }}">
                    All Requests ({{ $stats['total'] }})
                </a>
                <a href="{{ route('admin.deposits.index', ['status' => 'pending']) }}" class="filter-tab-btn {{ $status === 'pending' ? 'active' : '' }}">
                    Pending
                    @if($stats['pending'] > 0)
                        <span class="badge bg-danger rounded-pill">{{ $stats['pending'] }}</span>
                    @else
                        <span class="badge bg-secondary rounded-pill">0</span>
                    @endif
                </a>
                <a href="{{ route('admin.deposits.index', ['status' => 'approved']) }}" class="filter-tab-btn {{ $status === 'approved' ? 'active' : '' }}">
                    Approved ({{ $stats['approved'] }})
                </a>
                <a href="{{ route('admin.deposits.index', ['status' => 'rejected']) }}" class="filter-tab-btn {{ $status === 'rejected' ? 'active' : '' }}">
                    Rejected ({{ $stats['rejected'] }})
                </a>
            </div>

            <!-- Search Field -->
            <form action="{{ route('admin.deposits.index') }}" method="GET" class="d-flex gap-2" style="min-width: 320px;">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="search-pill-box w-100">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" class="search-pill-input" placeholder="Search by TrxID, sender number or user..." value="{{ request('search') }}">
                </div>
                <button type="submit" class="btn-ch-primary" style="padding: 10px 16px;">
                    <i class="fa-solid fa-search"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Deposits Datatable Card -->
    <div class="premium-table-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0 fw-bold" style="font-size: 16px; color: var(--text-primary);">
                    Deposit Requests Registry
                </h5>
                <span class="badge bg-primary rounded-pill px-2 py-1">{{ $deposits->total() }}</span>
            </div>
            <small class="text-muted">Showing {{ $deposits->firstItem() ?? 0 }}-{{ $deposits->lastItem() ?? 0 }} of {{ $deposits->total() }}</small>
        </div>
        <div class="table-responsive">
            <table class="premium-datatable">
                <thead>
                    <tr>
                        <th>Req #</th>
                        <th>User Profile</th>
                        <th>Gateway</th>
                        <th>Sender Number</th>
                        <th>Transaction ID (TrxID)</th>
                        <th>Deposit & Coins</th>
                        <th>Receipt Proof</th>
                        <th>Status</th>
                        <th>Submitted At</th>
                        <th style="text-align: right;">Review Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deposits as $deposit)
                        @php
                            $brandCode = strtolower(trim($deposit->payment_method_name));
                            if (str_contains($brandCode, 'bkash')) $brandClass = 'bkash';
                            elseif (str_contains($brandCode, 'nagad')) $brandClass = 'nagad';
                            elseif (str_contains($brandCode, 'rocket')) $brandClass = 'rocket';
                            elseif (str_contains($brandCode, 'upay')) $brandClass = 'upay';
                            else $brandClass = 'general';
                        @endphp
                        <tr>
                            <td>
                                <strong class="font-monospace text-muted" style="font-size: 13px;">#{{ $deposit->id }}</strong>
                            </td>
                            <td>
                                @if($deposit->user)
                                    <div class="user-avatar-group">
                                        <img src="{{ $deposit->user->avatar_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($deposit->user->display_name) . '&background=3b82f6&color=fff' }}" 
                                             alt="" 
                                             class="user-avatar-img" style="width: 40px; height: 40px;">
                                        <div>
                                            <a href="{{ route('admin.users.show', $deposit->user_id) }}" class="text-decoration-none fw-bold" style="color: var(--text-primary); font-size: 13px;">
                                                {{ $deposit->user->display_name }}
                                            </a>
                                            <div class="user-sub-info">
                                                ID: {{ $deposit->user->account_id }} &bull; {{ $deposit->user->phone }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted fst-italic">User deleted</span>
                                @endif
                            </td>
                            <td>
                                <span class="gateway-badge {{ $brandClass }}">
                                    {{ $deposit->payment_method_name }}
                                </span>
                            </td>
                            <td>
                                <div class="copyable-chip" onclick="copyToClipboard('{{ $deposit->sender_number }}', 'Sender number copied!')" title="Click to copy">
                                    <span>{{ $deposit->sender_number }}</span>
                                    <i class="fa-regular fa-copy"></i>
                                </div>
                            </td>
                            <td>
                                <div class="copyable-chip" onclick="copyToClipboard('{{ $deposit->transaction_id }}', 'TrxID copied!')" title="Click to copy">
                                    <span class="fw-bold">{{ $deposit->transaction_id }}</span>
                                    <i class="fa-regular fa-copy"></i>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong class="d-block" style="color: var(--text-primary); font-size: 14px;">৳ {{ number_format($deposit->amount, 2) }}</strong>
                                    <div class="coin-badge-3d mt-1" style="padding: 2px 8px;">
                                        <i class="fa-solid fa-coins coin-icon-glow" style="font-size: 11px;"></i>
                                        <span class="coin-amount-text" style="font-size: 12px;">{{ number_format($deposit->coins) }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($deposit->screenshot_url)
                                    <div class="position-relative d-inline-block" style="cursor: pointer;" onclick="previewScreenshot('{{ $deposit->screenshot_url }}')">
                                        <img src="{{ $deposit->screenshot_url }}" alt="Receipt" style="width: 48px; height: 48px; object-fit: cover; border-radius: 10px; border: 2px solid var(--border-color); transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                                        <span class="position-absolute bottom-0 end-0 bg-dark text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 18px; height: 18px; font-size: 9px; opacity: 0.8;">
                                            <i class="fa-solid fa-magnifying-glass-plus"></i>
                                        </span>
                                    </div>
                                @else
                                    <span class="badge bg-light text-muted border">No receipt</span>
                                @endif
                            </td>
                            <td>
                                <span class="status-pill-modern {{ $deposit->status }}">
                                    <span class="status-pulsing-dot"></span>
                                    {{ ucfirst($deposit->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="text-muted" style="font-size: 13px;">
                                    {{ $deposit->created_at->format('M d, Y') }}<br>
                                    <small>{{ $deposit->created_at->format('h:i A') }}</small>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                @if($deposit->status === 'pending')
                                    <div class="d-inline-flex gap-2">
                                        <button type="button" 
                                                class="btn-ch-success" 
                                                title="Approve and Credit Coins"
                                                onclick="openApproveModal('{{ $deposit->id }}', '{{ $deposit->coins }}', '{{ addslashes($deposit->user?->display_name ?? 'User') }}', '{{ $deposit->amount }}', '{{ $deposit->payment_method_name }}', '{{ $deposit->transaction_id }}')">
                                            <i class="fa-solid fa-check"></i> Approve
                                        </button>
                                        <button type="button" 
                                                class="btn-ch-danger" 
                                                title="Reject Request"
                                                onclick="openRejectModal('{{ $deposit->id }}', '{{ addslashes($deposit->user?->display_name ?? 'User') }}')">
                                            <i class="fa-solid fa-xmark"></i> Reject
                                        </button>
                                    </div>
                                @else
                                    <div style="font-size: 12px; color: var(--text-secondary);">
                                        <span class="fw-bold d-block">{{ $deposit->status === 'approved' ? 'Credited' : 'Rejected' }}</span>
                                        <small>{{ $deposit->approved_at ? $deposit->approved_at->format('M d, h:i A') : '' }}</small>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="fa-solid fa-inbox fa-3x mb-3 text-muted" style="opacity: 0.4;"></i>
                                    <h5 class="fw-bold">No Deposit Requests Found</h5>
                                    <p class="text-muted mb-0">There are no deposit requests matching this filter status.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($deposits->hasPages())
            <div class="p-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-top: 1px solid var(--card-border-light);">
                <span class="text-muted" style="font-size: 13px;">Showing page {{ $deposits->currentPage() }} of {{ $deposits->lastPage() }}</span>
                <div>{{ $deposits->links() }}</div>
            </div>
        @endif
    </div>
</div>

<!-- Modal for Approve Deposit -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-modern-dialog">
        <div class="modal-content modal-modern-content">
            <div class="modal-modern-header" style="background: rgba(16, 185, 129, 0.08);">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-box stat-icon-green" style="width: 44px; height: 44px; font-size: 18px;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-success">Approve Deposit & Credit Coins</h5>
                        <small class="text-muted">User will receive coins instantly upon approval</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="approveForm" method="POST" action="">
                @csrf
                <div class="modal-modern-body">
                    <!-- Deposit Details Summary -->
                    <div class="p-3 rounded-4 mb-3" style="background: var(--bg-main); border: 1px solid var(--border-color);">
                        <div class="d-flex justify-content-between py-1 border-bottom" style="border-color: var(--border-color) !important;">
                            <span class="text-muted">Recipient User:</span>
                            <strong id="approveUserName" class="text-primary"></strong>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom" style="border-color: var(--border-color) !important;">
                            <span class="text-muted">Gateway & TrxID:</span>
                            <span class="font-monospace fw-bold" id="approveGatewayTrx"></span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom" style="border-color: var(--border-color) !important;">
                            <span class="text-muted">Amount Received:</span>
                            <strong id="approveAmount" class="text-dark"></strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2">
                            <span class="text-muted fw-bold">Coins to Credit:</span>
                            <div class="coin-badge-3d">
                                <i class="fa-solid fa-coins coin-icon-glow"></i>
                                <span id="approveCoins" class="coin-amount-text"></span>
                                <span class="coin-label-text">Coins</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold" style="font-size: 13px;">Admin Remarks / Note (Optional)</label>
                        <input type="text" name="admin_note" class="form-control rounded-3" placeholder="e.g. TrxID verified in merchant portal">
                    </div>
                </div>
                <div class="modal-modern-footer">
                    <button type="button" class="btn btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-ch-success">
                        <i class="fa-solid fa-check-double"></i> Confirm & Credit Balance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for Reject Deposit -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-modern-dialog">
        <div class="modal-content modal-modern-content">
            <div class="modal-modern-header" style="background: rgba(239, 68, 68, 0.08);">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-box stat-icon-red" style="width: 44px; height: 44px; font-size: 18px;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-danger">Reject Deposit Request</h5>
                        <small class="text-muted">State the reason for rejecting this deposit</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectForm" method="POST" action="">
                @csrf
                <div class="modal-modern-body">
                    <p style="font-size: 14px; color: var(--text-primary);">
                        You are about to reject the deposit request for <strong id="rejectUserName" class="text-danger"></strong>. No coins will be credited.
                    </p>
                    <div class="mb-2">
                        <label class="form-label fw-bold" style="font-size: 13px;">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea name="admin_note" class="form-control rounded-3" rows="3" placeholder="e.g. Invalid TrxID / Amount not received in bKash account / Duplicate submission" required></textarea>
                    </div>
                </div>
                <div class="modal-modern-footer">
                    <button type="button" class="btn btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-ch-danger">
                        <i class="fa-solid fa-xmark"></i> Reject Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Fullscreen Lightbox Modal for Receipt Screenshot Preview -->
<div class="modal fade" id="screenshotLightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 text-center">
            <div class="position-relative d-inline-block mx-auto">
                <button type="button" class="btn btn-light rounded-circle position-absolute top-0 end-0 m-3 shadow" data-bs-dismiss="modal" aria-label="Close" style="z-index: 10;">
                    <i class="fa-solid fa-xmark fa-lg"></i>
                </button>
                <img id="lightboxImg" src="" alt="Payment Receipt" style="max-height: 80vh; max-width: 100%; border-radius: 16px; box-shadow: 0 20px 50px rgba(0,0,0,0.5); object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<div class="ch-toast-container" id="toastContainer"></div>

@push('scripts')
<script>
function openApproveModal(depositId, coins, userName, amount, method, trx) {
    const modalEl = document.getElementById('approveModal');
    const form = document.getElementById('approveForm');
    form.action = `/admin/deposits/${depositId}/approve`;
    document.getElementById('approveUserName').innerText = userName;
    document.getElementById('approveGatewayTrx').innerText = `${method} (${trx})`;
    document.getElementById('approveAmount').innerText = '৳ ' + Number(amount).toLocaleString();
    document.getElementById('approveCoins').innerText = Number(coins).toLocaleString();

    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
}

function openRejectModal(depositId, userName) {
    const modalEl = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = `/admin/deposits/${depositId}/reject`;
    document.getElementById('rejectUserName').innerText = userName;

    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
}

function previewScreenshot(url) {
    document.getElementById('lightboxImg').src = url;
    const modalEl = document.getElementById('screenshotLightboxModal');
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
}

function copyToClipboard(text, msg) {
    navigator.clipboard.writeText(text).then(() => {
        showToast(msg || 'Copied to clipboard!');
    });
}

function showToast(message) {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = 'ch-toast';
    toast.innerHTML = `<i class="fa-solid fa-circle-check text-success"></i> <span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        setTimeout(() => toast.remove(), 300);
    }, 2500);
}
</script>
@endpush
@endsection
