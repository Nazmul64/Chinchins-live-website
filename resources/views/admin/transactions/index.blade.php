@extends('layouts.admin')

@section('title', 'Coin Transactions Ledger')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Coin Ledger</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-receipt text-purple"></i>
                <span>Coin Transactions Ledger</span>
            </h1>
            <p class="page-subtitle">Real-time audit log of all coin deposits, video call minutes deductions, and admin manual adjustments.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-users text-primary me-1"></i> Users Directory
            </a>
            <a href="{{ route('admin.deposits.index') }}" class="btn-ch-primary">
                <i class="fa-solid fa-money-bill-transfer"></i> Review Deposits
            </a>
        </div>
    </div>

    <!-- 3 Metric Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="premium-stat-card">
                <div class="stat-icon-box stat-icon-blue">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Total Transactions</span>
                    <h3 class="stat-count-value">{{ number_format($stats['total_transactions']) }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                        <i class="fa-solid fa-database"></i> All Operations
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="premium-stat-card">
                <div class="stat-icon-box stat-icon-green">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Total Coins Credited</span>
                    <h3 class="stat-count-value" style="color: #10b981;">+{{ number_format($stats['total_added']) }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i class="fa-solid fa-circle-arrow-down"></i> Deposits & Bonus
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="premium-stat-card">
                <div class="stat-icon-box stat-icon-red">
                    <i class="fa-solid fa-arrow-trend-down"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Total Coins Spent / Deducted</span>
                    <h3 class="stat-count-value" style="color: #ef4444;">-{{ number_format($stats['total_deducted']) }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                        <i class="fa-solid fa-video"></i> Video Calls & Deducts
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="filter-card-wrapper">
        <form action="{{ route('admin.transactions.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-6">
                <div class="search-pill-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" class="search-pill-input" placeholder="Search by user name, phone, description, or reference ID..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-4">
                <select name="type" class="custom-select-pill" onchange="this.form.submit()">
                    <option value="">Filter: All Transaction Types</option>
                    <option value="deposit" {{ request('type') === 'deposit' ? 'selected' : '' }}>Deposit Credit</option>
                    <option value="admin_add" {{ request('type') === 'admin_add' ? 'selected' : '' }}>Admin Manual Add</option>
                    <option value="admin_deduct" {{ request('type') === 'admin_deduct' ? 'selected' : '' }}>Admin Manual Deduct</option>
                    <option value="video_call_spent" {{ request('type') === 'video_call_spent' ? 'selected' : '' }}>Video Call Spent</option>
                </select>
            </div>
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn-ch-primary w-100 justify-content-center">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                @if(request()->hasAny(['search', 'type']))
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="border-radius: 10px; width: 44px;" title="Reset">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Transactions Datatable Card -->
    <div class="premium-table-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0 fw-bold" style="font-size: 16px; color: var(--text-primary);">
                    Coin Ledger Audit Trail
                </h5>
                <span class="badge bg-primary rounded-pill px-2 py-1">{{ $transactions->total() }}</span>
            </div>
            <small class="text-muted">Showing {{ $transactions->firstItem() ?? 0 }}-{{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }}</small>
        </div>
        <div class="table-responsive">
            <table class="premium-datatable">
                <thead>
                    <tr>
                        <th>Tx #</th>
                        <th>User Profile</th>
                        <th>Transaction Type</th>
                        <th>Coins Changed</th>
                        <th>Balance After</th>
                        <th>Description / Reference</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td>
                                <strong class="font-monospace text-muted" style="font-size: 13px;">#{{ $tx->id }}</strong>
                            </td>
                            <td>
                                @if($tx->user)
                                    <div class="user-avatar-group">
                                        <img src="{{ $tx->user->avatar_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($tx->user->display_name) . '&background=3b82f6&color=fff' }}" 
                                             alt="" 
                                             class="user-avatar-img" style="width: 38px; height: 38px;">
                                        <div>
                                            <a href="{{ route('admin.users.show', $tx->user_id) }}" class="text-decoration-none fw-bold" style="color: var(--text-primary); font-size: 13px;">
                                                {{ $tx->user->display_name }}
                                            </a>
                                            <div class="user-sub-info">
                                                ID: {{ $tx->user->account_id }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted fst-italic">User deleted</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ in_array($tx->type, ['deposit', 'admin_add', 'gift_received']) ? 'bg-success' : 'bg-danger' }} rounded-pill px-3 py-1" style="font-size: 11px; font-weight: 600;">
                                    {{ str_replace('_', ' ', ucfirst($tx->type)) }}
                                </span>
                            </td>
                            <td>
                                <strong style="font-size: 15px; color: {{ $tx->amount >= 0 ? '#10b981' : '#ef4444' }};">
                                    {{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount) }}
                                </strong>
                                <small class="text-muted">Coins</small>
                            </td>
                            <td>
                                <span class="font-monospace fw-bold" style="font-size: 14px;">{{ number_format($tx->balance_after) }}</span>
                            </td>
                            <td>
                                <div style="font-size: 13px; color: var(--text-primary); font-weight: 500;">
                                    {{ $tx->description }}
                                </div>
                                @if($tx->reference_id)
                                    <span class="badge bg-light text-muted border font-monospace mt-1" style="font-size: 11px;">
                                        {{ $tx->reference_id }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted" style="font-size: 13px;">
                                    {{ $tx->created_at->format('M d, Y') }}<br>
                                    <small>{{ $tx->created_at->format('h:i A') }}</small>
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="fa-solid fa-receipt fa-3x mb-3 text-muted" style="opacity: 0.4;"></i>
                                    <h5 class="fw-bold">No Transactions Found</h5>
                                    <p class="text-muted mb-0">No coin ledger entries match your filter criteria.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="p-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-top: 1px solid var(--card-border-light);">
                <span class="text-muted" style="font-size: 13px;">Showing page {{ $transactions->currentPage() }} of {{ $transactions->lastPage() }}</span>
                <div>{{ $transactions->links() }}</div>
            </div>
        @endif
    </div>
</div>
@endsection
