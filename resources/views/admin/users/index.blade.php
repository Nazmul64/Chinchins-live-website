@extends('layouts.admin')

@section('title', 'Users & Coin Balance Management')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Users Management</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-users text-primary"></i>
                <span>Users & Coin Balance</span>
            </h1>
            <p class="page-subtitle">View and search registered users, manage account statuses, and manually credit/debit coin balances.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.deposits.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-money-bill-transfer text-warning me-1"></i> View Deposits
            </a>
            <a href="{{ route('admin.transactions.index') }}" class="btn-ch-primary">
                <i class="fa-solid fa-receipt"></i> Coin Ledger
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

    <!-- 4 Metric Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card">
                <div class="stat-icon-box stat-icon-blue">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Total Users</span>
                    <h3 class="stat-count-value">{{ number_format($stats['total_users']) }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                        <i class="fa-solid fa-database"></i> Registered
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card">
                <div class="stat-icon-box stat-icon-green">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Active / Online</span>
                    <h3 class="stat-count-value" style="color: #10b981;">{{ number_format($stats['active_users']) }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <span class="status-pulsing-dot"></span> Live in App
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card">
                <div class="stat-icon-box stat-icon-gold">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">System Coins</span>
                    <h3 class="stat-count-value" style="color: #d97706;">{{ number_format($stats['total_coins']) }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(245, 158, 11, 0.12); color: #d97706;">
                        <i class="fa-solid fa-coins"></i> Circulating
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card">
                <div class="stat-icon-box stat-icon-purple">
                    <i class="fa-solid fa-badge-check"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Verified Users</span>
                    <h3 class="stat-count-value" style="color: #8b5cf6;">{{ number_format($stats['verified_users']) }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                        <i class="fa-solid fa-check-double"></i> Verified Profile
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="filter-card-wrapper">
        <form action="{{ route('admin.users.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-lg-5">
                <div class="search-pill-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" class="search-pill-input" placeholder="Search by name, phone number, 10-digit Account ID, or email..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <select name="status" class="custom-select-pill" onchange="this.form.submit()">
                    <option value="">Status: All Users</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active / Online Only</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive / Offline Only</option>
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <select name="sort" class="custom-select-pill" onchange="this.form.submit()">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Sort: Newest First</option>
                    <option value="coins_high" {{ request('sort') == 'coins_high' ? 'selected' : '' }}>Sort: Most Coins</option>
                    <option value="coins_low" {{ request('sort') == 'coins_low' ? 'selected' : '' }}>Sort: Least Coins</option>
                </select>
            </div>
            <div class="col-12 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn-ch-primary w-100 justify-content-center">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                @if(request()->hasAny(['search', 'status', 'sort', 'gender']))
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="border-radius: 10px; width: 44px;" title="Reset Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Users Table Card -->
    <div class="premium-table-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0 fw-bold" style="font-size: 16px; color: var(--text-primary);">
                    Registered Users Directory
                </h5>
                <span class="badge bg-primary rounded-pill px-2 py-1" style="font-size: 12px;">{{ $users->total() }}</span>
            </div>
            <small class="text-muted">Showing {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} of {{ $users->total() }}</small>
        </div>
        <div class="table-responsive">
            <table class="premium-datatable">
                <thead>
                    <tr>
                        <th>User Profile</th>
                        <th>Account ID</th>
                        <th>Phone Number</th>
                        <th>Coin Balance</th>
                        <th>Call Rate</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th style="text-align: right;">Quick Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="user-avatar-group">
                                    <div class="user-avatar-wrapper">
                                        <img src="{{ $user->avatar_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($user->display_name) . '&background=3b82f6&color=fff' }}" 
                                             alt="{{ $user->display_name }}" 
                                             class="user-avatar-img">
                                        <span class="online-pulse-dot {{ $user->is_active ? 'online' : 'offline' }}"></span>
                                    </div>
                                    <div>
                                        <div class="user-name-title">
                                            <a href="{{ route('admin.users.show', $user->id) }}" class="text-decoration-none" style="color: var(--text-primary);">
                                                {{ $user->display_name }}
                                            </a>
                                            @if($user->is_verified)
                                                <i class="fa-solid fa-circle-check text-primary" title="Verified Streamer" style="font-size: 13px;"></i>
                                            @endif
                                            <span class="badge bg-secondary" style="font-size: 10px; font-weight: 600;">{{ $user->level ?: 'Lv1' }}</span>
                                        </div>
                                        <div class="user-sub-info">
                                            <i class="fa-solid fa-location-dot me-1 text-muted"></i>{{ $user->country ?: 'Global' }} &bull; {{ ucfirst($user->gender ?: 'User') }} &bull; {{ $user->age ? $user->age . ' yrs' : 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="copyable-chip" onclick="copyToClipboard('{{ $user->account_id }}', 'Account ID copied!')" title="Click to copy">
                                    <span>{{ $user->account_id ?: 'ID #' . $user->id }}</span>
                                    <i class="fa-regular fa-copy text-muted" style="font-size: 11px;"></i>
                                </div>
                            </td>
                            <td>
                                <span class="fw-semibold font-monospace" style="font-size: 13px; color: var(--text-primary);">
                                    {{ $user->phone ?: 'Not provided' }}
                                </span>
                            </td>
                            <td>
                                <div class="coin-badge-3d">
                                    <i class="fa-solid fa-coins coin-icon-glow"></i>
                                    <span class="coin-amount-text">{{ number_format($user->coins) }}</span>
                                    <span class="coin-label-text">Coins</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border" style="font-size: 12px; font-weight: 600;">
                                    <i class="fa-solid fa-video text-primary me-1"></i> {{ $user->video_call_rate ?: 100 }} c/min
                                </span>
                            </td>
                            <td>
                                <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="status-pill-modern {{ $user->is_active ? 'active' : 'inactive' }}" style="border: none; cursor: pointer;" title="Click to toggle Online/Offline">
                                        <span class="status-pulsing-dot"></span>
                                        {{ $user->is_active ? 'Online' : 'Offline' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <span class="text-muted" style="font-size: 13px;">
                                    {{ $user->created_at ? $user->created_at->format('M d, Y') : '-' }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div class="d-inline-flex gap-2">
                                    <!-- Adjust Coins Button -->
                                    <button type="button" 
                                            class="btn-ch-gold" 
                                            title="Add / Deduct Coins" 
                                            onclick="openAdjustCoinModal('{{ $user->id }}', '{{ addslashes($user->display_name) }}', '{{ $user->coins }}', '{{ $user->avatar_url }}')">
                                        <i class="fa-solid fa-coins"></i>
                                        <span>Adjust</span>
                                    </button>

                                    <!-- View Details -->
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn-ch-icon" title="View Full Profile & History">
                                        <i class="fa-solid fa-eye text-primary"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="fa-solid fa-user-slash fa-3x mb-3 text-muted" style="opacity: 0.4;"></i>
                                    <h5 class="fw-bold">No Users Found</h5>
                                    <p class="text-muted mb-0">No users match your filter or search query.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="p-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-top: 1px solid var(--card-border-light);">
                <span class="text-muted" style="font-size: 13px;">Showing page {{ $users->currentPage() }} of {{ $users->lastPage() }}</span>
                <div>{{ $users->links() }}</div>
            </div>
        @endif
    </div>
</div>

<!-- Ultra-Premium Modal for Adjusting Coins -->
<div class="modal fade" id="adjustCoinModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-modern-dialog">
        <div class="modal-content modal-modern-content">
            <div class="modal-modern-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-box stat-icon-gold" style="width: 44px; height: 44px; font-size: 18px;">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" style="color: var(--text-primary);">Manual Coins Adjustment</h5>
                        <small class="text-muted">Instantly credit or debit coins from user balance</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="adjustCoinForm" method="POST" action="">
                @csrf
                <div class="modal-modern-body">
                    <!-- User Target Card -->
                    <div class="p-3 rounded-4 mb-3 d-flex justify-content-between align-items-center" style="background: var(--bg-main); border: 1px solid var(--border-color);">
                        <div class="d-flex align-items-center gap-3">
                            <img id="modalUserAvatar" src="" class="rounded-circle" style="width: 44px; height: 44px; object-fit: cover; border: 2px solid var(--ch-gold);">
                            <div>
                                <small class="text-muted d-block" style="font-size: 11px; text-transform: uppercase;">Target Account</small>
                                <strong id="modalUserName" style="font-size: 15px; color: var(--text-primary);"></strong>
                            </div>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block" style="font-size: 11px; text-transform: uppercase;">Current Balance</small>
                            <span class="coin-badge-3d">
                                <i class="fa-solid fa-coins coin-icon-glow"></i>
                                <span id="modalCurrentCoins" class="coin-amount-text">0</span>
                            </span>
                        </div>
                    </div>

                    <!-- 3-Way Segment Selector -->
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Select Operation</label>
                        <div class="coin-action-segmented">
                            <div class="coin-segment-tab active-add" data-action="add">
                                <i class="fa-solid fa-plus-circle"></i> Add Coins
                            </div>
                            <div class="coin-segment-tab" data-action="deduct">
                                <i class="fa-solid fa-minus-circle"></i> Deduct Coins
                            </div>
                            <div class="coin-segment-tab" data-action="set">
                                <i class="fa-solid fa-equals"></i> Set Exact
                            </div>
                        </div>
                        <input type="hidden" name="action" id="coinActionInput" value="add">
                    </div>

                    <!-- Coin Amount Field -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold mb-0" style="font-size: 13px;">Amount of Coins <span class="text-danger">*</span></label>
                            <span id="newBalancePreview" class="text-muted" style="font-size: 12px;"></span>
                        </div>
                        <div class="position-relative">
                            <input type="number" name="amount" id="coinAmountInput" class="form-control form-control-lg rounded-3 fw-bold" style="font-family: var(--font-heading); font-size: 18px; padding-left: 45px;" placeholder="e.g. 1000" min="1" required>
                            <i class="fa-solid fa-coins text-warning position-absolute" style="left: 16px; top: 50%; transform: translateY(-50%); font-size: 18px;"></i>
                        </div>
                        <!-- Quick Add Presets -->
                        <div class="d-flex gap-2 flex-wrap mt-2">
                            <button type="button" class="quick-chip-btn" onclick="addCoinPreset(100)">+100</button>
                            <button type="button" class="quick-chip-btn" onclick="addCoinPreset(500)">+500</button>
                            <button type="button" class="quick-chip-btn" onclick="addCoinPreset(1000)">+1,000</button>
                            <button type="button" class="quick-chip-btn" onclick="addCoinPreset(5000)">+5,000</button>
                            <button type="button" class="quick-chip-btn" onclick="addCoinPreset(10000)">+10,000</button>
                            <button type="button" class="quick-chip-btn" onclick="addCoinPreset(50000)">+50,000</button>
                        </div>
                    </div>

                    <!-- Reason / Transaction Note -->
                    <div class="mb-2">
                        <label class="form-label fw-bold" style="font-size: 13px;">Transaction Reason / Note</label>
                        <input type="text" name="reason" class="form-control rounded-3" placeholder="e.g. Admin bonus / Manual deposit credit / Reward" style="font-size: 14px;">
                    </div>
                </div>
                <div class="modal-modern-footer">
                    <button type="button" class="btn btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-ch-primary">
                        <i class="fa-solid fa-circle-check"></i> Apply Coin Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Container for Clipboard feedback -->
<div class="ch-toast-container" id="toastContainer"></div>

@push('scripts')
<script>
let currentSelectedUserCoins = 0;

function openAdjustCoinModal(userId, userName, currentCoins, avatarUrl) {
    const modalEl = document.getElementById('adjustCoinModal');
    const form = document.getElementById('adjustCoinForm');
    form.action = `/admin/users/${userId}/adjust-coins`;
    
    currentSelectedUserCoins = Number(currentCoins) || 0;
    document.getElementById('modalUserName').innerText = userName;
    document.getElementById('modalCurrentCoins').innerText = currentSelectedUserCoins.toLocaleString();
    document.getElementById('modalUserAvatar').src = avatarUrl || `https://ui-avatars.com/api/?name=${encodeURIComponent(userName)}&background=3b82f6&color=fff`;
    document.getElementById('coinAmountInput').value = '';
    document.getElementById('newBalancePreview').innerText = '';
    
    // Reset to Add tab
    document.querySelectorAll('.coin-segment-tab').forEach(t => t.className = 'coin-segment-tab');
    document.querySelector('.coin-segment-tab[data-action="add"]').className = 'coin-segment-tab active-add';
    document.getElementById('coinActionInput').value = 'add';

    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
}

function addCoinPreset(val) {
    const input = document.getElementById('coinAmountInput');
    input.value = (Number(input.value) || 0) + val;
    updateBalancePreview();
}

function updateBalancePreview() {
    const action = document.getElementById('coinActionInput').value;
    const amount = Number(document.getElementById('coinAmountInput').value) || 0;
    const previewEl = document.getElementById('newBalancePreview');
    
    if (amount <= 0) {
        previewEl.innerText = '';
        return;
    }
    
    let newBal = currentSelectedUserCoins;
    if (action === 'add') newBal += amount;
    else if (action === 'deduct') newBal = Math.max(0, currentSelectedUserCoins - amount);
    else if (action === 'set') newBal = amount;
    
    previewEl.innerHTML = `New Balance: <strong class="text-primary">${newBal.toLocaleString()} Coins</strong>`;
}

document.getElementById('coinAmountInput')?.addEventListener('input', updateBalancePreview);

document.querySelectorAll('.coin-segment-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const action = this.getAttribute('data-action');
        document.getElementById('coinActionInput').value = action;
        document.querySelectorAll('.coin-segment-tab').forEach(t => t.className = 'coin-segment-tab');
        this.className = `coin-segment-tab active-${action}`;
        updateBalancePreview();
    });
});

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
