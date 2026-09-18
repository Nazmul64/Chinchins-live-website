@extends('layouts.admin')

@section('title', '1-on-1 Video Call Monitoring & Records')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <a href="{{ route('admin.calls.index') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Calls</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Video Call Monitoring</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-shield-halved text-danger"></i>
                <span>1-on-1 Video Call Monitoring & Compliance</span>
            </h1>
            <p class="page-subtitle">Inspect recorded 1-on-1 video call sessions, search by User/Account ID, review reported complaints, view snapshots/video evidence, and issue warnings or suspensions.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.calls.index') }}" class="btn btn-outline-primary" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-list me-1"></i> Call Sessions Log
            </a>
            <a href="{{ route('admin.calls.settings') }}" class="btn-ch-primary">
                <i class="fa-solid fa-sliders"></i> Call Settings
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="premium-stat-card" style="border-left: 4px solid #ef4444;">
                <div class="stat-icon-box" style="background: rgba(239,68,68,0.15); color: #ef4444;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Pending Abuse Complaints</span>
                    <h3 class="stat-count-value" style="color: #ef4444;">{{ $pendingReportsCount }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(239,68,68,0.1); color: #ef4444;">
                        Requires Review
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="premium-stat-card" style="border-left: 4px solid #3b82f6;">
                <div class="stat-icon-box stat-icon-blue">
                    <i class="fa-solid fa-video"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Logged Call Records</span>
                    <h3 class="stat-count-value" style="color: #2563eb;">{{ $callSessions->total() }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(59,130,246,0.1); color: #3b82f6;">
                        Searchable by ID
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="premium-stat-card" style="border-left: 4px solid #10b981;">
                <div class="stat-icon-box stat-icon-green">
                    <i class="fa-solid fa-server"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Active Engine</span>
                    <h3 class="stat-count-value" style="color: #10b981; font-size: 20px;">Hostinger VPS WebRTC</h3>
                    <span class="stat-badge-chip" style="background: rgba(16,185,129,0.1); color: #10b981;">
                        Dual-Engine Ready
                    </span>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px; background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); color: #10b981;">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Recent Abuse Reports / Complaints Section -->
    @if($reports->isNotEmpty())
    <div class="card mb-4" style="background: #1a162b; border: 1px solid rgba(239,68,68,0.25); border-radius: 16px; overflow: hidden;">
        <div class="card-header d-flex justify-content-between align-items-center py-3" style="background: rgba(239,68,68,0.08); border-bottom: 1px solid rgba(239,68,68,0.2);">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-flag text-danger"></i>
                <h5 class="mb-0 text-white fw-bold" style="font-size: 15px;">User Call Complaints & Reports</h5>
            </div>
            <span class="badge bg-danger rounded-pill px-3 py-1">{{ $reports->count() }} Recent</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle text-white mb-0" style="font-size: 13px;">
                <thead style="background: rgba(255,255,255,0.02); color: #94a3b8;">
                    <tr>
                        <th>Report ID</th>
                        <th>Call Session ID</th>
                        <th>Complainant (Reporter)</th>
                        <th>Reported User</th>
                        <th>Complaint Reason</th>
                        <th>Status</th>
                        <th>Evidence / Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $r)
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td class="fw-bold text-danger">#{{ $r->id }}</td>
                        <td>
                            <span class="badge bg-dark text-info border border-info-subtle">{{ $r->call_session_id }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $r->reporter?->avatar_url ?? asset('assets/images/default_avatar.png') }}" class="rounded-circle" width="32" height="32" style="object-fit: cover;">
                                <div>
                                    <div class="fw-bold text-white">{{ $r->reporter?->display_name ?? $r->reporter?->name ?? 'User' }}</div>
                                    <small class="text-muted">ID: {{ $r->reporter?->account_id ?? $r->reporter_id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $r->reportedUser?->avatar_url ?? asset('assets/images/default_avatar.png') }}" class="rounded-circle" width="32" height="32" style="object-fit: cover;">
                                <div>
                                    <div class="fw-bold text-danger">{{ $r->reportedUser?->display_name ?? $r->reportedUser?->name ?? 'User' }}</div>
                                    <small class="text-muted">ID: {{ $r->reportedUser?->account_id ?? $r->reported_user_id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-warning">{{ $r->reason }}</div>
                            @if($r->description)
                                <small class="text-muted">{{ Str::limit($r->description, 50) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($r->status === 'pending')
                                <span class="badge bg-danger">Pending</span>
                            @elseif($r->status === 'warning_issued')
                                <span class="badge bg-warning text-dark">Warning Issued</span>
                            @elseif($r->status === 'banned')
                                <span class="badge bg-dark text-danger border border-danger">Suspended</span>
                            @else
                                <span class="badge bg-secondary">Dismissed</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" style="border-radius: 8px;">
                                    Action
                                </button>
                                <ul class="dropdown-menu dropdown-menu-dark">
                                    <li>
                                        <form method="POST" action="{{ route('admin.calls.monitoring.action', $r->id) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="warning">
                                            <button type="submit" class="dropdown-item text-warning">
                                                <i class="fa-solid fa-triangle-exclamation me-1"></i> Issue Official Warning
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('admin.calls.monitoring.action', $r->id) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="ban">
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fa-solid fa-ban me-1"></i> Suspend User
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('admin.calls.monitoring.action', $r->id) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="dismiss">
                                            <button type="submit" class="dropdown-item text-muted">
                                                <i class="fa-solid fa-check me-1"></i> Dismiss Report
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Search & Filter Card -->
    <div class="card mb-4" style="background: #1a162b; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.calls.monitoring') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-muted">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" class="form-control bg-dark text-white border-secondary" placeholder="Search by User ID, 8-Digit Account ID, Name..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="type" class="form-select bg-dark text-white border-secondary">
                        <option value="all" {{ $type === 'all' ? 'selected' : '' }}>All Call Types</option>
                        <option value="video" {{ $type === 'video' ? 'selected' : '' }}>Video Calls Only</option>
                        <option value="audio" {{ $type === 'audio' ? 'selected' : '' }}>Audio Calls Only</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <button type="submit" class="btn btn-primary w-100" style="border-radius: 10px; font-weight: 600;">
                        <i class="fa-solid fa-filter me-1"></i> Filter
                    </button>
                </div>
                <div class="col-12 col-md-2">
                    <a href="{{ route('admin.calls.monitoring') }}" class="btn btn-outline-secondary w-100" style="border-radius: 10px;">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- 1-on-1 Video Call Sessions & Records Table -->
    <div class="card" style="background: #1a162b; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; overflow: hidden;">
        <div class="card-header d-flex justify-content-between align-items-center py-3" style="background: rgba(255,255,255,0.02); border-bottom: 1px solid rgba(255,255,255,0.06);">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-video text-primary"></i>
                <h5 class="mb-0 text-white fw-bold" style="font-size: 15px;">Recorded 1-on-1 Call Sessions</h5>
            </div>
            <span class="text-muted" style="font-size: 13px;">Showing {{ $callSessions->firstItem() ?? 0 }}-{{ $callSessions->lastItem() ?? 0 }} of {{ $callSessions->total() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle text-white mb-0" style="font-size: 13px;">
                <thead style="background: rgba(255,255,255,0.02); color: #94a3b8;">
                    <tr>
                        <th>Call ID</th>
                        <th>Type</th>
                        <th>Caller (User ID)</th>
                        <th>Receiver (Host ID)</th>
                        <th>Duration</th>
                        <th>Coins / Billed</th>
                        <th>Status</th>
                        <th>Timestamp</th>
                        <th class="text-end">Inspection</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($callSessions as $call)
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td class="fw-bold text-info">#{{ $call->id }}</td>
                        <td>
                            @if($call->call_type === 'video')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                    <i class="fa-solid fa-video me-1"></i> Video
                                </span>
                            @else
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                    <i class="fa-solid fa-phone me-1"></i> Audio
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $call->caller?->avatar_url ?? asset('assets/images/default_avatar.png') }}" class="rounded-circle" width="34" height="34" style="object-fit: cover;">
                                <div>
                                    <div class="fw-bold text-white">{{ $call->caller?->display_name ?? $call->caller?->name ?? 'User' }}</div>
                                    <small class="text-muted">ID: {{ $call->caller?->account_id ?? $call->caller_id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $call->receiver?->avatar_url ?? asset('assets/images/default_avatar.png') }}" class="rounded-circle" width="34" height="34" style="object-fit: cover;">
                                <div>
                                    <div class="fw-bold text-pink" style="color: #ec4899;">{{ $call->receiver?->display_name ?? $call->receiver?->name ?? 'Host' }}</div>
                                    <small class="text-muted">ID: {{ $call->receiver?->account_id ?? $call->receiver_id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-dark border border-secondary text-white">
                                <i class="fa-regular fa-clock me-1 text-warning"></i>
                                {{ gmdate('H:i:s', $call->duration_seconds ?? 0) }}
                            </span>
                        </td>
                        <td>
                            <span class="text-warning fw-bold">💎 {{ number_format($call->coins_deducted ?? 0) }}</span>
                        </td>
                        <td>
                            @if($call->status === 'connected')
                                <span class="badge bg-success">Connected</span>
                            @elseif($call->status === 'ended')
                                <span class="badge bg-secondary">Completed</span>
                            @else
                                <span class="badge bg-dark text-muted">{{ ucfirst($call->status) }}</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $call->created_at?->format('d M Y, h:i A') }}</small>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-primary" onclick="inspectCall('{{ $call->id }}', '{{ $call->channel_name }}', '{{ $call->caller?->display_name ?? 'Caller' }}', '{{ $call->receiver?->display_name ?? 'Host' }}', '{{ $call->duration_seconds }}')" style="border-radius: 8px;">
                                <i class="fa-solid fa-eye me-1"></i> Inspect
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-video-slash fa-2x mb-2 d-block"></i>
                            No call records found matching the search criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($callSessions->hasPages())
        <div class="card-footer py-3" style="background: rgba(255,255,255,0.02); border-top: 1px solid rgba(255,255,255,0.06);">
            {{ $callSessions->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Video Call Inspection Modal -->
<div class="modal fade" id="callInspectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: #1a162b; border: 1px solid rgba(255,255,255,0.15); border-radius: 16px; color: #fff;">
            <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,0.08);">
                <h5 class="modal-title fw-bold" id="inspectModalTitle"><i class="fa-solid fa-video text-danger me-2"></i> Call Inspection</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12 col-md-7">
                        <div style="background: #000; border-radius: 12px; height: 280px; display: flex; align-items: center; justify-content: center; flex-direction: column; border: 1px solid rgba(255,255,255,0.1);">
                            <i class="fa-solid fa-video fa-3x text-secondary mb-3"></i>
                            <span class="text-muted" style="font-size: 13px;">1-on-1 VPS Video Stream Stream Recording</span>
                            <small class="text-info mt-2" id="modalChannelName">Channel: call_session</small>
                        </div>
                    </div>
                    <div class="col-12 col-md-5">
                        <h6 class="text-warning fw-bold mb-3"><i class="fa-solid fa-circle-info me-1"></i> Session Details</h6>
                        <ul class="list-group list-group-flush bg-transparent">
                            <li class="list-group-item bg-transparent text-white d-flex justify-content-between px-0 py-2 border-secondary">
                                <span class="text-muted">Caller:</span>
                                <span class="fw-bold" id="modalCaller">--</span>
                            </li>
                            <li class="list-group-item bg-transparent text-white d-flex justify-content-between px-0 py-2 border-secondary">
                                <span class="text-muted">Receiver:</span>
                                <span class="fw-bold" id="modalReceiver">--</span>
                            </li>
                            <li class="list-group-item bg-transparent text-white d-flex justify-content-between px-0 py-2 border-secondary">
                                <span class="text-muted">Duration:</span>
                                <span class="badge bg-warning text-dark" id="modalDuration">0s</span>
                            </li>
                            <li class="list-group-item bg-transparent text-white d-flex justify-content-between px-0 py-2 border-secondary">
                                <span class="text-muted">Engine:</span>
                                <span class="badge bg-primary">Hostinger VPS WebRTC</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.08);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function inspectCall(id, channel, caller, receiver, duration) {
    document.getElementById('inspectModalTitle').innerHTML = '<i class="fa-solid fa-video text-danger me-2"></i> Call #' + id + ' Inspection';
    document.getElementById('modalChannelName').innerText = 'Channel: ' + channel;
    document.getElementById('modalCaller').innerText = caller;
    document.getElementById('modalReceiver').innerText = receiver;
    document.getElementById('modalDuration').innerText = duration + 's';
    
    var myModal = new bootstrap.Modal(document.getElementById('callInspectModal'));
    myModal.show();
}
</script>
@endsection
