@extends('layouts.admin')

@section('title', 'Call Sessions & Revenue Ledger')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Call Sessions</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-video text-primary"></i>
                <span>Audio & Video Call Sessions</span>
            </h1>
            <p class="page-subtitle">Track real-time audio/video calls, free trial usage, coin billing, host earnings, and platform commission revenue.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.calls.settings') }}" class="btn btn-outline-primary" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-sliders text-primary me-1"></i> Call & Revenue Settings
            </a>
            <a href="{{ route('admin.transactions.index') }}" class="btn-ch-primary">
                <i class="fa-solid fa-coins"></i> Coin Ledger
            </a>
        </div>
    </div>

    <!-- Metric Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #3b82f6;">
                <div class="stat-icon-box stat-icon-blue">
                    <i class="fa-solid fa-phone-volume"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Total Calls / Minutes</span>
                    <h3 class="stat-count-value" style="color: #2563eb;">{{ number_format($stats['total_calls']) }} <small style="font-size: 14px; font-weight: normal; color: #64748b;">({{ number_format($stats['total_minutes']) }} min)</small></h3>
                    <span class="stat-badge-chip" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                        <i class="fa-solid fa-clock"></i> WebRTC Calls
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card">
                <div class="stat-icon-box stat-icon-gold">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Total Coins Billed</span>
                    <h3 class="stat-count-value" style="color: #d97706;">{{ number_format($stats['total_coins_spent']) }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(245, 158, 11, 0.1); color: #d97706;">
                        <i class="fa-solid fa-receipt"></i> Caller Spent
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card">
                <div class="stat-icon-box stat-icon-green">
                    <i class="fa-solid fa-heart"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Host Earnings ({{ $config['host_earning_percent'] }}%)</span>
                    <h3 class="stat-count-value" style="color: #10b981;">{{ number_format($stats['total_host_earnings']) }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i class="fa-solid fa-wallet"></i> Credited to Hosts
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card">
                <div class="stat-icon-box stat-icon-purple">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Platform Revenue ({{ $config['admin_commission_percent'] }}%)</span>
                    <h3 class="stat-count-value" style="color: #8b5cf6;">{{ number_format($stats['total_admin_revenue']) }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                        <i class="fa-solid fa-chart-pie"></i> Admin Profit
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Navigation Tabs & Search -->
    <div class="filter-card-wrapper mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <!-- Filter Tabs -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('admin.calls.index', ['type' => 'all', 'search' => request('search')]) }}" 
                   class="filter-pill-tab {{ $type === 'all' ? 'active' : '' }}">
                    All Calls
                    <span class="filter-pill-badge">{{ $stats['total_calls'] }}</span>
                </a>
                <a href="{{ route('admin.calls.index', ['type' => 'video', 'search' => request('search')]) }}" 
                   class="filter-pill-tab {{ $type === 'video' ? 'active' : '' }}">
                    <i class="fa-solid fa-video text-primary me-1"></i> Video Calls
                </a>
                <a href="{{ route('admin.calls.index', ['type' => 'audio', 'search' => request('search')]) }}" 
                   class="filter-pill-tab {{ $type === 'audio' ? 'active' : '' }}">
                    <i class="fa-solid fa-phone text-success me-1"></i> Audio Calls
                </a>
                <a href="{{ route('admin.calls.index', ['type' => 'free_trial', 'search' => request('search')]) }}" 
                   class="filter-pill-tab {{ $type === 'free_trial' ? 'active' : '' }}">
                    <i class="fa-solid fa-gift text-warning me-1"></i> Free Trials
                    <span class="filter-pill-badge">{{ $stats['free_trials_count'] }}</span>
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('admin.calls.index') }}" method="GET" class="d-flex align-items-center gap-2">
                <input type="hidden" name="type" value="{{ $type }}">
                <div class="search-input-wrapper">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" name="search" class="form-control search-input" 
                           placeholder="Search caller, host, or channel..." 
                           value="{{ request('search') }}">
                    @if(request('search'))
                        <a href="{{ route('admin.calls.index', ['type' => $type]) }}" class="search-clear-btn" title="Clear">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
                <button type="submit" class="btn btn-secondary" style="border-radius: 10px; font-weight: 600; padding: 8px 14px; font-size: 13px;">
                    Search
                </button>
            </form>
        </div>
    </div>

    <!-- Calls Table Card -->
    <div class="premium-table-card">
        <div class="table-responsive">
            <table class="table align-middle ch-datatable mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Caller (User)</th>
                        <th>Host (Receiver)</th>
                        <th>Type & Trial</th>
                        <th>Duration</th>
                        <th>Coins Billed</th>
                        <th>Revenue Split (50/50)</th>
                        <th>Status</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($calls as $item)
                        <tr>
                            <!-- Call ID -->
                            <td>
                                <span class="fw-bold text-muted" style="font-size: 13px;">#{{ $item->id }}</span>
                            </td>

                            <!-- Caller -->
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar-small">
                                        @if($item->caller && $item->caller->avatar_url)
                                            <img src="{{ $item->caller->avatar_url }}" alt="avatar">
                                        @else
                                            <div class="avatar-placeholder">{{ strtoupper(substr($item->caller->name ?? 'C', 0, 1)) }}</div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 13px;">{{ $item->caller->display_name ?? 'Caller' }}</div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            ID: <code>{{ $item->caller->account_id ?? 'N/A' }}</code> &bull; <span class="badge bg-light text-dark">{{ $item->caller->gender ?? 'male' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Receiver / Host -->
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar-small">
                                        @if($item->receiver && $item->receiver->avatar_url)
                                            <img src="{{ $item->receiver->avatar_url }}" alt="avatar">
                                        @else
                                            <div class="avatar-placeholder" style="background: #ec4899;">{{ strtoupper(substr($item->receiver->name ?? 'H', 0, 1)) }}</div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 13px;">{{ $item->receiver->display_name ?? 'Host' }}</div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            ID: <code>{{ $item->receiver->account_id ?? 'N/A' }}</code> &bull; <span class="badge bg-pink-subtle text-danger">Female</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Type & Free Trial -->
                            <td>
                                @if($item->call_type === 'audio')
                                    <span class="badge bg-success-subtle text-success fw-bold" style="font-size: 12px;">
                                        <i class="fa-solid fa-phone me-1"></i> Audio
                                    </span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary fw-bold" style="font-size: 12px;">
                                        <i class="fa-solid fa-video me-1"></i> Video
                                    </span>
                                @endif

                                @if($item->is_free_trial)
                                    <div class="mt-1">
                                        <span class="badge bg-warning-subtle text-warning fw-semibold" style="font-size: 10px;">
                                            <i class="fa-solid fa-gift"></i> Free Trial ({{ $item->free_duration_seconds }}s)
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <!-- Duration -->
                            <td>
                                <div class="fw-bold text-dark" style="font-size: 14px; font-family: monospace;">
                                    <i class="fa-regular fa-clock text-muted me-1"></i> {{ $item->formatted_duration }}
                                </div>
                                <div class="text-muted" style="font-size: 11px;">
                                    Rate: {{ $item->rate_per_minute }} coins/min
                                </div>
                            </td>

                            <!-- Coins Billed -->
                            <td>
                                <div class="fw-bold text-danger" style="font-size: 14px;">
                                    <i class="fa-solid fa-coins text-warning me-1"></i> {{ number_format($item->coins_deducted) }}
                                </div>
                                <div class="text-muted" style="font-size: 11px;">
                                    Billed from Caller
                                </div>
                            </td>

                            <!-- 50/50 Revenue Split -->
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="p-1 px-2 rounded" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2);">
                                        <span class="text-muted" style="font-size: 10px;">Host:</span>
                                        <strong class="text-success" style="font-size: 12px;">+{{ number_format($item->host_earned_coins) }}</strong>
                                    </div>
                                    <div class="p-1 px-2 rounded" style="background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.2);">
                                        <span class="text-muted" style="font-size: 10px;">Admin:</span>
                                        <strong class="text-purple" style="font-size: 12px; color: #8b5cf6;">+{{ number_format($item->admin_revenue_coins) }}</strong>
                                    </div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td>
                                @if($item->status === 'connected')
                                    <span class="badge badge-soft-success">
                                        <span class="status-pulsing-dot me-1"></span> Live Call
                                    </span>
                                @elseif($item->status === 'ended')
                                    <span class="badge badge-soft-secondary">
                                        <i class="fa-solid fa-check me-1"></i> Ended
                                    </span>
                                @elseif($item->status === 'initiated')
                                    <span class="badge badge-soft-info">
                                        <i class="fa-solid fa-phone me-1"></i> Ringing
                                    </span>
                                @else
                                    <span class="badge badge-soft-danger">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Date & Time -->
                            <td>
                                <div class="text-dark" style="font-size: 12px;">
                                    <i class="fa-regular fa-calendar text-muted me-1"></i> {{ $item->created_at->format('M d, Y') }}
                                </div>
                                <div class="text-muted" style="font-size: 11px;">
                                    {{ $item->created_at->format('h:i:s A') }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="py-4">
                                    <i class="fa-solid fa-video-slash text-muted mb-3" style="font-size: 48px; opacity: 0.3;"></i>
                                    <h5 class="fw-bold text-muted">No call sessions recorded yet</h5>
                                    <p class="text-muted mb-0" style="font-size: 13px;">When users make audio/video calls from the app, they will be logged here with duration and revenue sharing.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($calls->hasPages())
            <div class="p-3 border-top">
                {{ $calls->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
