@extends('layouts.admin')

@section('title', 'Reseller Deposit Requests')

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
                <span class="text-primary fw-bold" style="font-size: 13px;">Deposit Requests</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-hand-holding-dollar text-success"></i>
                <span>Reseller Coin Refill / Deposit Requests</span>
            </h1>
            <p class="page-subtitle">Review payment proofs and approve coin balance top-ups requested by authorized resellers.</p>
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
                    <div class="stat-title">Pending Approvals</div>
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
                    <div class="stat-title">Approved Deposits</div>
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
                    <div class="stat-title">Total Approved Coins</div>
                    <div class="stat-value">{{ number_format($stats['total_approved_coins']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-blue">
                <div class="stat-icon-box stat-icon-blue">
                    <i class="fa-solid fa-bangladeshi-taka-sign"></i>
                </div>
                <div>
                    <div class="stat-title">Total Collected BDT</div>
                    <div class="stat-value">৳{{ number_format($stats['total_approved_bdt'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deposits Table -->
    <div class="card border-0 rounded-4 shadow-sm" style="background: var(--card-bg-light); border: 1px solid var(--border-color) !important;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Request ID / Date</th>
                        <th>Reseller</th>
                        <th>Method & Sender</th>
                        <th>Amount (BDT)</th>
                        <th>Coins Requested</th>
                        <th>TrxID & Proof</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deposits as $dep)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold">#{{ $dep->id }}</span>
                                <small class="text-muted d-block" style="font-size: 11px;">{{ $dep->created_at->format('M d, Y h:i A') }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $dep->reseller->avatar_url ?? '' }}" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;">
                                    <div>
                                        <strong class="d-block text-dark">{{ $dep->reseller->name ?? 'Deleted' }}</strong>
                                        <small class="text-muted" style="font-size: 11px;">Current: {{ number_format($dep->reseller->coins_balance ?? 0) }} coins</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $dep->payment_method }}</span>
                                @if($dep->sender_number)
                                    <small class="text-muted d-block font-monospace mt-1">{{ $dep->sender_number }}</small>
                                @endif
                            </td>
                            <td>
                                <strong class="text-success fs-6">৳{{ number_format($dep->amount_bdt, 2) }}</strong>
                            </td>
                            <td>
                                <span class="fw-bolder text-warning fs-6">
                                    <i class="fa-solid fa-coins me-1"></i> {{ number_format($dep->coins_requested) }}
                                </span>
                            </td>
                            <td>
                                @if($dep->transaction_id)
                                    <span class="badge bg-light text-dark border font-monospace">{{ $dep->transaction_id }}</span>
                                @endif
                                @if($dep->screenshot_url)
                                    <a href="{{ $dep->screenshot_url }}" target="_blank" class="btn btn-sm btn-light border py-0 px-2 mt-1 d-block" style="font-size: 10px; width: fit-content;">
                                        <i class="fa-solid fa-image me-1"></i> View Screenshot
                                    </a>
                                @endif
                            </td>
                            <td>
                                @if($dep->status === 'approved')
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">
                                        <i class="fa-solid fa-circle-check me-1"></i> Approved
                                    </span>
                                @elseif($dep->status === 'rejected')
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
                                @if($dep->status === 'pending')
                                    <div class="d-flex justify-content-end gap-1">
                                        <form action="{{ route('admin.resellers.deposits.approve', $dep->id) }}" method="POST" onsubmit="return confirm('Approve this deposit and credit {{ number_format($dep->coins_requested) }} coins to {{ $dep->reseller->name }}?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success rounded-3 px-2 py-1" title="Approve & Credit Coins">
                                                <i class="fa-solid fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.resellers.deposits.reject', $dep->id) }}" method="POST" onsubmit="return confirm('Reject this deposit request?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-2 py-1" title="Reject Request">
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
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-hand-holding-dollar fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                                <h5>No Deposit Requests</h5>
                                <p class="mb-0">When resellers request coin deposits from admin, they will appear here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($deposits->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $deposits->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
