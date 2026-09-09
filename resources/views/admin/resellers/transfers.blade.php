@extends('layouts.admin')

@section('title', 'Reseller Coin Transfers Ledger')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <a href="{{ route('admin.resellers.index') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Resellers</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Transfer Ledger</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-money-bill-transfer text-primary"></i>
                <span>Reseller Coin Transfer History</span>
            </h1>
            <p class="page-subtitle">Real-time ledger of coins recharged from authorized resellers to specific user account IDs.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.resellers.index') }}" class="btn btn-outline-secondary rounded-3 px-3">
                <i class="fa-solid fa-store"></i> All Resellers
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="stat-card stat-card-blue">
                <div class="stat-icon-box stat-icon-blue">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div>
                    <div class="stat-title">Total Transfers</div>
                    <div class="stat-value">{{ number_format($stats['total_transfers']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stat-card stat-card-purple">
                <div class="stat-icon-box stat-icon-purple">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div>
                    <div class="stat-title">Total Coins Recharged</div>
                    <div class="stat-value">{{ number_format($stats['total_coins_transferred']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stat-card stat-card-green">
                <div class="stat-icon-box stat-icon-green">
                    <i class="fa-solid fa-bangladeshi-taka-sign"></i>
                </div>
                <div>
                    <div class="stat-title">Total Paid BDT</div>
                    <div class="stat-value">৳{{ number_format($stats['total_bdt_transferred'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: var(--card-bg-light); border: 1px solid var(--border-color) !important;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.resellers.transfers') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search target Account ID, TrxID, User..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <select name="reseller_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Resellers</option>
                        @foreach($resellers as $res)
                            <option value="{{ $res->id }}" {{ request('reseller_id') == $res->id ? 'selected' : '' }}>
                                {{ $res->name }} ({{ $res->coins_balance }} coins)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <button type="submit" class="btn btn-primary w-100 rounded-3">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                </div>
                <div class="col-12 col-md-2 text-end">
                    @if(request()->hasAny(['search', 'reseller_id']))
                        <a href="{{ route('admin.resellers.transfers') }}" class="btn btn-light border w-100 rounded-3 text-muted">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Transfers Table -->
    <div class="card border-0 rounded-4 shadow-sm" style="background: var(--card-bg-light); border: 1px solid var(--border-color) !important;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID / Date</th>
                        <th>Reseller</th>
                        <th>Recipient User</th>
                        <th>Coins Transferred</th>
                        <th>Amount BDT</th>
                        <th>Payment Proof / TrxID</th>
                        <th class="pe-4 text-end">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $tr)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold">#{{ $tr->id }}</span>
                                <small class="text-muted d-block" style="font-size: 11px;">{{ $tr->created_at->format('M d, Y h:i A') }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $tr->reseller->avatar_url ?? '' }}" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;">
                                    <div>
                                        <strong class="d-block text-dark">{{ $tr->reseller->name ?? 'Deleted Reseller' }}</strong>
                                        <small class="text-muted" style="font-size: 11px;">{{ $tr->reseller->email ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $tr->user->avatar_url ?? '' }}" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;">
                                    <div>
                                        <strong class="d-block text-dark">{{ $tr->user->name ?? 'User #' . $tr->user_id }}</strong>
                                        <span class="badge bg-light text-primary border font-monospace" style="font-size: 11px;">UID: {{ $tr->target_account_id ?: ($tr->user->account_id ?? $tr->user_id) }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-bolder text-warning fs-6">
                                    <i class="fa-solid fa-coins me-1"></i> {{ number_format($tr->coins) }}
                                </span>
                            </td>
                            <td>
                                @if($tr->amount_bdt)
                                    <strong class="text-success">৳{{ number_format($tr->amount_bdt, 2) }}</strong>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div>
                                    @if($tr->transaction_id)
                                        <span class="badge bg-light text-dark border font-monospace">{{ $tr->transaction_id }}</span>
                                    @endif
                                    @if($tr->payment_method)
                                        <small class="text-muted d-block" style="font-size: 11px;">via {{ $tr->payment_method }}</small>
                                    @endif
                                    @if($tr->screenshot_url)
                                        <a href="{{ $tr->screenshot_url }}" target="_blank" class="btn btn-sm btn-light border py-0 px-2 mt-1" style="font-size: 10px;">
                                            <i class="fa-solid fa-image me-1"></i> View Proof
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="pe-4 text-end">
                                <span class="badge bg-success text-white rounded-pill px-3 py-1">
                                    <i class="fa-solid fa-circle-check me-1"></i> Completed
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-money-bill-transfer fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                                <h5>No Coin Transfers Found</h5>
                                <p class="mb-0">When resellers transfer coins to users, records will appear here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($transfers->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $transfers->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
