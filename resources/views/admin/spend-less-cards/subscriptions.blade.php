@extends('layouts.admin')

@section('title', 'User Card Subscriptions (Spend Less, Get More)')

@section('content')
<div class="container-fluid px-0">
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <a href="{{ route('admin.spend-less-cards.index') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Spend Less Cards</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">User Subscriptions</span>
            </div>
            <h1 class="page-title mb-1">
                <i class="fa-solid fa-users-viewfinder text-primary"></i>
                <span>User Monthly & Weekly Card Subscriptions</span>
            </h1>
            <p class="page-subtitle mb-0">Monitor active user card subscriptions, daily check-in claims, and expiration logs.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.spend-less-cards.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Cards
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: #ffffff; border: 1px solid rgba(0,0,0,0.08) !important;">
        <div class="card-body p-3">
            <form action="{{ route('admin.spend-less-cards.subscriptions') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold text-muted mb-1">Subscription Status</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold text-muted mb-1">Card Type</label>
                    <select name="card_type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Cards</option>
                        <option value="new_user_weekly" {{ request('card_type') === 'new_user_weekly' ? 'selected' : '' }}>New User Weekly</option>
                        <option value="super_monthly" {{ request('card_type') === 'super_monthly' ? 'selected' : '' }}>Super Monthly</option>
                        <option value="luxury_monthly" {{ request('card_type') === 'luxury_monthly' ? 'selected' : '' }}>Luxury Monthly</option>
                        <option value="super_weekly" {{ request('card_type') === 'super_weekly' ? 'selected' : '' }}>Super Weekly</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 d-flex align-items-end">
                    <a href="{{ route('admin.spend-less-cards.subscriptions') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fa-solid fa-rotate-left me-1"></i> Reset Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: #ffffff; border: 1px solid rgba(0,0,0,0.08) !important;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th># ID</th>
                        <th>User</th>
                        <th>Card Package</th>
                        <th>Status</th>
                        <th>Started At</th>
                        <th>Expires At</th>
                        <th>Days Claimed</th>
                        <th>Last Claimed</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                        <tr>
                            <td class="fw-bold text-muted">#{{ $sub->id }}</td>
                            <td>
                                @if($sub->user)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-light fw-bold text-primary" style="width: 32px; height: 32px; font-size: 11px;">
                                            {{ substr($sub->user->nickname ?? $sub->user->name ?? 'U', 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $sub->user->nickname ?? $sub->user->name }}</div>
                                            <div class="text-muted" style="font-size: 11px;">ID: {{ $sub->user->account_id ?? $sub->user->id }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">Unknown User</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge" style="background: {{ $sub->card?->card_color ?? '#EC4899' }}; color: #fff;">
                                    {{ $sub->card?->name ?? ucfirst(str_replace('_', ' ', $sub->card_type)) }}
                                </span>
                            </td>
                            <td>
                                @if($sub->is_active)
                                    <span class="badge bg-success rounded-pill px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> Active</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-2 py-1">Expired</span>
                                @endif
                            </td>
                            <td>{{ $sub->started_at ? $sub->started_at->format('M d, Y H:i') : 'N/A' }}</td>
                            <td>{{ $sub->expires_at ? $sub->expires_at->format('M d, Y H:i') : 'N/A' }}</td>
                            <td>
                                <span class="fw-bold text-primary">{{ $sub->claimed_days_count ?? count($sub->claimed_days ?? []) }}</span> / {{ $sub->card?->duration_days ?? 7 }} Days
                            </td>
                            <td>
                                @if($sub->last_claimed_at)
                                    <span class="text-dark">{{ $sub->last_claimed_at->diffForHumans() }}</span>
                                @else
                                    <span class="text-muted small">Not claimed yet</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-circle-info fs-4 mb-2"></i>
                                <p class="mb-0">No card subscriptions found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subscriptions->hasPages())
            <div class="card-footer bg-transparent border-top p-3">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
