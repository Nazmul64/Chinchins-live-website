@extends('layouts.reseller')

@section('title', 'My Transfer History')

@section('content')
<div class="card border-0 rounded-4 shadow-sm" style="background: #fff; border: 1px solid #e2e8f0 !important;">
    <div class="card-header bg-transparent border-0 p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Coin Transfer Ledger</h4>
            <p class="text-muted mb-0" style="font-size: 13px;">Complete log of coin recharges delivered to user accounts</p>
        </div>
        <a href="{{ route('reseller.dashboard') }}" class="btn btn-primary rounded-3 px-3">
            <i class="fa-solid fa-paper-plane me-1"></i> New Transfer
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Trx ID / Date</th>
                    <th>User</th>
                    <th>Account ID</th>
                    <th>Coins Sent</th>
                    <th>Amount BDT</th>
                    <th>Payment Method</th>
                    <th>Transaction ID</th>
                    <th class="pe-4 text-end">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transfers as $tr)
                    <tr>
                        <td class="ps-4">
                            <strong>#{{ $tr->id }}</strong>
                            <small class="text-muted d-block" style="font-size: 11px;">{{ $tr->created_at->format('M d, Y h:i A') }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $tr->user->avatar_url ?? '' }}" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;">
                                <span>{{ $tr->user->name ?? 'User #' . $tr->user_id }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-primary border font-monospace">{{ $tr->target_account_id }}</span>
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
                            <span class="badge bg-light text-dark border">{{ $tr->payment_method ?: 'Direct' }}</span>
                        </td>
                        <td>
                            <span class="font-monospace text-muted">{{ $tr->transaction_id ?: '—' }}</span>
                        </td>
                        <td class="pe-4 text-end">
                            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">
                                <i class="fa-solid fa-circle-check me-1"></i> Completed
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-money-bill-transfer fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                            <h5>No Transfers Found</h5>
                            <p class="mb-0">Go to dashboard to send coins to users.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transfers->hasPages())
        <div class="card-footer bg-transparent border-0 p-3 d-flex justify-content-center">
            {{ $transfers->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
