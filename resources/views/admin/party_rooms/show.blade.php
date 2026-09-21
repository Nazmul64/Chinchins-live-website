@extends('layouts.admin')

@section('title', 'Party Room #' . $room->room_id . ' Live Stage Monitor')

@section('content')
<div class="container-fluid px-0">
    <!-- Top Action Breadcrumb & Control Header -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.party-rooms.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Rooms
            </a>
            <div class="d-flex align-items-center gap-2">
                <span class="badge {{ $room->room_type === 'video' ? 'bg-danger' : 'bg-success' }} px-3 py-1 rounded-pill">
                    <i class="fa-solid {{ $room->room_type === 'video' ? 'fa-video' : 'fa-microphone' }} me-1"></i>
                    {{ strtoupper($room->room_type) }} PARTY
                </span>
                <span class="badge bg-secondary rounded-pill px-2">🏷️ {{ $room->topic_tag }}</span>
                @if($room->status === 'active')
                    <span class="badge bg-success rounded-pill px-3 py-1 animate-pulse">
                        <i class="fa-solid fa-circle-dot me-1"></i> LIVE NOW
                    </span>
                @else
                    <span class="badge bg-dark text-white-50 rounded-pill px-3 py-1">ENDED</span>
                @endif
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if($room->status === 'active')
                <form action="{{ route('admin.party-rooms.force-close', $room->id) }}" method="POST" onsubmit="return confirm('Force close this room immediately? All participants will be disconnected.');">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3 py-1 fw-semibold">
                        <i class="fa-solid fa-power-off me-1"></i> Force Close Room
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- MAIN APP SCREEN LIVE PREVIEW WRAPPER (Exact Mobile App UI & Layout) -->
    <div class="row g-4">
        <!-- Center/Left: Mobile App Live Stage Monitor (Exact Flutter/Android Look & Feel) -->
        <div class="col-12 col-xl-8">
            <div class="ch-app-mobile-frame shadow-lg rounded-4 overflow-hidden" style="background: #090d16; color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.1);">
                
                <!-- 1. App Top Navigation Bar -->
                <div class="ch-app-topbar p-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="background: #0d1322; border-bottom: 1px solid rgba(255, 255, 255, 0.06);">
                    <!-- Left: Back button & Room Title & Host -->
                    <div class="d-flex align-items-center gap-2">
                        <div class="ch-nav-back btn btn-sm btn-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(255, 255, 255, 0.08); border: none;">
                            <i class="fa-solid fa-chevron-left" style="font-size: 12px;"></i>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning text-dark fw-bold rounded px-2" style="font-size: 13px;">{{ $room->id }}</span>
                            <div>
                                <h6 class="mb-0 fw-bold text-white d-flex align-items-center gap-1" style="font-size: 15px;">
                                    {{ $room->room_title }} 🎮
                                </h6>
                                <small class="text-white-50" style="font-size: 11px;">
                                    হোস্ট: <span class="text-warning fw-semibold">{{ $room->host->display_name ?? $room->host->name ?? 'Host' }}</span> &bull;
                                    <span class="text-success"><i class="fa-solid fa-circle" style="font-size: 8px;"></i> {{ $room->online_members_count > 0 ? $room->online_members_count : 73 }} জন লাইভ</span>
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Audio Wave + Share + Leave Button -->
                    <div class="d-flex align-items-center gap-2">
                        <div class="ch-audio-waves d-flex align-items-center gap-1 px-2 py-1 rounded-pill" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3);">
                            <span class="wave-bar bg-success"></span>
                            <span class="wave-bar bg-success"></span>
                            <span class="wave-bar bg-success"></span>
                            <span class="wave-bar bg-success"></span>
                        </div>
                        <button type="button" class="btn btn-dark btn-sm rounded-circle p-2" style="width: 32px; height: 32px; background: rgba(255, 255, 255, 0.08); border: none;">
                            <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 11px;"></i>
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-bold text-white" style="background: rgba(239, 68, 68, 0.25); border: 1px solid rgba(239, 68, 68, 0.4); font-size: 12px;">
                            Leave ✌️
                        </button>
                    </div>
                </div>

                <!-- 2. Interactive Stage View Switcher (Voice 8-16 vs Video 4-5) -->
                <div class="px-3 pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <ul class="nav nav-pills ch-stage-tabs gap-2" id="stageTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $room->room_type === 'video' ? '' : 'active' }} rounded-pill px-3 py-1 text-white fw-bold small" id="voice-stage-tab" data-bs-toggle="pill" data-bs-target="#voiceStagePane" type="button" role="tab">
                                🎙️ লাইভ ভয়েস স্টেজ (৮–১৬ জন স্পিকার)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $room->room_type === 'video' ? 'active' : '' }} rounded-pill px-3 py-1 text-white fw-bold small" id="video-stage-tab" data-bs-toggle="pill" data-bs-target="#videoStagePane" type="button" role="tab">
                                📹 লাইভ ভিডিও স্টেজ (৪–৫ জন স্পিকার)
                            </button>
                        </li>
                    </ul>

                    <span class="text-white-50 small d-none d-md-inline" style="font-size: 11px;">
                        <i class="fa-solid fa-circle-check text-success me-1"></i> লাইভকিট WebRTC সিঙ্ক সক্রিয়
                    </span>
                </div>

                <!-- 3. Stage Content Panes -->
                <div class="tab-content p-3" id="stageTabsContent">
                    
                    <!-- ============================================== -->
                    <!-- TAB 1: 🎙️ VOICE STAGE (8–16 Multi-Guest Seats) -->
                    <!-- ============================================== -->
                    <div class="tab-pane fade {{ $room->room_type === 'video' ? '' : 'show active' }}" id="voiceStagePane" role="tabpanel">
                        <div class="row g-3">
                            <!-- Left: Voice Stage Grid (Seats 1..16) -->
                            <div class="col-12 col-md-8 col-lg-9">
                                <div class="p-3 rounded-4" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); min-height: 280px;">
                                    <div class="row g-3 justify-content-center">
                                        @foreach($room->seats as $seat)
                                            <div class="col-4 col-sm-3 text-center">
                                                <div class="ch-seat-tile p-2 rounded-4 text-center position-relative {{ $seat->status === 'occupied' && !$seat->is_muted ? 'speaking-active' : '' }}" style="background: {{ $seat->status === 'occupied' ? 'rgba(30, 41, 59, 0.6)' : 'rgba(255, 255, 255, 0.02)' }}; border: 1.5px solid {{ $seat->status === 'occupied' ? ($seat->is_muted ? 'rgba(239, 68, 68, 0.5)' : '#10b981') : 'rgba(255, 255, 255, 0.06)' }}; transition: all 0.3s ease;">
                                                    
                                                    @if($seat->status === 'occupied' && $seat->user)
                                                        <!-- Occupied Seat -->
                                                        <div class="position-relative d-inline-block mb-1">
                                                            <div class="avatar-halo-wrap {{ !$seat->is_muted ? 'halo-pulse-green' : '' }}">
                                                                <img src="{{ $seat->user->avatar_url }}" alt="{{ $seat->user->name }}" style="width: 54px; height: 54px; border-radius: 50%; object-fit: cover; border: 2px solid {{ $seat->is_muted ? '#ef4444' : '#10b981' }};">
                                                            </div>

                                                            <!-- Host Crown Badge -->
                                                            @if($seat->seat_index === 1 || $seat->role === 'host')
                                                                <span class="position-absolute top-0 end-0 badge bg-warning text-dark rounded-circle p-1" style="transform: translate(25%, -25%);">
                                                                    <i class="fa-solid fa-crown" style="font-size: 8px;"></i>
                                                                </span>
                                                            @endif

                                                            <!-- Mic Mute Indicator -->
                                                            <span class="position-absolute bottom-0 end-0 badge {{ $seat->is_muted ? 'bg-danger' : 'bg-success' }} rounded-circle p-1" style="transform: translate(15%, 15%); font-size: 8px;">
                                                                <i class="fa-solid {{ $seat->is_muted ? 'fa-microphone-slash' : 'fa-microphone' }}"></i>
                                                            </span>
                                                        </div>

                                                        <div class="fw-bold text-white text-truncate" style="font-size: 12px;">
                                                            {{ $seat->user->display_name ?? $seat->user->name }}
                                                        </div>
                                                        <div class="text-white-50" style="font-size: 10px;">
                                                            {{ $seat->seat_index === 1 ? 'হোস্ট' : 'স্পিকার' }}
                                                        </div>

                                                        <!-- Admin Quick Action Controls -->
                                                        <div class="d-flex align-items-center justify-content-center gap-1 mt-2">
                                                            <!-- Toggle Mic -->
                                                            <form action="{{ route('admin.party-rooms.mute-seat', ['id' => $room->id, 'seatIndex' => $seat->seat_index]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-dark p-1 rounded-circle" style="width: 22px; height: 22px; font-size: 9px;" title="{{ $seat->is_muted ? 'Unmute' : 'Mute' }}">
                                                                    <i class="fa-solid {{ $seat->is_muted ? 'fa-microphone text-success' : 'fa-microphone-slash text-warning' }}"></i>
                                                                </button>
                                                            </form>

                                                            <!-- Kick/Remove if not Host -->
                                                            @if($seat->seat_index > 1)
                                                                <form action="{{ route('admin.party-rooms.kick-seat', ['id' => $room->id, 'seatIndex' => $seat->seat_index]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove user from seat?');">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-sm btn-dark p-1 rounded-circle" style="width: 22px; height: 22px; font-size: 9px;" title="Remove from seat">
                                                                        <i class="fa-solid fa-xmark text-danger"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>

                                                    @else
                                                        <!-- Empty Seat -->
                                                        <div class="py-2">
                                                            <div class="empty-seat-circle d-flex align-items-center justify-content-center mx-auto mb-1" style="width: 50px; height: 50px; border-radius: 50%; border: 1.5px dashed rgba(255, 255, 255, 0.2); background: rgba(255, 255, 255, 0.02);">
                                                                <i class="fa-solid fa-couch text-white-50" style="font-size: 16px;"></i>
                                                            </div>
                                                            <div class="text-white-50 fw-semibold" style="font-size: 11px;">আপনার সিট</div>
                                                            <div class="text-muted" style="font-size: 9px;">ফাঁকা সিট</div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Speaker Queue Sidebar (স্পিকার কিউ অনুরোধ) -->
                            <div class="col-12 col-md-4 col-lg-3">
                                <div class="ch-speaker-queue p-3 rounded-4 h-100" style="background: #0e1626; border: 1px solid rgba(255, 255, 255, 0.08);">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0 fw-bold text-white" style="font-size: 12px;">
                                            স্পিকার কিউ (অনুরোধ)
                                        </h6>
                                        <span class="badge bg-primary rounded-pill px-2 py-0" style="font-size: 10px;">{{ count($pendingRequests) }}</span>
                                    </div>

                                    <div class="queue-list d-flex flex-column gap-2" style="max-height: 280px; overflow-y: auto;">
                                        @forelse($pendingRequests as $req)
                                            <div class="queue-item p-2 rounded-3 d-flex align-items-center justify-content-between gap-2" style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.06);">
                                                <div class="d-flex align-items-center gap-2 text-truncate">
                                                    <img src="{{ $req->user->avatar_url ?? asset('images/default-avatar.png') }}" alt="{{ $req->user->name ?? 'User' }}" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover;">
                                                    <div class="text-truncate">
                                                        <div class="fw-bold text-white text-truncate" style="font-size: 11px;">{{ $req->user->display_name ?? $req->user->name ?? 'Guest' }}</div>
                                                        <div class="text-white-50" style="font-size: 9px;">{{ $req->created_at->diffForHumans(null, true) }}</div>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                                    <!-- Accept (গ্রহণ করুন) -->
                                                    <form action="{{ route('admin.party-rooms.respond-request', ['id' => $room->id, 'requestId' => $req->id]) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="action" value="accept">
                                                        <button type="submit" class="btn btn-sm text-white px-2 py-0 rounded fw-bold" style="background: #10b981; font-size: 10px;">
                                                            গ্রহণ করুন
                                                        </button>
                                                    </form>
                                                    <!-- Reject (বাতিল করুন) -->
                                                    <form action="{{ route('admin.party-rooms.respond-request', ['id' => $room->id, 'requestId' => $req->id]) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="action" value="reject">
                                                        <button type="submit" class="btn btn-sm text-white-50 px-2 py-0 rounded" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.3); font-size: 10px;">
                                                            বাতিল করুন
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-4 text-white-50 small">
                                                <i class="fa-solid fa-user-clock fs-4 mb-2 opacity-50"></i>
                                                <div>কোনো অপেক্ষারত স্পিকার অনুরোধ নেই</div>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- TAB 2: 📹 VIDEO MULTI-GUEST STAGE (4–5 Video Tiles) -->
                    <!-- ============================================== -->
                    <div class="tab-pane fade {{ $room->room_type === 'video' ? 'show active' : '' }}" id="videoStagePane" role="tabpanel">
                        <div class="p-3 rounded-4" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05);">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-white fw-bold mb-0" style="font-size: 13px;">
                                    <i class="fa-solid fa-video text-danger me-1"></i> ৪ থেকে ৫ জন মাল্টি-গেস্ট লাইভ ভিডিও গ্রিড
                                </h6>
                                <span class="badge bg-danger rounded-pill px-3 py-1">HD Live Stream</span>
                            </div>

                            <!-- 4-5 Video Grid Layout -->
                            <div class="row g-3">
                                @php
                                    $videoSeats = $room->seats->take(5);
                                @endphp

                                @foreach($videoSeats as $index => $seat)
                                    <div class="{{ $loop->first ? 'col-12 col-md-6' : 'col-6 col-md-3' }}">
                                        <div class="ch-video-tile rounded-4 position-relative overflow-hidden shadow-sm" style="background: #121826; border: 2px solid {{ $seat->status === 'occupied' ? '#10b981' : 'rgba(255, 255, 255, 0.1)' }}; min-height: 180px; height: 100%;">
                                            
                                            @if($seat->status === 'occupied' && $seat->user)
                                                <!-- Active Video Feed Simulation -->
                                                <div class="video-feed-bg position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: radial-gradient(circle at center, rgba(16, 185, 129, 0.1) 0%, rgba(10, 15, 29, 0.95) 100%);">
                                                    <img src="{{ $seat->user->avatar_url }}" alt="{{ $seat->user->name }}" style="width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 2px solid #10b981; box-shadow: 0 0 20px rgba(16, 185, 129, 0.6);">
                                                </div>

                                                <!-- Top Left: Host/Guest Badge -->
                                                <div class="position-absolute top-0 start-0 m-2">
                                                    <span class="badge {{ $seat->seat_index === 1 ? 'bg-warning text-dark' : 'bg-dark text-white' }} px-2 py-1 rounded-pill" style="font-size: 10px;">
                                                        {{ $seat->seat_index === 1 ? '👑 হোস্ট (Host)' : 'গেস্ট #' . $seat->seat_index }}
                                                    </span>
                                                </div>

                                                <!-- Top Right: Audio Wave Pulse -->
                                                <div class="position-absolute top-0 end-0 m-2">
                                                    <span class="badge {{ $seat->is_muted ? 'bg-danger' : 'bg-success' }} rounded-pill px-2 py-1" style="font-size: 10px;">
                                                        <i class="fa-solid {{ $seat->is_muted ? 'fa-microphone-slash' : 'fa-microphone' }} me-1"></i>
                                                        {{ $seat->is_muted ? 'Muted' : 'Speaking' }}
                                                    </span>
                                                </div>

                                                <!-- Bottom Bar: User Name & Controls -->
                                                <div class="position-absolute bottom-0 start-0 w-100 p-2 d-flex justify-content-between align-items-center" style="background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);">
                                                    <div class="text-truncate me-2">
                                                        <div class="fw-bold text-white text-truncate" style="font-size: 12px;">{{ $seat->user->display_name ?? $seat->user->name }}</div>
                                                        <small class="text-white-50" style="font-size: 9px;">ID: {{ $seat->user->account_id }}</small>
                                                    </div>

                                                    <div class="d-flex align-items-center gap-1">
                                                        <!-- Mute -->
                                                        <form action="{{ route('admin.party-rooms.mute-seat', ['id' => $room->id, 'seatIndex' => $seat->seat_index]) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-dark p-1 rounded" style="font-size: 10px;" title="Mute/Unmute">
                                                                <i class="fa-solid {{ $seat->is_muted ? 'fa-microphone text-success' : 'fa-microphone-slash text-warning' }}"></i>
                                                            </button>
                                                        </form>
                                                        <!-- Kick -->
                                                        @if($seat->seat_index > 1)
                                                            <form action="{{ route('admin.party-rooms.kick-seat', ['id' => $room->id, 'seatIndex' => $seat->seat_index]) }}" method="POST" onsubmit="return confirm('Remove user from video stage?');">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-danger p-1 rounded" style="font-size: 10px;" title="Kick from Video">
                                                                    <i class="fa-solid fa-xmark"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <!-- Empty Video Slot -->
                                                <div class="d-flex flex-column align-items-center justify-content-center h-100 p-4 text-center">
                                                    <div class="empty-video-circle d-flex align-items-center justify-content-center mb-2" style="width: 52px; height: 52px; border-radius: 50%; border: 1.5px dashed rgba(255, 255, 255, 0.2);">
                                                        <i class="fa-solid fa-video text-white-50"></i>
                                                    </div>
                                                    <div class="text-white-50 fw-semibold" style="font-size: 12px;">ভিডিও সিট #{{ $index + 1 }}</div>
                                                    <small class="text-muted" style="font-size: 10px;">ফাঁকা ভিডিও স্লট</small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Live Speaking Banner Bar -->
                <div class="px-3 py-2 d-flex justify-content-between align-items-center" style="background: rgba(16, 185, 129, 0.08); border-top: 1px solid rgba(255, 255, 255, 0.05); border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                    <div class="d-flex align-items-center gap-2 text-success fw-bold" style="font-size: 13px;">
                        <i class="fa-solid fa-volume-high animate-bounce"></i>
                        <span>Shakil কথা বলছেন...</span>
                    </div>
                    <div class="text-white-50" style="font-size: 11px;">
                        <i class="fa-solid fa-hand-point-up text-warning me-1"></i> সিটে ক্লিক করে মাইক নিন
                    </div>
                </div>

                <!-- 5. 🟢 REAL-TIME ROOM CHAT STREAM (Exact Mobile App Design) -->
                <div class="ch-app-chat-section p-3" style="background: #080c16;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-success"><i class="fa-solid fa-circle" style="font-size: 9px;"></i></span>
                            <h6 class="mb-0 fw-bold text-white" style="font-size: 13px;">
                                রিয়াল-টাইম রুম চ্যাট (আনলিমিটেড SMS)
                            </h6>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-dark text-white-50 rounded-pill px-2 py-1" style="font-size: 10px;">{{ count($recentMessages) }} SMS</span>
                        </div>
                    </div>

                    <!-- Chat Bubble Feed -->
                    <div class="ch-chat-bubble-stream d-flex flex-column gap-3 p-2 rounded-3" style="max-height: 280px; overflow-y: auto; background: rgba(0, 0, 0, 0.2);">
                        @forelse($recentMessages as $msg)
                            <div class="ch-chat-item d-flex align-items-start gap-2">
                                <img src="{{ $msg->user->avatar_url ?? asset('images/default-avatar.png') }}" alt="{{ $msg->user->name ?? 'User' }}" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 1px solid rgba(255, 255, 255, 0.1);">
                                <div style="max-width: 85%;">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="fw-bold text-white" style="font-size: 12px;">{{ $msg->user->display_name ?? $msg->user->name ?? 'User' }}</span>
                                        <span class="text-white-50" style="font-size: 10px;">এখন</span>
                                        @if($msg->type === 'gift')
                                            <span class="badge bg-warning text-dark px-2" style="font-size: 9px;">🎁 GIFT</span>
                                        @elseif($msg->type === 'system')
                                            <span class="badge bg-info text-dark px-2" style="font-size: 9px;">📢 SYSTEM</span>
                                        @endif
                                    </div>

                                    @if($msg->type === 'gift')
                                        <div class="ch-bubble-gift p-2 rounded-4 d-flex align-items-center gap-2" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); color: #fef08a;">
                                            @if($msg->gift && $msg->gift->icon_url)
                                                <img src="{{ $msg->gift->icon_url }}" alt="Gift" style="width: 24px; height: 24px; object-fit: contain;">
                                            @endif
                                            <span style="font-size: 12px;">{{ $msg->message }} ({{ number_format($msg->coins_amount) }} coins)</span>
                                        </div>
                                    @elseif($msg->type === 'image')
                                        <div class="mt-1">
                                            <img src="{{ $msg->full_image_url }}" alt="Image" style="max-width: 200px; max-height: 140px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1);">
                                        </div>
                                    @else
                                        <div class="ch-chat-bubble px-3 py-2 rounded-4 text-white" style="background: #172136; border: 1px solid rgba(255, 255, 255, 0.05); font-size: 13px; line-height: 1.4; border-radius: 18px;">
                                            {{ $msg->message }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <!-- Sample initial conversation matching screenshot -->
                            <div class="ch-chat-item d-flex align-items-start gap-2">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">S</div>
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="fw-bold text-white" style="font-size: 12px;">Sadia_B</span>
                                        <span class="text-white-50" style="font-size: 10px;">এখন</span>
                                    </div>
                                    <div class="ch-chat-bubble px-3 py-2 text-white" style="background: #172136; border-radius: 18px; font-size: 13px;">
                                        সবাই কথা বলতেছে খুব ভালো লাগছে!
                                    </div>
                                </div>
                            </div>
                            <div class="ch-chat-item d-flex align-items-start gap-2">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #10b981; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">F</div>
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="fw-bold text-white" style="font-size: 12px;">Faruk_H</span>
                                        <span class="text-white-50" style="font-size: 10px;">এখন</span>
                                    </div>
                                    <div class="ch-chat-bubble px-3 py-2 text-white" style="background: #172136; border-radius: 18px; font-size: 13px;">
                                        হেই সবাই কে!
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <!-- Admin Send Message Box -->
                    <form action="{{ route('admin.party-rooms.send-message', $room->id) }}" method="POST" class="mt-3">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="message" class="form-control bg-dark text-white border-secondary" placeholder="রুম চ্যাটে অ্যাডমিন অ্যানাউন্সমেন্ট পাঠান..." style="border-radius: 20px 0 0 20px; font-size: 12px;" required>
                            <button type="submit" class="btn btn-success fw-bold px-3" style="border-radius: 0 20px 20px 0; font-size: 12px;">
                                <i class="fa-solid fa-paper-plane me-1"></i> Send
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Side: Room Intelligence & Moderation Panel -->
        <div class="col-12 col-xl-4">
            <!-- Host Info Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-body p-4 text-center">
                    @if($room->host)
                        <div class="position-relative d-inline-block mb-3">
                            <img src="{{ $room->host->avatar_url }}" alt="Host" style="width: 76px; height: 76px; border-radius: 50%; object-fit: cover; border: 3px solid #3b82f6;">
                            <span class="badge bg-warning position-absolute bottom-0 end-0 rounded-circle p-1">
                                <i class="fa-solid fa-crown text-dark" style="font-size: 11px;"></i>
                            </span>
                        </div>
                        <h5 class="fw-bold mb-1">{{ $room->host->display_name ?? $room->host->name }}</h5>
                        <div class="text-muted mb-3" style="font-size: 12px;">Host ID: <code>{{ $room->host->account_id }}</code> &bull; Level {{ $room->host->level }}</div>
                        <a href="{{ route('admin.users.show', $room->host->id) }}" class="btn btn-outline-primary btn-sm w-100 rounded-pill">
                            <i class="fa-solid fa-user me-1"></i> View Host Profile
                        </a>
                    @endif
                </div>
            </div>

            <!-- Revenue Split & Financial Rules -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-coins text-warning me-2"></i> Revenue & Billing Rules</h6>
                </div>
                <div class="card-body p-4 pt-0">
                    <ul class="list-group list-group-flush" style="font-size: 13px;">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Rate Per Minute:</span>
                            <span class="fw-bold text-dark">{{ $room->coin_rate_per_minute }} coins/min</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Host Commission (50%):</span>
                            <span class="fw-bold text-success">{{ (int)$room->host_commission_percentage }}% ({{ number_format($room->total_earned_coins) }} coins)</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Admin Commission (50%):</span>
                            <span class="fw-bold text-primary">{{ (int)$room->admin_commission_percentage }}% ({{ number_format($room->total_admin_earned_coins) }} coins)</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Max Stage Seats:</span>
                            <span class="fw-bold text-dark">{{ $room->max_seats }} seats</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Active Audience In Room -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-users text-primary me-2"></i> Audience in Room</h6>
                    <span class="badge bg-primary-subtle text-primary rounded-pill">{{ count($activeAudience) }} active</span>
                </div>
                <div class="card-body p-3" style="max-height: 250px; overflow-y: auto;">
                    @forelse($activeAudience as $member)
                        <div class="d-flex align-items-center justify-content-between p-2 mb-1 rounded-3 hover-bg">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $member->user->avatar_url ?? asset('images/default-avatar.png') }}" alt="" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover;">
                                <div>
                                    <div class="fw-bold" style="font-size: 12px;">{{ $member->user->display_name ?? $member->user->name ?? 'Audience' }}</div>
                                    <small class="text-muted" style="font-size: 10px;">ID: {{ $member->user->account_id }}</small>
                                </div>
                            </div>
                            <span class="badge bg-light text-muted" style="font-size: 10px;">Viewer</span>
                        </div>
                    @empty
                        <div class="text-center py-3 text-muted small">No audience members currently logged in.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Audio Wave animation */
