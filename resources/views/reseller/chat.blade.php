@extends('layouts.reseller')

@section('title', 'Reseller Live Customer Chat')

@section('content')
<div class="card border-0 rounded-4 shadow-sm overflow-hidden" style="background: #fff; border: 1px solid #e2e8f0 !important; height: calc(100vh - 120px);">
    <div class="row g-0 h-100">
        <!-- Left Sidebar: Conversations List -->
        <div class="col-12 col-md-4 col-xl-3 border-end h-100 d-flex flex-column" style="background: #f8fafc;">
            <div class="p-3 border-bottom bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h5 class="fw-bold mb-0 text-dark">Customer Inquiries</h5>
                    <span class="badge bg-warning text-dark rounded-pill">{{ $conversations->count() }} Users</span>
                </div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" id="chatSearchInput" class="form-control bg-light border-start-0 ps-0" placeholder="Search conversations..." onkeyup="filterChats(this.value)">
                </div>
            </div>

            <!-- Conversations Scroll -->
            <div class="flex-grow-1 overflow-auto p-2" id="conversationsContainer">
                @forelse($conversations as $conv)
                    @php
                        $isSelected = $selectedUser && $selectedUser->id === $conv->id;
                    @endphp
                    <a href="{{ route('reseller.chat.user', $conv->id) }}" class="d-flex align-items-center gap-3 p-3 rounded-3 mb-1 text-decoration-none transition-all {{ $isSelected ? 'bg-white shadow-sm border' : 'hover-bg-light' }}" style="border: 1px solid {{ $isSelected ? '#e2e8f0' : 'transparent' }};">
                        <div class="position-relative">
                            <img src="{{ $conv->avatar_url }}" alt="{{ $conv->name }}" class="rounded-circle border" style="width: 44px; height: 44px; object-fit: cover;">
                            @if($conv->unread_count > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">
                                    {{ $conv->unread_count }}
                                </span>
                            @endif
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="text-truncate text-dark d-block" style="font-size: 14px; max-width: 140px;">
                                    {{ $conv->name }}
                                </strong>
                                @if($conv->last_message)
                                    <small class="text-muted" style="font-size: 10px;">{{ $conv->last_message->created_at->format('h:i A') }}</small>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-light text-primary border" style="font-size: 10px;">UID: {{ $conv->account_id ?: $conv->id }}</span>
                                <p class="text-muted text-truncate mb-0" style="font-size: 12px;">
                                    {{ $conv->last_message ? ($conv->last_message->message ?: ($conv->last_message->type == 'image' ? '📷 Image' : ($conv->last_message->type == 'voice' ? '🎤 Voice' : ''))) : 'No messages' }}
                                </p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-comments fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                        <p class="mb-0">No active customer chats yet.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Side: Active Chat Window -->
        <div class="col-12 col-md-8 col-xl-9 h-100 d-flex flex-column bg-white">
            @if($selectedUser)
                <!-- Chat Header -->
                <div class="p-3 px-4 border-bottom d-flex justify-content-between align-items-center bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $selectedUser->avatar_url }}" alt="{{ $selectedUser->name }}" class="rounded-circle border" style="width: 44px; height: 44px; object-fit: cover;">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="fw-bold mb-0 text-dark">{{ $selectedUser->name }}</h6>
                                <span class="badge bg-primary text-white rounded-pill px-2" style="font-size: 10px;">{{ $selectedUser->level ?: 'Lv1' }}</span>
                            </div>
                            <small class="text-muted font-monospace" style="font-size: 12px;">
                                User ID: <strong class="text-dark">{{ $selectedUser->account_id ?: $selectedUser->id }}</strong> · Current Gems: <strong class="text-warning">{{ number_format($selectedUser->coins) }}</strong>
                            </small>
                        </div>
                    </div>

                    <!-- Quick Transfer from Chat Button -->
                    <button type="button" class="btn btn-warning btn-sm rounded-pill fw-bold px-3" onclick="openQuickTransferModal('{{ $selectedUser->account_id ?: $selectedUser->id }}', '{{ $selectedUser->name }}')">
                        <i class="fa-solid fa-bolt me-1"></i> Send Coins to User
                    </button>
                </div>

                <!-- Messages Feed -->
                <div class="flex-grow-1 overflow-auto p-4 d-flex flex-column gap-3" id="messagesFeed" style="background: #f8fafc;">
                    @forelse($messages as $msg)
                        @php
                            $isMe = $msg->sender_type === 'reseller';
                            $isSystem = $msg->type === 'system';
                        @endphp

                        @if($isSystem)
                            <div class="text-center my-2">
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill shadow-sm" style="font-size: 12px;">
                                    {{ $msg->message }}
                                </span>
                            </div>
                        @else
                            <div class="d-flex flex-column {{ $isMe ? 'align-items-end' : 'align-items-start' }}">
                                <div class="p-3 rounded-4 shadow-sm position-relative" style="max-width: 75%; background: {{ $isMe ? 'linear-gradient(135deg, #312e81, #4338ca)' : '#ffffff' }}; color: {{ $isMe ? '#ffffff' : '#1e293b' }}; border: 1px solid {{ $isMe ? 'transparent' : '#e2e8f0' }};">
                                    <!-- Recharge Inquiry Badge / Highlights -->
                                    @if($msg->coins_amount)
                                        <div class="mb-2 p-2 rounded-3" style="background: rgba(245,158,11,0.15); border: 1px dashed #f59e0b; font-size: 12px;">
                                            <strong class="text-warning"><i class="fa-solid fa-gem me-1"></i> Recharge Inquiry:</strong> {{ number_format($msg->coins_amount) }} Gems
                                        </div>
                                    @endif

                                    <!-- Message Text -->
                                    @if($msg->message)
                                        <div style="font-size: 14px; white-space: pre-wrap; word-break: break-word;">{!! nl2br(e($msg->message)) !!}</div>
                                    @endif

                                    <!-- Attached Media (Image / Screenshot) -->
                                    @if($msg->full_media_url && $msg->type === 'image')
                                        <div class="mt-2">
                                            <a href="{{ $msg->full_media_url }}" target="_blank">
                                                <img src="{{ $msg->full_media_url }}" alt="Attachment" class="rounded-3 img-fluid border" style="max-height: 250px; object-fit: cover;">
                                            </a>
                                        </div>
                                    @elseif($msg->full_media_url && $msg->type === 'voice')
                                        <div class="mt-2">
                                            <audio controls src="{{ $msg->full_media_url }}" style="max-width: 240px; height: 36px;"></audio>
                                        </div>
                                    @endif
                                </div>
                                <small class="text-muted mt-1 px-1" style="font-size: 10px;">
                                    {{ $msg->created_at->format('h:i A') }} · {{ $isMe ? 'Sent by you' : $selectedUser->name }}
                                </small>
                            </div>
                        @endif
                    @empty
                        <div class="text-center py-5 text-muted">
                            <p class="mb-0">No messages in this conversation yet. Send a greeting below!</p>
                        </div>
                    @endforelse
                </div>

                <!-- Chat Input Footer -->
                <div class="p-3 border-top bg-white">
                    <form method="POST" action="{{ route('reseller.chat.send') }}" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">

                        <label class="btn btn-light border rounded-circle p-2" style="width: 42px; height: 42px; cursor: pointer;" title="Attach Image / Screenshot">
                            <i class="fa-solid fa-paperclip text-muted"></i>
                            <input type="file" name="image" accept="image/*" class="d-none" onchange="this.form.submit()">
                        </label>

                        <input type="text" name="message" class="form-control rounded-pill px-3" placeholder="Type your message to {{ $selectedUser->name }}..." autofocus autocomplete="off">

                        <button type="submit" class="btn btn-primary rounded-circle p-2" style="width: 42px; height: 42px;">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            @else
                <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-4 text-center">
                    <div style="width: 72px; height: 72px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 16px;">
                        <i class="fa-solid fa-comments text-muted"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Select a Conversation</h5>
                    <p class="mb-0" style="max-width: 320px; font-size: 13px;">Choose a customer from the left list to review payment details, exchange messages, and transfer coins.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Quick Transfer from Chat -->
<div class="modal fade" id="quickTransferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-bolt text-warning me-2"></i> Recharge User Coins</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('reseller.transfer') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Target Account ID</label>
                        <input type="text" name="target_account_id" id="qtAccountId" class="form-control font-monospace fw-bold" readonly required>
                        <small class="text-muted" id="qtUserName"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Gems to Send <span class="text-danger">*</span></label>
                        <input type="number" name="coins" class="form-control fs-5 font-monospace fw-bold" placeholder="7560" min="10" max="{{ $reseller->coins_balance }}" required>
                        <small class="text-muted">Stock: {{ number_format($reseller->coins_balance) }} gems</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Amount Paid (BDT)</label>
                        <input type="number" step="0.01" name="amount_bdt" class="form-control" placeholder="150.00">
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold" style="font-size: 13px;">Payment TrxID</label>
                        <input type="text" name="transaction_id" class="form-control font-monospace" placeholder="e.g. 9H8A7B6C">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold rounded-3">Confirm Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto scroll messages to bottom
document.addEventListener('DOMContentLoaded', function() {
    const feed = document.getElementById('messagesFeed');
    if (feed) {
        feed.scrollTop = feed.scrollHeight;
    }
});

function openQuickTransferModal(accountId, userName) {
    document.getElementById('qtAccountId').value = accountId;
    document.getElementById('qtUserName').innerText = 'User: ' + userName;
    const bsModal = new bootstrap.Modal(document.getElementById('quickTransferModal'));
    bsModal.show();
}

function filterChats(q) {
    const term = q.toLowerCase();
    const items = document.querySelectorAll('#conversationsContainer a');
    items.forEach(it => {
        const text = it.innerText.toLowerCase();
        it.style.display = text.includes(term) ? 'flex' : 'none';
    });
}
</script>
@endpush
@endsection
