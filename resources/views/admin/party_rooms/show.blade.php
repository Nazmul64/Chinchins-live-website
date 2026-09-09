@extends('layouts.admin')

@section('title', 'Party Room #' . $room->room_id . ' Monitor')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <a href="{{ route('admin.party-rooms.index') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Party Rooms</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">#{{ $room->room_id }}</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-tower-broadcast text-primary"></i>
                <span>{{ $room->room_title }}</span>
            </h1>
            <p class="page-subtitle">Room ID: <code>#{{ $room->room_id }}</code> &bull; Channel: <code>{{ $room->channel_name }}</code> &bull; Created {{ $room->created_at->format('M d, Y h:i A') }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($room->status === 'active')
                <form action="{{ route('admin.party-rooms.force-close', $room->id) }}" method="POST" onsubmit="return confirm('Force close this room immediately?');">
                    @csrf
                    <button type="submit" class="btn btn-danger" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                        <i class="fa-solid fa-power-off me-1"></i> Force Close Room
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.party-rooms.index') }}" class="btn btn-light" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Rooms
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Stage & Seats (10 Seats Grid) -->
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px; background: #0f172a; color: white;">
                <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge {{ $room->room_type === 'video' ? 'bg-danger' : 'bg-primary' }} px-3 py-1 rounded-pill">
                            {{ $room->room_type === 'video' ? '📹 Multi-Guest Video Grid' : '🎙️ 10-Seat Audio Stage' }}
                        </span>
                        <span class="badge bg-secondary rounded-pill px-2">🏷️ {{ $room->topic_tag }}</span>
                    </div>
                    <div>
                        @if($room->status === 'active')
                            <span class="badge bg-success rounded-pill px-3 py-1">
                                <i class="fa-solid fa-circle-dot me-1"></i> Live Stream Active
                            </span>
                        @else
                            <span class="badge bg-secondary rounded-pill px-3 py-1">Room Ended</span>
                        @endif
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Announcement Banner -->
                    <div class="p-3 mb-4 rounded-3 d-flex align-items-center gap-2" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.12);">
                        <i class="fa-solid fa-bullhorn text-warning fs-5"></i>
                        <div style="font-size: 13px;">{{ $room->announcement ?: 'Welcome to My Live Fun Hangout 🥳✨! Please be respectful.' }}</div>
                    </div>

                    <!-- 10 Seats Layout Grid -->
                    <h6 class="text-white-50 text-uppercase fw-bold mb-3" style="font-size: 12px; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-couch me-1"></i> Room Stage Seats ({{ $room->occupied_seats_count }} / {{ $room->max_seats }} Occupied)
                    </h6>

                    <div class="row g-3">
                        @foreach($room->seats as $seat)
                            <div class="col-6 col-sm-4 col-md-3 col-xl-2dot4">
                                <div class="p-3 rounded-4 text-center position-relative" style="background: {{ $seat->status === 'occupied' ? 'rgba(59, 130, 246, 0.12)' : 'rgba(255, 255, 255, 0.04)' }}; border: 1.5px solid {{ $seat->status === 'occupied' ? '#3b82f6' : 'rgba(255, 255, 255, 0.1)' }}; min-height: 140px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                    
                                    <!-- Seat Index Badge -->
                                    <span class="badge {{ $seat->seat_index === 1 ? 'bg-warning text-dark' : 'bg-dark' }} position-absolute top-0 start-0 m-2 rounded-pill" style="font-size: 10px;">
                                        {{ $seat->seat_index === 1 ? '👑 Host' : 'Seat ' . $seat->seat_index }}
                                    </span>

                                    @if($seat->status === 'occupied' && $seat->user)
                                        <div class="position-relative mb-2 mt-2">
                                            <img src="{{ $seat->user->avatar_url }}" alt="{{ $seat->user->name }}" style="width: 52px; height: 52px; border-radius: 50%; object-fit: cover; border: 2px solid #3b82f6;">
                                            @if($seat->is_muted)
                                                <span class="position-absolute bottom-0 end-0 badge bg-danger rounded-circle p-1" title="Muted">
                                                    <i class="fa-solid fa-microphone-slash" style="font-size: 9px;"></i>
                                                </span>
                                            @else
                                                <span class="position-absolute bottom-0 end-0 badge bg-success rounded-circle p-1" title="Speaking">
                                                    <i class="fa-solid fa-microphone" style="font-size: 9px;"></i>
                                                </span>
                                            @endif
                                        </div>
                                        <div class="fw-bold text-white text-truncate w-100" style="font-size: 12px;">
                                            {{ $seat->user->display_name ?? $seat->user->name }}
                                        </div>
                                        <div class="text-white-50" style="font-size: 10px;">
                                            ID: {{ $seat->user->account_id }}
                                        </div>
                                        <div class="badge bg-primary-subtle text-primary mt-1" style="font-size: 10px;">
                                            {{ number_format($seat->coins_spent) }} coins spent
                                        </div>
                                    @else
                                        <div class="d-flex flex-column align-items-center justify-content-center py-2 text-white-50">
                                            <div style="width: 44px; height: 44px; border-radius: 50%; border: 1.5px dashed rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; margin-bottom: 6px;">
                                                <i class="fa-solid fa-plus text-white-50"></i>
                                            </div>
                                            <span style="font-size: 11px;">Empty Seat</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- In-Room Chat Stream -->
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">
                        <i class="fa-solid fa-comments text-primary me-2"></i> Live Chat & Gifting Stream
                    </h6>
                    <span class="badge bg-light text-dark rounded-pill">{{ count($recentMessages) }} messages</span>
                </div>
                <div class="card-body p-3" style="max-height: 400px; overflow-y: auto; background: #f8fafc; border-radius: 0 0 16px 16px;">
                    @forelse($recentMessages as $msg)
                        <div class="d-flex align-items-start gap-2 mb-3">
                            <img src="{{ $msg->user->avatar_url ?? asset('images/default-avatar.png') }}" alt="User" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                            <div style="flex: 1;">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold text-dark" style="font-size: 12px;">{{ $msg->user->display_name ?? $msg->user->name ?? 'User' }}</span>
                                    <span class="text-muted" style="font-size: 10px;">{{ $msg->created_at->format('h:i:s A') }}</span>
                                    @if($msg->type === 'gift')
                                        <span class="badge bg-warning-subtle text-warning px-2" style="font-size: 10px;">🎁 GIFT</span>
                                    @elseif($msg->type === 'system')
                                        <span class="badge bg-info-subtle text-info px-2" style="font-size: 10px;">📢 SYSTEM</span>
                                    @endif
                                </div>

                                @if($msg->type === 'image')
                                    <div class="mt-1">
                                        <a href="{{ $msg->full_image_url }}" target="_blank">
                                            <img src="{{ $msg->full_image_url }}" alt="Shared image" style="max-width: 180px; max-height: 180px; border-radius: 10px; border: 1px solid #e2e8f0;">
                                        </a>
                                    </div>
                                @elseif($msg->type === 'gift')
                                    <div class="p-2 mt-1 rounded-3 d-flex align-items-center gap-2" style="background: #fffbeb; border: 1px solid #fde68a; width: fit-content;">
                                        @if($msg->gift && $msg->gift->icon_url)
                                            <img src="{{ $msg->gift->icon_url }}" alt="Gift" style="width: 28px; height: 28px; object-fit: contain;">
                                        @endif
                                        <div style="font-size: 12px; color: #92400e;">
                                            {{ $msg->message }} ({{ number_format($msg->coins_amount) }} coins)
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-white p-2 mt-1 rounded-3 border" style="font-size: 13px; width: fit-content; max-width: 85%;">
                                        {{ $msg->message }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted" style="font-size: 13px;">No messages sent in this room yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Host Info & Revenue Breakdown -->
        <div class="col-12 col-xl-4">
            <!-- Host Card -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body p-4 text-center">
                    @if($room->host)
                        <div class="position-relative d-inline-block mb-3">
                            <img src="{{ $room->host->avatar_url }}" alt="Host" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #3b82f6;">
                            <span class="badge bg-warning position-absolute bottom-0 end-0 rounded-circle p-1">
                                <i class="fa-solid fa-crown text-dark" style="font-size: 12px;"></i>
                            </span>
                        </div>
                        <h5 class="fw-bold mb-1">{{ $room->host->display_name ?? $room->host->name }}</h5>
                        <div class="text-muted mb-3" style="font-size: 13px;">Host ID: <code>{{ $room->host->account_id }}</code> &bull; Level {{ $room->host->level }}</div>
                        <a href="{{ route('admin.users.show', $room->host->id) }}" class="btn btn-outline-primary btn-sm w-100" style="border-radius: 10px;">
                            <i class="fa-solid fa-user me-1"></i> View Host Profile
                        </a>
                    @else
                        <p class="text-muted">Host unavailable</p>
                    @endif
                </div>
            </div>

            <!-- Revenue Split Card -->
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-coins text-warning me-2"></i> Revenue & Billing Rules</h6>
                </div>
                <div class="card-body p-4 pt-0">
                    <ul class="list-group list-group-flush" style="font-size: 13px;">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                            <span class="text-muted">Rate Per Minute:</span>
                            <span class="fw-bold text-dark">{{ $room->coin_rate_per_minute }} coins/min</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                            <span class="text-muted">Host Commission (50%):</span>
                            <span class="fw-bold text-success">{{ (int)$room->host_commission_percentage }}% ({{ number_format($room->total_earned_coins) }} coins)</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                            <span class="text-muted">Admin Commission (50%):</span>
                            <span class="fw-bold text-primary">{{ (int)$room->admin_commission_percentage }}% ({{ number_format($room->total_admin_earned_coins) }} coins)</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                            <span class="text-muted">Max Stage Seats:</span>
                            <span class="fw-bold text-dark">{{ $room->max_seats }} seats</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                            <span class="text-muted">Status:</span>
                            <span class="badge {{ $room->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ strtoupper($room->status) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
