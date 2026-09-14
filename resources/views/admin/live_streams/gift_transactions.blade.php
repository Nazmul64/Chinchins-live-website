@extends('layouts.admin')

@section('title', 'Virtual Gift Transactions Audit Log')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <a href="{{ route('admin.live-streams.index') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Live Streams</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Gift Transactions</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-hand-holding-heart text-pink"></i>
                <span>Virtual Gift Transactions Audit Log</span>
            </h1>
            <p class="page-subtitle">Real-time ledger of gifts sent during live streams and video calls with 50/50 revenue split tracking.</p>
        </div>
        <div>
            <a href="{{ route('admin.live-streams.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold" style="font-size: 13px;">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Live Streams
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center gap-3" style="background: var(--card-bg, #ffffff);">
                <div class="stat-icon-box" style="width: 50px; height: 50px; background: rgba(236,72,153,0.15); color: #ec4899; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="fa-solid fa-gift"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Gifts Sent</div>
                    <div class="fw-bold fs-4 text-pink">{{ number_format($totalTxCount) }} Gifts</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center gap-3" style="background: var(--card-bg, #ffffff);">
                <div class="stat-icon-box" style="width: 50px; height: 50px; background: rgba(245,158,11,0.15); color: #f59e0b; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Coins Spent</div>
                    <div class="fw-bold fs-4 text-warning">{{ number_format($totalSpent) }} Coins</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm rounded-4 p-4" style="background: var(--card-bg, #ffffff);">
        <div class="table-responsive">
            <table class="table align-middle table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Tx ID</th>
                        <th>Sender</th>
                        <th>Receiver (Host)</th>
                        <th>Gift Details</th>
                        <th>Coins Spent</th>
                        <th>Host Earned (50%)</th>
                        <th>Stream / Context</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                    <tr>
                        <td>
                            <span class="fw-bold text-muted">#{{ $tx->id }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $tx->sender->avatar_url ?? asset('assets/images/default_avatar.png') }}" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 13px;">{{ $tx->sender->display_name ?? 'User' }}</div>
                                    <div class="small text-muted" style="font-size: 11px;">ID: {{ $tx->sender->account_id ?? $tx->sender_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $tx->receiver->avatar_url ?? asset('assets/images/default_avatar.png') }}" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 13px;">{{ $tx->receiver->display_name ?? 'Host' }}</div>
                                    <div class="small text-muted" style="font-size: 11px;">ID: {{ $tx->receiver->account_id ?? $tx->receiver_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $tx->gift->icon_url ?? $tx->gift->image_url ?? asset('uploads/gifts/diamond_ring_gift.svg') }}" style="width: 30px; height: 30px; object-fit: contain;">
                                <div>
                                    <div class="fw-bold text-pink" style="font-size: 13px;">{{ $tx->gift->name ?? 'Gift' }}</div>
                                    <div class="small text-muted" style="font-size: 11px;">Qty: {{ $tx->quantity ?? 1 }}x</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="fw-bold text-danger">-{{ number_format($tx->coins_spent ?? $tx->total_coins ?? 0) }}</span>
                        </td>
                        <td>
                            <span class="fw-bold text-success">+{{ number_format(round(($tx->coins_spent ?? $tx->total_coins ?? 0) * 0.5)) }}</span>
                        </td>
                        <td>
                            @if($tx->live_room_id || $tx->stream_id)
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1">
                                    <i class="fa-solid fa-tower-broadcast me-1"></i> Live #{{ $tx->live_room_id ?? $tx->stream_id }}
                                </span>
                            @else
                                <span class="badge bg-light text-muted rounded-pill px-2 py-1">Direct / Call</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $tx->created_at ? $tx->created_at->format('M d, Y h:i A') : 'N/A' }}</small>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-gift fs-2 mb-2 d-block opacity-50"></i>
                            No gift transactions found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
