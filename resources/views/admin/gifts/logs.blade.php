@extends('layouts.admin')

@section('title', 'Gifts Sent & Received Ledger')

@section('content')
<div class="container-fluid px-0">
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <a href="{{ route('admin.gifts.index') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Gifts & Rewards</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Received Logs</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-list-check text-primary"></i>
                <span>Gifts Transaction & Received Ledger</span>
            </h1>
            <p class="page-subtitle">Complete real-time log of gifts sent by users/fans to streamers & hosts across audio/video calls, chat, and user profiles.</p>
        </div>
        <div>
            <a href="{{ route('admin.gifts.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Gifts
            </a>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--card-bg, #ffffff);">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead class="table-light">
                    <tr style="font-size: 12px; text-transform: uppercase;">
                        <th class="ps-4">Time</th>
                        <th>Gift</th>
                        <th>Sender (Fan)</th>
                        <th>Receiver (Host)</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total Volume</th>
                        <th>Context</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="ps-4 text-muted small">
                                <div>{{ $log->created_at->format('M d, Y') }}</div>
                                <div style="font-size: 11px;">{{ $log->created_at->format('h:i:s A') }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $log->gift ? $log->gift->image_url : asset('assets/images/gifts/gift-box-default.png') }}" class="rounded-3" style="width: 38px; height: 38px; object-fit: contain; background: rgba(0,0,0,0.03);" onerror="this.src='{{ asset('assets/images/gifts/gift-box-default.png') }}'">
                                    <div>
                                        <div class="fw-bold" style="font-size: 13px;">{{ $log->gift ? $log->gift->name : 'Unknown Gift' }}</div>
                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size: 10px;">{{ $log->gift ? $log->gift->category : 'gift' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($log->sender)
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $log->sender->avatar_url ?? asset('assets/images/defaults/avatar-male.png') }}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;" onerror="this.src='{{ asset('assets/images/defaults/avatar-male.png') }}'">
                                        <div>
                                            <div class="fw-semibold" style="font-size: 13px;">{{ $log->sender->display_name }}</div>
                                            <div class="text-muted" style="font-size: 11px;">#{{ $log->sender->account_id ?? $log->sender->id }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge bg-info-subtle text-info rounded-pill px-2 py-1">Admin / System</span>
                                @endif
                            </td>
                            <td>
                                @if($log->user)
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $log->user->avatar_url ?? asset('assets/images/defaults/avatar-female.png') }}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;" onerror="this.src='{{ asset('assets/images/defaults/avatar-female.png') }}'">
                                        <div>
                                            <div class="fw-semibold text-primary" style="font-size: 13px;">{{ $log->user->display_name }}</div>
                                            <div class="text-muted" style="font-size: 11px;">#{{ $log->user->account_id ?? $log->user->id }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">Deleted User</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-pink text-white rounded-pill px-3 py-1" style="background: #ec4899; font-size: 12px; font-weight: 700;">
                                    x{{ $log->quantity }}
                                </span>
                            </td>
                            <td class="fw-semibold" style="font-size: 13px;">
                                💎 {{ \App\Models\Gift::formatCoins($log->coins_per_unit) }}
                            </td>
                            <td>
                                <span class="badge bg-purple text-white rounded-pill px-2 py-1" style="background: #8b5cf6; font-size: 12px;">
                                    💎 {{ \App\Models\Gift::formatCoins($log->total_coins) }}
                                </span>
                                <div class="text-muted" style="font-size: 10px;">({{ number_format($log->total_coins) }} coins)</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border rounded-pill text-uppercase px-2 py-1" style="font-size: 10px;">
                                    {{ $log->context }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No gift transactions recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center my-4">
        {{ $logs->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
