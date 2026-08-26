@extends('layouts.admin')

@section('title', 'Manual Deposit Requests')

@section('content')
<div class="deposits-container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="page-title mb-1" style="font-size: 24px; font-weight: 700; color: var(--text-primary);">Deposit Requests</h1>
            <p class="text-muted mb-0" style="font-size: 14px;">Review manual deposit requests submitted via bKash, Nagad, etc., verify TrxIDs, and approve coin crediting.</p>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="custom-alert alert-success mb-4">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="custom-alert alert-danger mb-4">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Metric Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrapper" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Pending Approval</span>
                    <h3 class="stat-number" style="color: #f59e0b;">{{ number_format($stats['pending']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrapper" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Approved Deposits</span>
                    <h3 class="stat-number" style="color: #10b981;">{{ number_format($stats['approved']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrapper" style="background: rgba(59, 130, 246, 0.12); color: #3b82f6;">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Total Deposited Amount</span>
                    <h3 class="stat-number">৳ {{ number_format($stats['total_amount'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrapper" style="background: rgba(139, 92, 246, 0.12); color: #8b5cf6;">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Total Coins Credited</span>
                    <h3 class="stat-number">{{ number_format($stats['total_coins']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Tabs & Search -->
    <div class="card mb-4" style="border: 1px solid var(--border-color); border-radius: var(--radius-md); background: var(--bg-card);">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <!-- Status Filter Pills -->
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.deposits.index', ['status' => 'all']) }}" class="btn-filter-pill {{ $status === 'all' ? 'active' : '' }}">
                        All ({{ $stats['total'] }})
                    </a>
                    <a href="{{ route('admin.deposits.index', ['status' => 'pending']) }}" class="btn-filter-pill {{ $status === 'pending' ? 'active' : '' }}" style="position: relative;">
                        Pending ({{ $stats['pending'] }})
                        @if($stats['pending'] > 0)
                            <span class="badge bg-danger rounded-pill ms-1">{{ $stats['pending'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.deposits.index', ['status' => 'approved']) }}" class="btn-filter-pill {{ $status === 'approved' ? 'active' : '' }}">
                        Approved ({{ $stats['approved'] }})
                    </a>
                    <a href="{{ route('admin.deposits.index', ['status' => 'rejected']) }}" class="btn-filter-pill {{ $status === 'rejected' ? 'active' : '' }}">
                        Rejected ({{ $stats['rejected'] }})
                    </a>
                </div>

                <!-- Search form -->
                <form action="{{ route('admin.deposits.index') }}" method="GET" class="d-flex gap-2" style="min-width: 280px;">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <div class="search-input-group w-100">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="text" name="search" class="form-control-custom" placeholder="Search TrxID, sender number or user..." value="{{ request('search') }}">
                    </div>
                    <button type="submit" class="btn-primary-custom"><i class="fa-solid fa-search"></i></button>
                </form>
            </div>
        </div>
    </div>

    <!-- Deposits Table -->
    <div class="card" style="border: 1px solid var(--border-color); border-radius: var(--radius-md); background: var(--bg-card); overflow: hidden;">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Req #</th>
                        <th>User</th>
                        <th>Method</th>
                        <th>Sender Number</th>
                        <th>Transaction ID (TrxID)</th>
                        <th>Amount & Coins</th>
                        <th>Screenshot</th>
                        <th>Status</th>
                        <th>Submitted At</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deposits as $deposit)
                        <tr>
                            <td>
                                <strong style="font-size: 13px; color: var(--text-primary);">#{{ $deposit->id }}</strong>
                            </td>
                            <td>
                                @if($deposit->user)
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $deposit->user->avatar_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($deposit->user->display_name) . '&background=3b82f6&color=fff' }}" 
                                             alt="" 
                                             class="rounded-circle" 
                                             style="width: 36px; height: 36px; object-fit: cover;">
                                        <div>
                                            <a href="{{ route('admin.users.show', $deposit->user_id) }}" class="fw-bold text-decoration-none" style="color: var(--text-primary); font-size: 13px;">
                                                {{ $deposit->user->display_name }}
                                            </a>
                                            <small class="text-muted d-block" style="font-size: 11px;">ID: {{ $deposit->user->account_id }} &bull; {{ $deposit->user->phone }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">User deleted</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary" style="font-size: 12px;">{{ $deposit->payment_method_name }}</span>
                            </td>
                            <td>
                                <span class="font-monospace fw-bold" style="font-size: 13px;">{{ $deposit->sender_number }}</span>
                            </td>
                            <td>
                                <div class="d-inline-flex align-items-center gap-1">
                                    <code class="badge-account-id">{{ $deposit->transaction_id }}</code>
                                    <button type="button" class="btn btn-sm btn-link text-muted p-0" title="Copy TrxID" onclick="navigator.clipboard.writeText('{{ $deposit->transaction_id }}'); alert('TrxID copied!');">
                                        <i class="fa-regular fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong style="color: var(--text-primary);">৳ {{ number_format($deposit->amount, 2) }}</strong>
                                    <small class="d-block text-warning fw-bold"><i class="fa-solid fa-coins me-1"></i>{{ number_format($deposit->coins) }} Coins</small>
                                </div>
                            </td>
                            <td>
                                @if($deposit->screenshot_url)
                                    <a href="javascript:void(0)" onclick="previewScreenshot('{{ $deposit->screenshot_url }}')" title="Click to enlarge">
                                        <img src="{{ $deposit->screenshot_url }}" alt="Receipt" style="width: 42px; height: 42px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border-color);">
                                    </a>
                                @else
                                    <span class="text-muted" style="font-size: 12px;">No screenshot</span>
                                @endif
                            </td>
                            <td>
                                <span class="status-badge {{ $deposit->status === 'approved' ? 'badge-active' : ($deposit->status === 'rejected' ? 'badge-inactive' : 'badge-warning') }}">
                                    <span class="status-dot"></span>
                                    {{ ucfirst($deposit->status) }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $deposit->created_at->format('M d, Y') }}<br>{{ $deposit->created_at->format('h:i A') }}</small>
                            </td>
                            <td style="text-align: right;">
                                @if($deposit->status === 'pending')
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" 
                                                class="btn-action" 
                                                style="background: #10b981; color: #fff; border-color: #10b981;" 
                                                title="Approve & Credit Coins"
                                                onclick="openApproveModal('{{ $deposit->id }}', '{{ $deposit->coins }}', '{{ addslashes($deposit->user?->display_name) }}', '{{ $deposit->amount }}')">
                                            <i class="fa-solid fa-check me-1"></i> Approve
                                        </button>
                                        <button type="button" 
                                                class="btn-action" 
                                                style="background: #ef4444; color: #fff; border-color: #ef4444;" 
                                                title="Reject Request"
                                                onclick="openRejectModal('{{ $deposit->id }}', '{{ addslashes($deposit->user?->display_name) }}')">
                                            <i class="fa-solid fa-xmark me-1"></i> Reject
                                        </button>
                                    </div>
                                @else
                                    <small class="text-muted">
                                        {{ $deposit->status === 'approved' ? 'Credited' : 'Rejected' }}<br>
                                        {{ $deposit->approved_at ? $deposit->approved_at->format('M d, h:i A') : '' }}
                                    </small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-inbox fa-3x mb-3 text-muted"></i>
                                <p class="mb-0">No deposit requests found in this view.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($deposits->hasPages())
            <div class="p-3 d-flex justify-content-end" style="border-top: 1px solid var(--border-color);">
                {{ $deposits->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal for Approve Deposit -->
<div class="custom-modal-backdrop" id="approveModal" style="display: none;">
    <div class="custom-modal-dialog">
        <div class="custom-modal-content">
            <div class="custom-modal-header" style="background: rgba(16, 185, 129, 0.1);">
                <h5 class="modal-title text-success d-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Approve Deposit & Credit Coins</span>
                </h5>
                <button type="button" class="btn-close-modal" onclick="closeApproveModal()">&times;</button>
            </div>
            <form id="approveForm" method="POST" action="">
                @csrf
                <div class="custom-modal-body">
                    <p style="font-size: 14px; color: var(--text-primary);">
                        Are you sure you want to approve this deposit?
                    </p>
                    <div class="p-3 mb-3 rounded" style="background: var(--bg-main); border: 1px solid var(--border-color);">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">User:</span>
                            <strong id="approveUserName"></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Deposit Amount:</span>
                            <strong id="approveAmount"></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Coins to Credit:</span>
                            <span class="badge bg-warning text-dark font-monospace" style="font-size: 14px;">
                                <i class="fa-solid fa-coins me-1"></i> <span id="approveCoins"></span> Coins
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">Admin Note / Remarks (Optional)</label>
                        <input type="text" name="admin_note" class="form-control-custom" placeholder="e.g. Verified TrxID successfully">
                    </div>
                </div>
                <div class="custom-modal-footer">
                    <button type="button" class="btn-secondary-custom" onclick="closeApproveModal()">Cancel</button>
                    <button type="submit" class="btn-primary-custom" style="background: #10b981;"><i class="fa-solid fa-check me-1"></i> Confirm & Credit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for Reject Deposit -->
<div class="custom-modal-backdrop" id="rejectModal" style="display: none;">
    <div class="custom-modal-dialog">
        <div class="custom-modal-content">
            <div class="custom-modal-header" style="background: rgba(239, 68, 68, 0.1);">
                <h5 class="modal-title text-danger d-flex align-items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Reject Deposit Request</span>
                </h5>
                <button type="button" class="btn-close-modal" onclick="closeRejectModal()">&times;</button>
            </div>
            <form id="rejectForm" method="POST" action="">
                @csrf
                <div class="custom-modal-body">
                    <p style="font-size: 14px;">
                        Rejecting deposit request for <strong id="rejectUserName"></strong>. No coins will be credited.
                    </p>
                    <div class="mb-3">
                        <label class="form-label-custom">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea name="admin_note" class="form-control-custom" rows="3" placeholder="e.g. Invalid TrxID / Amount not received / Duplicate submission" required></textarea>
                    </div>
                </div>
                <div class="custom-modal-footer">
                    <button type="button" class="btn-secondary-custom" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="btn-primary-custom" style="background: #ef4444;"><i class="fa-solid fa-xmark me-1"></i> Reject Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Lightbox Modal for Screenshot Preview -->
<div class="custom-modal-backdrop" id="screenshotLightboxModal" style="display: none;" onclick="closeScreenshotLightbox()">
    <div style="max-width: 90%; max-height: 90%; text-align: center;">
        <img id="lightboxImg" src="" alt="Receipt Proof" style="max-width: 100%; max-height: 85vh; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
        <p class="text-white mt-2" style="font-size: 13px;">Click anywhere to close</p>
    </div>
</div>

@push('styles')
<style>
.btn-filter-pill {
    padding: 7px 16px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 20px;
    background: var(--bg-main);
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: var(--transition);
}
.btn-filter-pill:hover, .btn-filter-pill.active {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}
</style>
@endpush

@push('scripts')
<script>
function openApproveModal(depositId, coins, userName, amount) {
    const modal = document.getElementById('approveModal');
    const form = document.getElementById('approveForm');
    form.action = `/admin/deposits/${depositId}/approve`;
    document.getElementById('approveUserName').innerText = userName;
    document.getElementById('approveAmount').innerText = '৳ ' + Number(amount).toLocaleString();
    document.getElementById('approveCoins').innerText = Number(coins).toLocaleString();
    modal.style.display = 'flex';
}
function closeApproveModal() {
    document.getElementById('approveModal').style.display = 'none';
}

function openRejectModal(depositId, userName) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = `/admin/deposits/${depositId}/reject`;
    document.getElementById('rejectUserName').innerText = userName;
    modal.style.display = 'flex';
}
function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}

function previewScreenshot(url) {
    document.getElementById('lightboxImg').src = url;
    document.getElementById('screenshotLightboxModal').style.display = 'flex';
}
function closeScreenshotLightbox() {
    document.getElementById('screenshotLightboxModal').style.display = 'none';
}
</script>
@endpush
@endsection
