@extends('layouts.admin')

@section('title', 'Reseller Withdrawal Requests')

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
                <span class="text-primary fw-bold" style="font-size: 13px;">Withdrawals</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-money-bill-wave text-danger"></i>
                <span>Reseller Coin Cash-Out / Withdrawals</span>
            </h1>
            <p class="page-subtitle">Process cash-out payments for resellers who convert their earned coin revenue back to BDT.</p>
        </div>
        <a href="{{ route('admin.resellers.index') }}" class="btn btn-outline-secondary rounded-3 px-3">
            <i class="fa-solid fa-store"></i> All Resellers
        </a>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-yellow">
                <div class="stat-icon-box stat-icon-yellow">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <div class="stat-title">Pending Payouts</div>
                    <div class="stat-value">{{ $stats['pending'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-green">
                <div class="stat-icon-box stat-icon-green">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <div class="stat-title">Approved Payouts</div>
                    <div class="stat-value">{{ $stats['approved'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-purple">
                <div class="stat-icon-box stat-icon-purple">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div>
                    <div class="stat-title">Coins Withdrawn</div>
                    <div class="stat-value">{{ number_format($stats['total_withdrawn_coins']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-blue">
                <div class="stat-icon-box stat-icon-blue">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div>
                    <div class="stat-title">Net Paid BDT</div>
                    <div class="stat-value">৳{{ number_format($stats['total_net_payout_bdt'], 2) }}</div>
                    <small class="text-muted">Earned fee: ৳{{ number_format($stats['total_commission_bdt'], 2) }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Withdrawals Table -->
    <div class="card border-0 rounded-4 shadow-sm" style="background: var(--card-bg-light); border: 1px solid var(--border-color) !important;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID / Date</th>
                        <th>Reseller</th>
                        <th>Payout Account</th>
                        <th>Coins Amount</th>
                        <th>Gross BDT</th>
                        <th>Commission Fee</th>
                        <th>Net Payout BDT</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($withdrawals as $with)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold">#{{ $with->id }}</span>
                                <small class="text-muted d-block" style="font-size: 11px;">{{ $with->created_at->format('M d, Y h:i A') }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $with->reseller->avatar_url ?? '' }}" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;">
                                    <div>
                                        <strong class="d-block text-dark">{{ $with->reseller->name ?? 'Deleted' }}</strong>
                                        <small class="text-muted" style="font-size: 11px;">Balance: {{ number_format($with->reseller->coins_balance ?? 0) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $with->payment_method }}</span>
                                <span class="d-block font-monospace fw-bold text-dark mt-1">{{ $with->account_number }}</span>
                                @if($with->account_name)
                                    <small class="text-muted" style="font-size: 11px;">Name: {{ $with->account_name }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bolder text-warning fs-6">
                                    <i class="fa-solid fa-coins me-1"></i> {{ number_format($with->coins_amount) }}
                                </span>
                            </td>
                            <td>
                                <span>৳{{ number_format($with->gross_bdt, 2) }}</span>
                            </td>
                            <td>
                                <span class="text-danger fw-semibold">-৳{{ number_format($with->commission_amount, 2) }}</span>
                                <small class="text-muted d-block" style="font-size: 10px;">({{ $with->commission_percentage }}%)</small>
                            </td>
                            <td>
                                <strong class="text-success fs-6">৳{{ number_format($with->net_bdt, 2) }}</strong>
                            </td>
                            <td>
                                @if($with->status === 'approved')
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">
                                        <i class="fa-solid fa-circle-check me-1"></i> Paid
                                    </span>
                                @elseif($with->status === 'rejected')
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1">
                                        <i class="fa-solid fa-circle-xmark me-1"></i> Rejected
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning rounded-pill px-3 py-1">
                                        <i class="fa-solid fa-clock me-1"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                @if($with->status === 'pending')
                                    <div class="d-flex justify-content-end gap-1">
                                        <form action="{{ route('admin.resellers.withdrawals.approve', $with->id) }}" method="POST" onsubmit="return confirm('Confirm payout of ৳{{ number_format($with->net_bdt, 2) }} to {{ $with->reseller->name }}?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success rounded-3 px-2 py-1" title="Mark as Paid">
                                                <i class="fa-solid fa-check"></i> Mark Paid
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.resellers.withdrawals.reject', $with->id) }}" method="POST" onsubmit="return confirm('Reject this withdrawal and refund {{ number_format($with->coins_amount) }} coins to {{ $with->reseller->name }}?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-2 py-1" title="Reject & Refund">
                                                <i class="fa-solid fa-xmark"></i> Reject
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <small class="text-muted">Processed</small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-money-bill-wave fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                                <h5>No Withdrawal Requests</h5>
                                <p class="mb-0">When resellers request coin payouts, requests will appear here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($withdrawals->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $withdrawals->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
