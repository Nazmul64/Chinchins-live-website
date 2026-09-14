@extends('layouts.admin')

@section('title', 'Live Streaming Broadcasts & Rooms')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Live Broadcasts</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-tower-broadcast text-danger"></i>
                <span>Live Streaming & Multi-Guest Rooms</span>
            </h1>
            <p class="page-subtitle">Monitor real-time live video & audio broadcasts, viewer engagements, gift earnings, and terminate active streams.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.live-streams.gift-transactions') }}" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold" style="font-size: 13px;">
                <i class="fa-solid fa-gift me-1"></i> Gift Transactions Log
            </a>
            <a href="{{ route('admin.settings.streaming.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold" style="font-size: 13px;">
                <i class="fa-solid fa-sliders me-1"></i> Streaming Config
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center gap-3" style="background: var(--card-bg, #ffffff);">
                <div class="stat-icon-box" style="width: 50px; height: 50px; background: rgba(239,68,68,0.15); color: #ef4444; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="fa-solid fa-circle-dot animate-pulse"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Currently Live Streams</div>
                    <div class="fw-bold fs-4 text-danger">{{ $activeLivesCount }} Active</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center gap-3" style="background: var(--card-bg, #ffffff);">
                <div class="stat-icon-box" style="width: 50px; height: 50px; background: rgba(245,158,11,0.15); color: #f59e0b; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="fa-solid fa-gem"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Diamonds Earned</div>
                    <div class="fw-bold fs-4 text-warning">{{ number_format($totalDiamondsEarned) }} 💎</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center gap-3" style="background: var(--card-bg, #ffffff);">
                <div class="stat-icon-box" style="width: 50px; height: 50px; background: rgba(16,185,129,0.15); color: #10b981; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="fa-solid fa-hand-holding-heart"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Gift Transactions</div>
                    <div class="fw-bold fs-4 text-success">{{ number_format($totalTransactionsCount) }} Sent</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Stream List -->
    <div class="card border-0 shadow-sm rounded-4 p-4" style="background: var(--card-bg, #ffffff);">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div class="btn-group rounded-pill p-1 bg-light">
                <a href="{{ route('admin.live-streams.index', ['status' => 'all']) }}" class="btn btn-sm rounded-pill px-3 {{ $status === 'all' ? 'btn-primary' : 'btn-light' }}">All Streams</a>
                <a href="{{ route('admin.live-streams.index', ['status' => 'live']) }}" class="btn btn-sm rounded-pill px-3 {{ $status === 'live' ? 'btn-danger' : 'btn-light' }}">
                    <i class="fa-solid fa-circle text-danger me-1" style="font-size: 8px;"></i> Live Now ({{ $activeLivesCount }})
                </a>
                <a href="{{ route('admin.live-streams.index', ['status' => 'ended']) }}" class="btn btn-sm rounded-pill px-3 {{ $status === 'ended' ? 'btn-secondary' : 'btn-light' }}">Ended</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Stream ID</th>
                        <th>Host Details</th>
                        <th>Title & Channel</th>
                        <th>Status</th>
                        <th>Viewers</th>
                        <th>Diamonds</th>
                        <th>Started At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($streams as $stream)
                    <tr>
                        <td>
                            <span class="fw-bold text-muted">#{{ $stream->id }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $stream->host->avatar_url ?? asset('assets/images/default_avatar.png') }}" class="rounded-circle border" style="width: 38px; height: 38px; object-fit: cover;">
                                <div>
                                    <div class="fw-bold text-dark">{{ $stream->host->display_name ?? 'Host' }}</div>
                                    <div class="small text-muted">ID: {{ $stream->host->account_id ?? $stream->host_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-truncate" style="max-width: 200px;">{{ $stream->title ?: 'Live Broadcast' }}</div>
                            <small class="text-muted font-monospace">{{ $stream->channel_name }}</small>
                        </td>
                        <td>
                            @if($stream->status === 'live')
                                <span class="badge bg-danger rounded-pill px-3 py-1 fw-bold">
                                    <i class="fa-solid fa-circle-dot me-1"></i> LIVE
                                </span>
                            @else
                                <span class="badge bg-secondary rounded-pill px-3 py-1">Ended</span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-bold"><i class="fa-solid fa-users text-primary me-1"></i> {{ number_format($stream->viewer_count) }}</span>
                        </td>
                        <td>
                            <span class="fw-bold text-warning"><i class="fa-solid fa-gem me-1"></i> {{ number_format($stream->total_diamonds_earned) }}</span>
                        </td>
                        <td>
                            <small class="text-muted">{{ $stream->started_at ? $stream->started_at->format('M d, Y h:i A') : 'N/A' }}</small>
                        </td>
                        <td class="text-end">
                            @if($stream->status === 'live')
                            <form action="{{ route('admin.live-streams.force-close', $stream->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to terminate this live stream? The host and viewers will be disconnected immediately.');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                    <i class="fa-solid fa-ban me-1"></i> Terminate
                                </button>
                            </form>
                            @else
                            <span class="text-muted small">No action</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-tower-broadcast fs-2 mb-2 d-block opacity-50"></i>
                            No live stream sessions found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $streams->links() }}
        </div>
    </div>
</div>
@endsection
