@extends('layouts.admin')

@section('title', 'Admin <-> Reseller Live Support Chat')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <a href="{{ route('admin.resellers.index') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Resellers</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Live Chat Support</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-headset text-primary"></i>
                <span>Reseller Live Support Chat</span>
            </h1>
            <p class="page-subtitle">Direct live communication channel between Platform Administrators and authorized coin Resellers.</p>
        </div>
        <a href="{{ route('admin.resellers.index') }}" class="btn btn-outline-secondary rounded-3 px-3">
            <i class="fa-solid fa-store me-1"></i> Resellers List
        </a>
    </div>

    <!-- Chat Card -->
    <div class="card border-0 rounded-4 shadow-sm overflow-hidden" style="background: var(--card-bg-light); border: 1px solid var(--border-color) !important; height: calc(100vh - 220px);">
        <div class="row g-0 h-100">
            <!-- Left: Resellers List -->
            <div class="col-12 col-md-4 col-xl-3 border-end h-100 d-flex flex-column" style="background: var(--bg-main);">
                <div class="p-3 border-bottom bg-white">
                    <h6 class="fw-bold mb-0 text-dark">Active Resellers ({{ $resellers->count() }})</h6>
                </div>
                <div class="flex-grow-1 overflow-auto p-2">
                    @forelse($resellers as $res)
                        @php
                            $isSelected = $selectedReseller && $selectedReseller->id === $res->id;
                        @endphp
                        <a href="{{ route('admin.resellers.chat.selected', $res->id) }}" class="d-flex align-items-center gap-3 p-3 rounded-3 mb-1 text-decoration-none transition-all {{ $isSelected ? 'bg-white shadow-sm border' : 'hover-bg-light' }}">
                            <div class="position-relative">
                                <img src="{{ $res->avatar_url }}" alt="{{ $res->name }}" class="rounded-circle border" style="width: 42px; height: 42px; object-fit: cover;">
                                <span class="position-absolute bottom-0 end-0 p-1 border border-white rounded-circle {{ $res->is_online ? 'bg-success' : 'bg-secondary' }}" style="width: 12px; height: 12px;"></span>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <strong class="text-truncate text-dark d-block" style="font-size: 13px;">{{ $res->name }}</strong>
                                <small class="text-muted d-block" style="font-size: 11px;">Stock: {{ number_format($res->coins_balance) }} coins</small>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-4 text-muted">No resellers found.</div>
                    @endforelse
                </div>
            </div>

            <!-- Right: Active Chat Area -->
            <div class="col-12 col-md-8 col-xl-9 h-100 d-flex flex-column bg-white">
                @if($selectedReseller)
                    <div class="p-3 px-4 border-bottom d-flex justify-content-between align-items-center bg-white">
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $selectedReseller->avatar_url }}" alt="{{ $selectedReseller->name }}" class="rounded-circle border" style="width: 44px; height: 44px; object-fit: cover;">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">{{ $selectedReseller->name }}</h6>
                                <small class="text-muted">{{ $selectedReseller->email }} · Balance: <strong class="text-warning">{{ number_format($selectedReseller->coins_balance) }}</strong> Gems</small>
                            </div>
                        </div>
                    </div>

                    <!-- Messages Feed -->
                    <div class="flex-grow-1 overflow-auto p-4 d-flex flex-column gap-3" id="adminMessagesFeed" style="background: var(--bg-main);">
                        @forelse($messages as $msg)
                            @php
                                $isAdmin = $msg->sender_type === 'admin';
                            @endphp
                            <div class="d-flex flex-column {{ $isAdmin ? 'align-items-end' : 'align-items-start' }}">
                                <div class="p-3 rounded-4 shadow-sm" style="max-width: 75%; background: {{ $isAdmin ? 'linear-gradient(135deg, #2563eb, #3b82f6)' : '#ffffff' }}; color: {{ $isAdmin ? '#ffffff' : '#1e293b' }}; border: 1px solid {{ $isAdmin ? 'transparent' : 'var(--border-color)' }};">
                                    @if($msg->message)
                                        <div style="font-size: 14px; white-space: pre-wrap;">{!! nl2br(e($msg->message)) !!}</div>
                                    @endif
                                    @if($msg->full_media_url)
                                        <div class="mt-2">
                                            <a href="{{ $msg->full_media_url }}" target="_blank">
                                                <img src="{{ $msg->full_media_url }}" class="rounded-3 img-fluid" style="max-height: 250px;">
                                            </a>
                                        </div>
                                    @endif
                                </div>
                                <small class="text-muted mt-1 px-1" style="font-size: 10px;">
                                    {{ $msg->created_at->format('h:i A') }} · {{ $isAdmin ? 'Admin (You)' : $selectedReseller->name }}
                                </small>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <p class="mb-0">No messages yet. Send a message to {{ $selectedReseller->name }} below.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Chat Input -->
                    <div class="p-3 border-top bg-white">
                        <form method="POST" action="{{ route('admin.resellers.chat.send') }}" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
                            @csrf
                            <input type="hidden" name="reseller_id" value="{{ $selectedReseller->id }}">

                            <label class="btn btn-light border rounded-circle p-2" style="width: 42px; height: 42px; cursor: pointer;">
                                <i class="fa-solid fa-paperclip text-muted"></i>
                                <input type="file" name="image" accept="image/*" class="d-none" onchange="this.form.submit()">
                            </label>

                            <input type="text" name="message" class="form-control rounded-pill px-3" placeholder="Type message to {{ $selectedReseller->name }}..." autofocus autocomplete="off">

                            <button type="submit" class="btn btn-primary rounded-circle p-2" style="width: 42px; height: 42px;">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                @else
                    <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-4 text-center">
                        <i class="fa-solid fa-headset fa-3x mb-3" style="opacity: 0.3;"></i>
                        <h5>Select a Reseller</h5>
                        <p class="mb-0">Choose a reseller from the left list to start live support.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const feed = document.getElementById('adminMessagesFeed');
    if (feed) feed.scrollTop = feed.scrollHeight;
});
</script>
@endpush
@endsection
