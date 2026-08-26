@extends('layouts.admin')

@section('title', 'User Management & Coin Balance')

@section('content')
<div class="users-container">
    <!-- Breadcrumb & Top Action -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="page-title mb-1" style="font-size: 24px; font-weight: 700; color: var(--text-primary);">Users & Coin Balance</h1>
            <p class="text-muted mb-0" style="font-size: 14px;">Manage registered users, view profile details, and add/adjust coins manually.</p>
        </div>
    </div>

    <!-- Feedback Alerts -->
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
                <div class="stat-icon-wrapper" style="background: rgba(59, 130, 246, 0.12); color: #3b82f6;">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Total Users</span>
                    <h3 class="stat-number">{{ number_format($stats['total_users']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrapper" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Active Online</span>
                    <h3 class="stat-number">{{ number_format($stats['active_users']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrapper" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Total Coins in System</span>
                    <h3 class="stat-number" style="color: #f59e0b;">{{ number_format($stats['total_coins']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon-wrapper" style="background: rgba(139, 92, 246, 0.12); color: #8b5cf6;">
                    <i class="fa-solid fa-badge-check"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Verified Users</span>
                    <h3 class="stat-number">{{ number_format($stats['verified_users']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card mb-4" style="border: 1px solid var(--border-color); border-radius: var(--radius-md); background: var(--bg-card);">
        <div class="card-body p-3">
            <form action="{{ route('admin.users.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="search-input-group">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="text" name="search" class="form-control-custom" placeholder="Search by name, phone, account ID or email..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="status" class="form-select-custom" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active (Online)</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive (Offline)</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="sort" class="form-select-custom" onchange="this.form.submit()">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest Users</option>
                        <option value="coins_high" {{ request('sort') == 'coins_high' ? 'selected' : '' }}>Most Coins</option>
                        <option value="coins_low" {{ request('sort') == 'coins_low' ? 'selected' : '' }}>Least Coins</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn-primary-custom w-100"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                    @if(request()->hasAny(['search', 'status', 'sort', 'gender']))
                        <a href="{{ route('admin.users.index') }}" class="btn-secondary-custom" title="Reset Filters"><i class="fa-solid fa-rotate-left"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="card" style="border: 1px solid var(--border-color); border-radius: var(--radius-md); background: var(--bg-card); overflow: hidden;">
        <div class="card-header d-flex justify-content-between align-items-center p-3" style="border-bottom: 1px solid var(--border-color); background: transparent;">
            <h5 class="mb-0" style="font-size: 16px; font-weight: 600;">Registered Users List ({{ $users->total() }})</h5>
        </div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>User Profile</th>
                        <th>Account ID</th>
                        <th>Phone Number</th>
                        <th>Coin Balance</th>
                        <th>Call Rate</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="position-relative">
                                        <img src="{{ $user->avatar_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($user->display_name) . '&background=3b82f6&color=fff' }}" 
                                             alt="{{ $user->display_name }}" 
                                             class="rounded-circle" 
                                             style="width: 44px; height: 44px; object-fit: cover; border: 2px solid var(--border-color);">
                                        <span class="status-indicator {{ $user->is_active ? 'online' : 'offline' }}"></span>
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="color: var(--text-primary); font-size: 14px;">
                                            {{ $user->display_name }}
                                            @if($user->is_verified)
                                                <i class="fa-solid fa-circle-check text-primary ms-1" title="Verified" style="font-size: 12px;"></i>
                                            @endif
                                        </div>
                                        <small class="text-muted" style="font-size: 12px;">{{ $user->country ?: 'Global' }} &bull; {{ $user->gender ?: 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge-account-id">{{ $user->account_id ?: 'ID #' . $user->id }}</span>
                            </td>
                            <td>
                                <span style="font-size: 13px; font-weight: 500; color: var(--text-primary);">{{ $user->phone ?: 'No Phone' }}</span>
                            </td>
                            <td>
                                <div class="coin-balance-chip">
                                    <i class="fa-solid fa-coins text-warning"></i>
                                    <span class="fw-bold">{{ number_format($user->coins) }}</span>
                                    <small class="text-muted">Coins</small>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted" style="font-size: 13px;">{{ $user->video_call_rate ?: 100 }} c/min</span>
                            </td>
                            <td>
                                <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="status-badge {{ $user->is_active ? 'badge-active' : 'badge-inactive' }}" title="Click to toggle status">
                                        <span class="status-dot"></span>
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <small class="text-muted">{{ $user->created_at ? $user->created_at->format('M d, Y') : '-' }}</small>
                            </td>
                            <td style="text-align: right;">
                                <div class="d-inline-flex gap-2">
                                    <!-- Adjust Coins Button -->
                                    <button type="button" 
                                            class="btn-action btn-coin" 
                                            title="Add / Deduct Coins" 
                                            onclick="openAdjustCoinModal('{{ $user->id }}', '{{ addslashes($user->display_name) }}', '{{ $user->coins }}')">
                                        <i class="fa-solid fa-plus-minus"></i>
                                        <span>Coins</span>
                                    </button>

                                    <!-- View Details Button -->
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn-action btn-view" title="View Profile">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-user-slash fa-2x mb-3 text-muted"></i>
                                <p class="mb-0">No users found matching the search criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="p-3 d-flex justify-content-end" style="border-top: 1px solid var(--border-color);">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal for Add/Adjust Coins -->
<div class="custom-modal-backdrop" id="adjustCoinModal" style="display: none;">
    <div class="custom-modal-dialog">
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <i class="fa-solid fa-coins text-warning"></i>
                    <span>Adjust User Coins</span>
                </h5>
                <button type="button" class="btn-close-modal" onclick="closeAdjustCoinModal()">&times;</button>
            </div>
            <form id="adjustCoinForm" method="POST" action="">
                @csrf
                <div class="custom-modal-body">
                    <div class="user-summary-card mb-3 p-3" style="background: var(--bg-main); border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">Target User</small>
                                <strong id="modalUserName" style="font-size: 15px; color: var(--text-primary);"></strong>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">Current Balance</small>
                                <span class="badge bg-warning text-dark px-2 py-1" style="font-weight: 700;">
                                    <i class="fa-solid fa-coins me-1"></i> <span id="modalCurrentCoins">0</span> Coins
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom">Action Type</label>
                        <div class="action-type-selector">
                            <label class="action-type-pill active" data-action="add">
                                <input type="radio" name="action" value="add" checked style="display: none;">
                                <i class="fa-solid fa-plus text-success"></i> Add Coins
                            </label>
                            <label class="action-type-pill" data-action="deduct">
                                <input type="radio" name="action" value="deduct" style="display: none;">
                                <i class="fa-solid fa-minus text-danger"></i> Deduct Coins
                            </label>
                            <label class="action-type-pill" data-action="set">
                                <input type="radio" name="action" value="set" style="display: none;">
                                <i class="fa-solid fa-equals text-primary"></i> Set Exact
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom">Amount of Coins <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="coinAmountInput" class="form-control-custom" placeholder="e.g. 500" min="1" required>
                        <div class="quick-amounts mt-2 d-flex gap-2 flex-wrap">
                            <button type="button" class="btn-chip" onclick="setCoinValue(100)">+100</button>
                            <button type="button" class="btn-chip" onclick="setCoinValue(500)">+500</button>
                            <button type="button" class="btn-chip" onclick="setCoinValue(1000)">+1,000</button>
                            <button type="button" class="btn-chip" onclick="setCoinValue(5000)">+5,000</button>
                            <button type="button" class="btn-chip" onclick="setCoinValue(10000)">+10,000</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom">Reason / Transaction Note</label>
                        <input type="text" name="reason" class="form-control-custom" placeholder="e.g. Bonus reward / Special gift / Manual deposit">
                    </div>
                </div>
                <div class="custom-modal-footer">
                    <button type="button" class="btn-secondary-custom" onclick="closeAdjustCoinModal()">Cancel</button>
                    <button type="submit" class="btn-primary-custom"><i class="fa-solid fa-check me-1"></i> Apply Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Modern styling for Admin User Management */
.stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: var(--shadow-sm);
}
.stat-icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.stat-label {
    font-size: 13px;
    color: var(--text-secondary);
    display: block;
    font-weight: 500;
}
.stat-number {
    font-size: 22px;
    font-weight: 700;
    margin: 0;
    color: var(--text-primary);
}
.form-control-custom, .form-select-custom {
    width: 100%;
    padding: 9px 14px;
    font-size: 14px;
    background: var(--bg-main);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    color: var(--text-primary);
    outline: none;
    transition: var(--transition);
}
.form-control-custom:focus, .form-select-custom:focus {
    border-color: var(--primary);
    background: var(--bg-card);
}
.search-input-group {
    position: relative;
}
.search-input-group .search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
}
.search-input-group .form-control-custom {
    padding-left: 38px;
}
.btn-primary-custom {
    background: var(--primary);
    color: #fff;
    border: none;
    padding: 9px 18px;
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}
.btn-primary-custom:hover {
    background: var(--primary-dark);
}
.btn-secondary-custom {
    background: var(--bg-main);
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
    padding: 9px 16px;
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}
.btn-secondary-custom:hover {
    background: var(--border-color);
    color: var(--text-primary);
}
.table-custom {
    width: 100%;
    border-collapse: collapse;
}
.table-custom th {
    padding: 12px 16px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-secondary);
    background: var(--bg-main);
    border-bottom: 1px solid var(--border-color);
}
.table-custom td {
    padding: 14px 16px;
    vertical-align: middle;
    border-bottom: 1px solid var(--border-color);
    font-size: 14px;
}
.table-custom tbody tr:hover {
    background: var(--bg-main);
}
.status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    position: absolute;
    bottom: 0;
    right: 0;
    border: 2px solid var(--bg-card);
}
.status-indicator.online { background: #10b981; }
.status-indicator.offline { background: #94a3b8; }
.badge-account-id {
    font-family: monospace;
    font-weight: 600;
    font-size: 13px;
    background: var(--primary-light);
    color: var(--primary);
    padding: 4px 8px;
    border-radius: 6px;
}
.coin-balance-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(245, 158, 11, 0.12);
    border: 1px solid rgba(245, 158, 11, 0.3);
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 13px;
}
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    border: none;
    cursor: pointer;
}
.status-badge.badge-active {
    background: var(--success-light);
    color: var(--success);
}
.status-badge.badge-inactive {
    background: var(--danger-light);
    color: var(--danger);
}
.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}
.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    border: 1px solid var(--border-color);
    background: var(--bg-card);
    color: var(--text-primary);
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
}
.btn-action:hover {
    background: var(--bg-main);
}
.btn-action.btn-coin {
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
    border-color: rgba(245, 158, 11, 0.3);
}
.btn-action.btn-coin:hover {
    background: #d97706;
    color: #fff;
}
.btn-action.btn-view {
    color: var(--primary);
}
.btn-action.btn-view:hover {
    background: var(--primary);
    color: #fff;
}
.custom-alert {
    padding: 12px 16px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    font-weight: 500;
}
.custom-alert.alert-success {
    background: var(--success-light);
    color: var(--success);
    border: 1px solid rgba(16, 185, 129, 0.3);
}
.custom-alert.alert-danger {
    background: var(--danger-light);
    color: var(--danger);
    border: 1px solid rgba(239, 68, 68, 0.3);
}

/* Modal Styles */
.custom-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
}
.custom-modal-dialog {
    width: 100%;
    max-width: 500px;
    margin: 20px;
}
.custom-modal-content {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}
.custom-modal-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.btn-close-modal {
    background: none;
    border: none;
    font-size: 24px;
    color: var(--text-muted);
    cursor: pointer;
    line-height: 1;
}
.custom-modal-body {
    padding: 20px;
}
.custom-modal-footer {
    padding: 14px 20px;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    background: var(--bg-main);
}
.action-type-selector {
    display: flex;
    gap: 8px;
}
.action-type-pill {
    flex: 1;
    text-align: center;
    padding: 8px 12px;
    font-size: 13px;
    font-weight: 600;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    cursor: pointer;
    background: var(--bg-main);
    color: var(--text-secondary);
    transition: var(--transition);
}
.action-type-pill.active {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}
.action-type-pill.active i {
    color: #fff !important;
}
.form-label-custom {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 6px;
}
.btn-chip {
    background: var(--bg-main);
    border: 1px solid var(--border-color);
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-secondary);
    cursor: pointer;
    transition: var(--transition);
}
.btn-chip:hover {
    background: var(--primary-light);
    color: var(--primary);
    border-color: var(--primary);
}
</style>
@endpush

@push('scripts')
<script>
function openAdjustCoinModal(userId, userName, currentCoins) {
    const modal = document.getElementById('adjustCoinModal');
    const form = document.getElementById('adjustCoinForm');
    form.action = `/admin/users/${userId}/adjust-coins`;
    document.getElementById('modalUserName').innerText = userName;
    document.getElementById('modalCurrentCoins').innerText = Number(currentCoins).toLocaleString();
    document.getElementById('coinAmountInput').value = '';
    modal.style.display = 'flex';
}

function closeAdjustCoinModal() {
    document.getElementById('adjustCoinModal').style.display = 'none';
}

function setCoinValue(val) {
    const input = document.getElementById('coinAmountInput');
    input.value = (Number(input.value) || 0) + val;
}

// Action pills selector
document.querySelectorAll('.action-type-pill').forEach(pill => {
    pill.addEventListener('click', function() {
        document.querySelectorAll('.action-type-pill').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        const radio = this.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
    });
});

// Close modal on backdrop click
window.addEventListener('click', function(e) {
    const modal = document.getElementById('adjustCoinModal');
    if (e.target === modal) {
        closeAdjustCoinModal();
    }
});
</script>
@endpush
@endsection
