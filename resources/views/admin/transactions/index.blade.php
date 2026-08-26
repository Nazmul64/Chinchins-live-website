@extends('layouts.admin')

@section('title', 'Coin Transaction Ledger')

@section('content')
<div class="transactions-container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="page-title mb-1" style="font-size: 24px; font-weight: 700; color: var(--text-primary);">Coin Transactions Ledger</h1>
            <p class="text-muted mb-0" style="font-size: 14px;">Audit trail of all coin deposits, video call minutes deductions, and admin manual adjustments.</p>
        </div>
    </div>

    <!-- Metric Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="stat-card">
                <div class="stat-icon-wrapper" style="background: rgba(59, 130, 246, 0.12); color: #3b82f6;">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Total Transactions</span>
                    <h3 class="stat-number">{{ number_format($stats['total_transactions']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stat-card">
                <div class="stat-icon-wrapper" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Total Coins Credited</span>
                    <h3 class="stat-number" style="color: #10b981;">+{{ number_format($stats['total_added']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stat-card">
                <div class="stat-icon-wrapper" style="background: rgba(239, 68, 68, 0.12); color: #ef4444;">
                    <i class="fa-solid fa-arrow-trend-down"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Total Coins Spent / Deducted</span>
                    <h3 class="stat-number" style="color: #ef4444;">-{{ number_format($stats['total_deducted']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card mb-4" style="border: 1px solid var(--border-color); border-radius: var(--radius-md); background: var(--bg-card);">
        <div class="card-body p-3">
            <form action="{{ route('admin.transactions.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="search-input-group">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="text" name="search" class="form-control-custom" placeholder="Search by user, description, or reference ID..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <select name="type" class="form-select-custom" onchange="this.form.submit()">
                        <option value="">All Transaction Types</option>
                        <option value="deposit" {{ request('type') === 'deposit' ? 'selected' : '' }}>Deposit Credit</option>
                        <option value="admin_add" {{ request('type') === 'admin_add' ? 'selected' : '' }}>Admin Manual Add</option>
                        <option value="admin_deduct" {{ request('type') === 'admin_deduct' ? 'selected' : '' }}>Admin Manual Deduct</option>
                        <option value="video_call_spent" {{ request('type') === 'video_call_spent' ? 'selected' : '' }}>Video Call Spent</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <button type="submit" class="btn-primary-custom w-100"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card" style="border: 1px solid var(--border-color); border-radius: var(--radius-md); background: var(--bg-card); overflow: hidden;">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Tx #</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Balance After</th>
                        <th>Description / Reference</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td>
                                <strong style="font-size: 13px; color: var(--text-primary);">#{{ $tx->id }}</strong>
                            </td>
                            <td>
                                @if($tx->user)
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $tx->user->avatar_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($tx->user->display_name) . '&background=3b82f6&color=fff' }}" 
                                             alt="" 
                                             class="rounded-circle" 
                                             style="width: 32px; height: 32px; object-fit: cover;">
                                        <div>
                                            <a href="{{ route('admin.users.show', $tx->user_id) }}" class="fw-bold text-decoration-none" style="color: var(--text-primary); font-size: 13px;">
                                                {{ $tx->user->display_name }}
                                            </a>
                                            <small class="text-muted d-block" style="font-size: 11px;">ID: {{ $tx->user->account_id }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">User deleted</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ in_array($tx->type, ['deposit', 'admin_add']) ? 'bg-success' : 'bg-danger' }}">
                                    {{ str_replace('_', ' ', ucfirst($tx->type)) }}
                                </span>
                            </td>
                            <td>
                                <strong style="color: {{ $tx->amount >= 0 ? '#10b981' : '#ef4444' }}; font-size: 14px;">
                                    {{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount) }}
                                </strong>
                                <small class="text-muted">Coins</small>
                            </td>
                            <td>
                                <span class="font-monospace fw-bold">{{ number_format($tx->balance_after) }}</span>
                            </td>
                            <td>
                                <div style="font-size: 13px; color: var(--text-primary);">{{ $tx->description }}</div>
                                @if($tx->reference_id)
                                    <small class="text-muted font-monospace">{{ $tx->reference_id }}</small>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $tx->created_at->format('M d, Y') }}<br>{{ $tx->created_at->format('h:i A') }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-receipt fa-3x mb-3 text-muted"></i>
                                <p class="mb-0">No coin transactions found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="p-3 d-flex justify-content-end" style="border-top: 1px solid var(--border-color);">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
