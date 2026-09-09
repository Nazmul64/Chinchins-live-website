@extends('layouts.admin')

@section('title', 'Live Chat Users & Customer Service')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Customer Support</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-headset text-primary"></i>
                <span>Live Chat Users & Support</span>
            </h1>
            <p class="page-subtitle">24/7 Live Customer Service Console to answer user inquiries and assist with coin deposits & issues.</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            @if($totalUnread > 0)
                <span class="badge bg-danger rounded-pill px-3 py-2 fs-6">
                    <i class="fa-solid fa-bell me-1"></i> {{ $totalUnread }} Unread Inquiries
                </span>
            @endif
        </div>
    </div>

    <!-- Live Support Chat Box (Two-Pane Layout) -->
    <div class="card border-0 rounded-4 shadow-sm overflow-hidden" style="background: var(--card-bg-light); border: 1px solid var(--card-border-light) !important; height: calc(100vh - 210px); min-height: 550px;">
        <div class="row g-0 h-100">
            <!-- Left Pane: Conversations List -->
            <div class="col-12 col-md-4 col-xl-3 border-end h-100 d-flex flex-column" style="background: var(--bg-main, #f8fafc);">
                <!-- Search & Header -->
                <div class="p-3 border-bottom bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="fw-bold mb-0 text-dark">User Inquiries</h6>
                        <span class="badge bg-primary rounded-pill">{{ $conversations->count() }} Users</span>
                    </div>
                    <form method="GET" action="{{ route('admin.support.index') }}">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input type="text" name="search" class="form-control bg-light border-start-0 ps-0" placeholder="Search user name or ID..." value="{{ request('search') }}">
                        </div>
                    </form>
                </div>

                <!-- Users List Scroll -->
                <div class="flex-grow-1 overflow-auto p-2" id="userConversationsList">
                    @forelse($conversations as $convUser)
                        @php
                            $isSelected = $selectedUser && $selectedUser->id === $convUser->id;
                        @endphp
                        <a href="{{ route('admin.support.index', ['user_id' => $convUser->id]) }}" 
                           class="d-flex align-items-center gap-3 p-3 rounded-3 mb-1 text-decoration-none transition-all {{ $isSelected ? 'bg-white shadow-sm border' : 'hover-bg-light' }}" 
                           style="border: 1px solid {{ $isSelected ? 'var(--primary-color, #3b82f6)' : 'transparent' }} !important;">
                            <div class="position-relative">
                                <img src="{{ $convUser->avatar_url }}" alt="{{ $convUser->display_name }}" class="rounded-circle border" style="width: 44px; height: 44px; object-fit: cover;">
                                @if($convUser->unread_count > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">
                                        {{ $convUser->unread_count }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-truncate text-dark d-block" style="font-size: 13px; max-width: 140px;">
                                        {{ $convUser->display_name }}
                                    </strong>
                                    <small class="text-muted" style="font-size: 11px;">
                                        {{ $convUser->last_message && $convUser->last_message->created_at ? $convUser->last_message->created_at->format('h:i A') : '' }}
                                    </small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-truncate text-muted d-block" style="font-size: 12px; max-width: 160px;">
                                        {{ $convUser->last_message ? ($convUser->last_message->type === 'image' ? '📷 Photo' : $convUser->last_message->message) : 'No messages yet' }}
                                    </small>
                                    <span class="badge bg-light text-muted font-monospace" style="font-size: 10px;">ID: {{ $convUser->account_id }}</span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-inbox fa-3x mb-2 text-muted" style="opacity: 0.3;"></i>
                            <div class="fw-bold" style="font-size: 13px;">No Support Inquiries</div>
                            <small class="text-muted">User messages will appear here.</small>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right Pane: Live Support Chat Thread -->
            <div class="col-12 col-md-8 col-xl-9 h-100 d-flex flex-column bg-white">
                @if($selectedUser)
                    <!-- Chat Header -->
                    <div class="p-3 px-4 border-bottom d-flex justify-content-between align-items-center bg-white">
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $selectedUser->avatar_url }}" alt="{{ $selectedUser->display_name }}" class="rounded-circle border" style="width: 44px; height: 44px; object-fit: cover;">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">
                                    {{ $selectedUser->display_name }}
                                    @if($selectedUser->is_verified)
                                        <i class="fa-solid fa-circle-check text-primary ms-1" style="font-size: 12px;"></i>
                                    @endif
                                </h6>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="badge bg-light text-dark font-monospace" style="font-size: 11px;">Account ID: {{ $selectedUser->account_id }}</span>
                                    <span class="text-muted" style="font-size: 12px;">Balance: <strong>{{ number_format($selectedUser->coins) }} Coins</strong></span>
                                    <span class="badge bg-secondary" style="font-size: 10px;">{{ $selectedUser->level ?: 'Lv1' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('admin.users.show', $selectedUser->id) }}" class="btn btn-outline-primary btn-sm rounded-3" target="_blank" title="View Full Profile">
                                <i class="fa-solid fa-user me-1"></i> User Profile
                            </a>
                        </div>
                    </div>

                    <!-- Messages Feed -->
                    <div class="flex-grow-1 overflow-auto p-4 d-flex flex-column gap-3" id="adminSupportMessagesFeed" style="background: #f8fafc;">
                        @forelse($messages as $msg)
                            @php
                                $isAdmin = $msg->sender_type === 'admin';
                            @endphp
                            <div class="d-flex {{ $isAdmin ? 'justify-content-end' : 'justify-content-start' }}">
                                <div style="max-width: 70%;">
                                    <div class="p-3 rounded-4 {{ $isAdmin ? 'bg-primary text-white shadow-sm' : 'bg-white text-dark border shadow-sm' }}" 
                                         style="border-bottom-right-radius: {{ $isAdmin ? '4px' : '16px' }} !important; border-bottom-left-radius: {{ !$isAdmin ? '4px' : '16px' }} !important;">
                                        
                                        @if(!$isAdmin)
                                            <div class="fw-bold mb-1 text-primary" style="font-size: 11px;">
                                                <i class="fa-solid fa-user me-1"></i> {{ $selectedUser->display_name }} (User)
                                            </div>
                                        @else
                                            <div class="fw-bold mb-1 text-warning" style="font-size: 11px;">
                                                <i class="fa-solid fa-headset me-1"></i> Admin Support (Official)
                                            </div>
                                        @endif

                                        @if($msg->type === 'image' && $msg->media_url)
                                            <div class="mb-2">
                                                <a href="{{ $msg->full_media_url }}" target="_blank">
                                                    <img src="{{ $msg->full_media_url }}" alt="Attachment" class="rounded-3 img-fluid border" style="max-height: 250px; object-fit: contain;">
                                                </a>
                                            </div>
                                        @endif

                                        @if($msg->message)
                                            <p class="mb-0" style="font-size: 14px; white-space: pre-wrap; line-height: 1.4;">{{ $msg->message }}</p>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center gap-1 mt-1 {{ $isAdmin ? 'justify-content-end' : 'justify-content-start' }}" style="font-size: 11px; color: #94a3b8;">
                                        <span>{{ $msg->created_at ? $msg->created_at->format('h:i A') : '' }}</span>
                                        @if($isAdmin)
                                            <i class="fa-solid fa-check-double text-primary ms-1" title="Sent"></i>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted my-auto">
                                <i class="fa-solid fa-comments fa-3x mb-2" style="opacity: 0.2;"></i>
                                <div class="fw-bold">No messages in this support conversation</div>
                                <small class="text-muted">Type a reply below to reach out to {{ $selectedUser->display_name }}.</small>
                            </div>
                        @endforelse
                    </div>

                    <!-- Admin Reply Form Bar -->
                    <div class="p-3 border-top bg-white">
                        <form action="{{ route('admin.support.reply', $selectedUser->id) }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
                            @csrf
                            <label class="btn btn-light rounded-circle p-2 border" style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; cursor: pointer;" title="Attach Screenshot / Image">
                                <i class="fa-solid fa-image text-muted"></i>
                                <input type="file" name="image" accept="image/*" class="d-none" onchange="previewImage(this)">
                            </label>
                            <input type="text" name="message" id="supportReplyInput" class="form-control rounded-pill px-3" placeholder="Type your reply to {{ $selectedUser->display_name }}..." autocomplete="off">
                            <button type="submit" class="btn btn-primary rounded-circle p-2" style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;" title="Send Reply">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </form>
                        <div id="imagePreviewContainer" class="d-none mt-2 d-flex align-items-center gap-2">
                            <span class="badge bg-info text-dark" id="fileNameBadge"></span>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="clearImagePreview()"><i class="fa-solid fa-xmark"></i> Remove</button>
                        </div>
                    </div>
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
                        <i class="fa-solid fa-headset fa-4x mb-3 text-muted" style="opacity: 0.3;"></i>
                        <h5 class="fw-bold">Select a user to view support chat</h5>
                        <p class="text-muted" style="font-size: 13px;">Choose from user inquiries on the left to start live support.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const feed = document.getElementById('adminSupportMessagesFeed');
    if (feed) {
        feed.scrollTop = feed.scrollHeight;
    }
});

function previewImage(input) {
    if (input.files && input.files[0]) {
        document.getElementById('fileNameBadge').innerText = '📎 ' + input.files[0].name;
        document.getElementById('imagePreviewContainer').classList.remove('d-none');
    }
}

function clearImagePreview() {
    const fileInput = document.querySelector('input[name="image"]');
    if (fileInput) fileInput.value = '';
    document.getElementById('imagePreviewContainer').classList.add('d-none');
}
</script>
@endpush
@endsection
