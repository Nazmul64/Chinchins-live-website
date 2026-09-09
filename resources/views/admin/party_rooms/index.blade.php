@extends('layouts.admin')

@section('title', 'Party Rooms Management')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Party Rooms</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-microphone-lines text-primary"></i>
                <span>Voice & Video Party Rooms 🎉</span>
            </h1>
            <p class="page-subtitle">Monitor live multi-guest audio stages, video grids, seat occupancies, guest billing, and 50/50 revenue split.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.party-rooms.settings') }}" class="btn btn-outline-primary" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-sliders text-primary me-1"></i> Room & Revenue Settings
            </a>
            <a href="{{ route('admin.transactions.index') }}" class="btn-ch-primary">
                <i class="fa-solid fa-coins"></i> Coin Ledger
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert" style="border-radius: 12px;">
            <i class="fa-solid fa-circle-check fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Metric Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #10b981;">
                <div class="stat-icon-box" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                    <i class="fa-solid fa-broadcast-tower"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Active Party Rooms</span>
                    <h3 class="stat-count-value" style="color: #059669;">{{ number_format($totalActiveRooms) }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i class="fa-solid fa-circle text-success" style="font-size: 8px;"></i> Live Now
                    </span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #8b5cf6;">
                <div class="stat-icon-box" style="background: rgba(139, 92, 246, 0.15); color: #8b5cf6;">
                    <i class="fa-solid fa-microphone"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Voice vs Video</span>
                    <h3 class="stat-count-value" style="color: #7c3aed;">
                        {{ $totalVoiceRooms }} <small style="font-size: 13px; color: #64748b;">Voice</small> / {{ $totalVideoRooms }} <small style="font-size: 13px; color: #64748b;">Video</small>
                    </h3>
                    <span class="stat-badge-chip" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                        <i class="fa-solid fa-layer-group"></i> Room Types
                    </span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #f59e0b;">
                <div class="stat-icon-box" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Host Earned Coins</span>
                    <h3 class="stat-count-value" style="color: #d97706;">{{ number_format($totalHostCoins) }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(245, 158, 11, 0.1); color: #d97706;">
                        <i class="fa-solid fa-coins"></i> 50% Host Split
                    </span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #3b82f6;">
                <div class="stat-icon-box" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Admin Platform Revenue</span>
                    <h3 class="stat-count-value" style="color: #2563eb;">{{ number_format($totalAdminCoins) }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                        <i class="fa-solid fa-wallet"></i> 50% Admin Split
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-body p-3">
            <form action="{{ route('admin.party-rooms.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px;">
                            <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search Room ID, Title, Host Name..." value="{{ request('search') }}" style="border-radius: 0 10px 10px 0;">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <select name="room_type" class="form-select" style="border-radius: 10px;">
                        <option value="">All Room Types</option>
                        <option value="voice" {{ request('room_type') == 'voice' ? 'selected' : '' }}>🎙️ Voice Party (Audio Stage)</option>
                        <option value="video" {{ request('room_type') == 'video' ? 'selected' : '' }}>📹 Video Party (Video Grid)</option>
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <select name="status" class="form-select" style="border-radius: 10px;">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>🟢 Live Active</option>
                        <option value="ended" {{ request('status') == 'ended' ? 'selected' : '' }}>⚪ Ended / Closed</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100" style="border-radius: 10px; font-weight: 600;">
                        <i class="fa-solid fa-filter me-1"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'room_type', 'status']))
                        <a href="{{ route('admin.party-rooms.index') }}" class="btn btn-light" style="border-radius: 10px;">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table of Party Rooms -->
    <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: #f8fafc; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;">
                    <tr>
                        <th class="ps-4">Room Info</th>
                        <th>Host</th>
                        <th>Type & Topic</th>
                        <th>Seats / Online</th>
                        <th>Rate & Split</th>
                        <th>Earnings (Host / Admin)</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody style="font-size: 13px;">
                    @forelse($rooms as $room)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="position: relative; width: 44px; height: 44px; border-radius: 12px; overflow: hidden; background: #e2e8f0; flex-shrink: 0;">
                                        @if($room->room_cover_url)
                                            <img src="{{ $room->room_cover_url }}" alt="Cover" style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                                <i class="fa-solid fa-shapes"></i>
                                            </div>
                                        @endif
                                        @if($room->status === 'active')
                                            <span style="position: absolute; top: 3px; right: 3px; width: 8px; height: 8px; border-radius: 50%; background: #10b981; box-shadow: 0 0 6px #10b981;"></span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $room->room_title }}</div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            <code>#{{ $room->room_id }}</code> &bull; {{ $room->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                @if($room->host)
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $room->host->avatar_url }}" alt="Avatar" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 1.5px solid #e2e8f0;">
                                        <div>
                                            <a href="{{ route('admin.users.show', $room->host->id) }}" class="fw-semibold text-dark text-decoration-none">
                                                {{ $room->host->display_name ?? $room->host->name }}
                                            </a>
                                            <div class="text-muted" style="font-size: 11px;">ID: {{ $room->host->account_id }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">Unknown Host</span>
                                @endif
                            </td>

                            <td>
                                <div class="d-flex flex-column gap-1">
                                    @if($room->room_type === 'video')
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1" style="width: fit-content; font-size: 11px;">
                                            <i class="fa-solid fa-video me-1"></i> Video Party
                                        </span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1" style="width: fit-content; font-size: 11px;">
                                            <i class="fa-solid fa-microphone me-1"></i> Voice Party
                                        </span>
                                    @endif
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2" style="width: fit-content; font-size: 10px;">
                                        🏷️ {{ $room->topic_tag }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                <div>
                                    <span class="fw-bold text-dark">{{ $room->occupied_seats_count }}</span> / {{ $room->max_seats }} <span class="text-muted">Seats</span>
                                </div>
                                <div class="text-muted" style="font-size: 11px;">
                                    <i class="fa-solid fa-eye text-primary"></i> {{ $room->online_members_count }} audience
                                </div>
                            </td>

                            <td>
                                <div class="fw-bold text-warning" style="font-size: 13px;">
                                    {{ $room->coin_rate_per_minute }} <span style="font-size: 11px; font-weight: normal; color: #64748b;">coins/min</span>
                                </div>
                                <div class="text-muted" style="font-size: 11px;">
                                    Split: {{ (int)$room->host_commission_percentage }}% Host / {{ (int)$room->admin_commission_percentage }}% Admin
                                </div>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-success fw-semibold" style="font-size: 12px;">
                                        <i class="fa-solid fa-user-check me-1"></i> Host: {{ number_format($room->total_earned_coins) }}
                                    </span>
                                    <span class="text-primary fw-semibold" style="font-size: 12px;">
                                        <i class="fa-solid fa-shield me-1"></i> Admin: {{ number_format($room->total_admin_earned_coins) }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                @if($room->status === 'active')
                                    <span class="badge bg-success rounded-pill px-2 py-1">
                                        <i class="fa-solid fa-circle-dot me-1"></i> Live
                                    </span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-2 py-1">
                                        Ended
                                    </span>
                                @endif
                            </td>

                            <td class="text-end pe-4">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <a href="{{ route('admin.party-rooms.show', $room->id) }}" class="btn btn-sm btn-outline-primary" title="View Room Details" style="border-radius: 8px;">
                                        <i class="fa-solid fa-eye"></i> Monitor
                                    </a>

                                    @if($room->status === 'active')
                                        <form action="{{ route('admin.party-rooms.force-close', $room->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to force-end this party room?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Force Close Room" style="border-radius: 8px;">
                                                <i class="fa-solid fa-power-off"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-microphone-slash fa-3x mb-3 text-secondary"></i>
                                <h5>No Party Rooms Found</h5>
                                <p style="font-size: 13px;">No party rooms match the selected filters or search query.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rooms->hasPages())
            <div class="card-footer bg-white border-0 py-3 d-flex justify-content-end">
                {{ $rooms->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
