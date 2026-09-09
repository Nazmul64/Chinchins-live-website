@extends('layouts.reseller')

@section('title', 'Refill Stock History')

@section('content')
<div class="card border-0 rounded-4 shadow-sm" style="background: #fff; border: 1px solid #e2e8f0 !important;">
    <div class="card-header bg-transparent border-0 p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Stock Refill History</h4>
            <p class="text-muted mb-0" style="font-size: 13px;">View your coin deposit requests and Admin approval statuses</p>
        </div>
        <a href="{{ route('reseller.dashboard') }}" class="btn btn-warning fw-bold rounded-3 px-3">
            <i class="fa-solid fa-circle-plus me-1"></i> New Refill Request
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">ID / Date</th>
                    <th>Payment Method</th>
                    <th>Sender Number</th>
                    <th>Amount BDT</th>
                    <th>Coins Requested</th>
                    <th>TrxID</th>
                    <th class="pe-4 text-end">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deposits as $dep)
                    <tr>
                        <td class="ps-4">
                            <strong>#{{ $dep->id }}</strong>
                            <small class="text-muted d-block" style="font-size: 11px;">{{ $dep->created_at->format('M d, Y h:i A') }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $dep->payment_method }}</span>
                        </td>
                        <td>
                            <span class="font-monospace">{{ $dep->sender_number }}</span>
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
                            <span class="font-monospace text-muted">{{ $dep->transaction_id }}</span>
                        </td>
                        <td class="pe-4 text-end">
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
                                    <i class="fa-solid fa-clock me-1"></i> Pending Review
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-circle-down fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                            <h5>No Deposit Requests Found</h5>
                            <p class="mb-0">Request coin stock refill on the dashboard.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($deposits->hasPages())
        <div class="card-footer bg-transparent border-0 p-3 d-flex justify-content-center">
            {{ $deposits->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