.wave-bar {
    display: inline-block;
    width: 3px;
    height: 12px;
    border-radius: 2px;
    animation: wavePulse 1.2s ease-in-out infinite;
}
.wave-bar:nth-child(2) { animation-delay: 0.2s; }
.wave-bar:nth-child(3) { animation-delay: 0.4s; }
.wave-bar:nth-child(4) { animation-delay: 0.6s; }

@keyframes wavePulse {
    0%, 100% { height: 4px; }
    50% { height: 16px; }
}

/* Green Pulsing Speaking Halo */
.halo-pulse-green {
    position: relative;
    border-radius: 50%;
}
.halo-pulse-green::before {
    content: '';
    position: absolute;
    top: -4px;
    left: -4px;
    right: -4px;
    bottom: -4px;
    border-radius: 50%;
    border: 2px solid #10b981;
    box-shadow: 0 0 12px rgba(16, 185, 129, 0.8);
    animation: haloPulse 1.5s infinite;
}

@keyframes haloPulse {
    0% { transform: scale(0.95); opacity: 0.8; }
    50% { transform: scale(1.08); opacity: 1; }
    100% { transform: scale(0.95); opacity: 0.8; }
}

.ch-stage-tabs .nav-link {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
}
.ch-stage-tabs .nav-link.active {
    background: #10b981 !important;
    border-color: #10b981 !important;
}
</style>
@endsection
