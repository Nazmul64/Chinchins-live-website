@extends('layouts.reseller')

@section('title', 'Admin Live Support')

@section('content')
<div class="card border-0 rounded-4 shadow-sm overflow-hidden" style="background: #fff; border: 1px solid #e2e8f0 !important; height: calc(100vh - 120px);">
    <div class="p-3 px-4 border-bottom d-flex justify-content-between align-items-center bg-white">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 44px; height: 44px; background: rgba(59,130,246,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #2563eb; font-size: 20px;">
                <i class="fa-solid fa-headset"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Platform Admin Live Support</h6>
                <small class="text-muted">Chat directly with ChinChins Official Administration for coin refills, inquiries, or support.</small>
            </div>
        </div>
        <a href="{{ route('reseller.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    <!-- Messages Feed -->
    <div class="flex-grow-1 overflow-auto p-4 d-flex flex-column gap-3" id="resellerAdminMessagesFeed" style="background: #f8fafc;">
        @forelse($messages as $msg)
            @php
                $isMe = $msg->sender_type === 'reseller';
            @endphp
            <div class="d-flex flex-column {{ $isMe ? 'align-items-end' : 'align-items-start' }}">
                <div class="p-3 rounded-4 shadow-sm" style="max-width: 75%; background: {{ $isMe ? 'linear-gradient(135deg, #1e1b4b, #312e81)' : '#ffffff' }}; color: {{ $isMe ? '#ffffff' : '#1e293b' }}; border: 1px solid {{ $isMe ? 'transparent' : '#e2e8f0' }};">
                    @if($msg->message)
                        <div style="font-size: 14px; white-space: pre-wrap;">{!! nl2br(e($msg->message)) !!}</div>
                    @endif
                    @if($msg->full_media_url)
                        <div class="mt-2">
                            <a href="{{ $msg->full_media_url }}" target="_blank">
                                <img src="{{ $msg->full_media_url }}" class="rounded-3 img-fluid border" style="max-height: 250px;">
                            </a>
                        </div>
                    @endif
                </div>
                <small class="text-muted mt-1 px-1" style="font-size: 10px;">
                    {{ $msg->created_at->format('h:i A') }} · {{ $isMe ? 'You' : 'Admin' }}
                </small>
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-headset fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                <h5>No Messages Yet</h5>
                <p class="mb-0">Need help or coin refills? Send a message to the Admin below.</p>
            </div>
        @endforelse
    </div>

    <!-- Chat Input -->
    <div class="p-3 border-top bg-white">
        <form method="POST" action="{{ route('reseller.admin-chat.send') }}" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
            @csrf
            <label class="btn btn-light border rounded-circle p-2" style="width: 42px; height: 42px; cursor: pointer;" title="Attach Screenshot">
                <i class="fa-solid fa-paperclip text-muted"></i>
                <input type="file" name="image" accept="image/*" class="d-none" onchange="this.form.submit()">
            </label>

            <input type="text" name="message" class="form-control rounded-pill px-3" placeholder="Type your message to Admin..." autofocus autocomplete="off">

            <button type="submit" class="btn btn-primary rounded-circle p-2" style="width: 42px; height: 42px;">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const feed = document.getElementById('resellerAdminMessagesFeed');
    if (feed) feed.scrollTop = feed.scrollHeight;
});
</script>
@endpush
@endsection
