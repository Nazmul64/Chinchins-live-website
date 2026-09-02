@extends('layouts.admin')

@section('title', 'User VIP Card Subscriptions')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <a href="{{ route('admin.vip-cards.index') }}" class="text-muted text-decoration-none" style="font-size: 13px;">VIP Cards</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">User Subscriptions</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-users-viewfinder text-primary" style="color: #3b82f6;"></i>
                <span>User VIP Card Subscriptions & Claims</span>
            </h1>
            <p class="page-subtitle">Track active Monthly & Weekly VIP card subscribers, daily check-in claim streaks, and card expiration dates.</p>
        </div>
        <div>
            <a href="{{ route('admin.vip-cards.index') }}" class="btn-ch-primary">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to VIP Cards
            </a>
        </div>
    </div>

    <!-- Subscriptions Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: #ffffff;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-muted small fw-bold text-uppercase">Sub ID</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">User Details</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Card Package</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Price Paid</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Duration & Days</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Claimed Days</th>
                            <th class="py-3 text-muted small fw-bold text-uppercase">Status</th>
                            <th class="pe-4 py-3 text-muted small fw-bold text-uppercase">Expiry Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscriptions as $sub)
                            <tr>
                                <td class="ps-4 fw-bold text-dark">#{{ $sub->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary-subtle text-primary fw-bold" style="width: 36px; height: 36px; font-size: 13px;">
                                            {{ substr($sub->user?->name ?? 'User', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $sub->user?->name ?? 'User #' . $sub->user_id }}</div>
                                            <div class="text-muted small">ID: {{ $sub->user?->account_id ?? 'N/A' }} | {{ $sub->user?->phone ?? $sub->user?->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" style="background: {{ $sub->card?->card_color ?? '#3b82f6' }}; color: #fff; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 6px;">
                                        <i class="fa-solid fa-crown me-1"></i> {{ $sub->card?->name ?? $sub->card_type }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark">৳ {{ number_format($sub->price_paid, 2) }}</span>
                                    <div class="text-muted small">via {{ ucfirst($sub->payment_method) }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">Day {{ $sub->getCurrentDayNumber() }} of {{ $sub->card?->duration_days ?? 30 }}</div>
                                    <div class="text-muted small">{{ $sub->card?->duration_days }} Days Plan</div>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($sub->claimed_days ?? [] as $day)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 10px;">Day {{ $day }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    @if($sub->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">Active</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">Expired</span>
                                    @endif
                                </td>
                                <td class="pe-4">
                                    <div class="fw-semibold text-dark">{{ $sub->expires_at?->format('d M Y, h:i A') }}</div>
                                    @if($sub->is_active)
                                        <div class="text-primary small fw-bold">{{ $sub->expires_at?->diffForHumans() }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-inbox mb-2" style="font-size: 32px;"></i>
                                    <div>No user VIP card subscriptions yet.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($subscriptions->hasPages())
            <div class="card-footer bg-white border-top py-3 px-4">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
