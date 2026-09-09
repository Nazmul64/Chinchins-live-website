@extends('layouts.reseller')

@section('title', 'My Cash-Out History')

@section('content')
<div class="card border-0 rounded-4 shadow-sm" style="background: #fff; border: 1px solid #e2e8f0 !important;">
    <div class="card-header bg-transparent border-0 p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Coin Cash-Out / Withdrawals</h4>
            <p class="text-muted mb-0" style="font-size: 13px;">View your coin cashout requests and payout statuses</p>
        </div>
        <a href="{{ route('reseller.dashboard') }}" class="btn btn-primary rounded-3 px-3">
            <i class="fa-solid fa-hand-holding-dollar me-1"></i> Request Cash-Out
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">ID / Date</th>
                    <th>Payout Account</th>
                    <th>Coins Withdrawn</th>
                    <th>Gross BDT</th>
                    <th>Fee Deducted</th>
                    <th>Net Payout BDT</th>
                    <th class="pe-4 text-end">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($withdrawals as $with)
                    <tr>
                        <td class="ps-4">
                            <strong>#{{ $with->id }}</strong>
                            <small class="text-muted d-block" style="font-size: 11px;">{{ $with->created_at->format('M d, Y h:i A') }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $with->payment_method }}</span>
                            <span class="d-block font-monospace fw-bold text-dark mt-1">{{ $with->account_number }}</span>
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
                            <span class="text-danger">-৳{{ number_format($with->commission_amount, 2) }}</span>
                            <small class="text-muted d-block" style="font-size: 10px;">({{ $with->commission_percentage }}%)</small>
                        </td>
                        <td>
                            <strong class="text-success fs-6">৳{{ number_format($with->net_bdt, 2) }}</strong>
                        </td>
                        <td class="pe-4 text-end">
                            @if($with->status === 'approved')
                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">
                                    <i class="fa-solid fa-circle-check me-1"></i> Paid
                                </span>
                            @elseif($with->status === 'rejected')
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1">
                                    <i class="fa-solid fa-circle-xmark me-1"></i> Rejected & Refunded
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning rounded-pill px-3 py-1">
                                    <i class="fa-solid fa-clock me-1"></i> Pending Payout
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-hand-holding-dollar fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                            <h5>No Withdrawal Requests Found</h5>
                            <p class="mb-0">Request coin cash-out on the dashboard.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($withdrawals->hasPages())
        <div class="card-footer bg-transparent border-0 p-3 d-flex justify-content-center">
            {{ $withdrawals->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
