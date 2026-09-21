@extends('layouts.admin')

@section('title', 'Send Push Notification')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Main Card Container -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <!-- Card Header Banner -->
        <div class="card-header border-0 py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2" style="background: #e6f9ee;">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-paper-plane text-success fs-5"></i>
                <h5 class="fw-bold mb-0 text-dark">Send Push Notification (OneSignal & Firebase)</h5>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-xs fw-semibold small">
                    <i class="fa-solid fa-fire text-warning me-1"></i> Firebase: {{ $firebaseCount }}
                </span>
                <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-xs fw-semibold small">
                    <i class="fa-solid fa-bell text-primary me-1"></i> OneSignal: {{ $oneSignalCount }}
                </span>
                <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-xs fw-semibold small">
                    <i class="fa-solid fa-users text-info me-1"></i> Total: {{ $totalCount }}
                </span>
                <a href="{{ route('admin.firebase.history') }}" class="btn btn-outline-dark btn-sm rounded-pill px-3 py-1 fw-semibold d-flex align-items-center gap-1">
                    <i class="fa-solid fa-clock-rotate-left"></i> History
                </a>
            </div>
        </div>

        <!-- Notification Form -->
        <div class="card-body p-4">
            <form action="{{ route('admin.firebase.send.submit') }}" method="POST" id="pushNotificationForm">
                @csrf

                <!-- Notification Platform -->
                <div class="mb-4">
                    <label class="form-label fw-bold small text-dark d-flex align-items-center gap-2 mb-2">
                        <i class="fa-solid fa-tower-broadcast text-primary"></i> Notification Platform <span class="text-danger">*</span>
                    </label>
                    <div class="d-flex align-items-center gap-4 flex-wrap">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="platform" id="platformOneSignal" value="onesignal">
                            <label class="form-check-label fw-semibold" for="platformOneSignal">
                                <i class="fa-solid fa-bell text-danger me-1"></i> OneSignal (Instant Broadcast)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="platform" id="platformFirebase" value="firebase" checked>
                            <label class="form-check-label fw-semibold" for="platformFirebase">
                                <i class="fa-solid fa-fire text-warning me-1"></i> Firebase FCM
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="platform" id="platformBoth" value="both">
                            <label class="form-check-label fw-semibold" for="platformBoth">
                                <i class="fa-solid fa-arrows-rotate text-success me-1"></i> Both (OneSignal + Firebase)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Select Firebase App -->
                <div class="mb-4" id="firebaseAppSelectGroup">
                    <label class="form-label fw-semibold small text-dark mb-1">
                        Select Firebase App <span class="text-danger">*</span>
                    </label>
                    <select name="firebase_app_id" class="form-select rounded-3 py-2">
                        <option value="">Choose a Firebase App...</option>
                        @foreach($apps as $app)
                            <option value="{{ $app->id }}" {{ $loop->first ? 'selected' : '' }}>
                                {{ $app->app_name }} ({{ $app->package_name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Notification Title -->
                <div class="mb-4">
                    <label class="form-label fw-semibold small text-dark mb-1">
                        Notification Title <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="title" id="notificationTitle" class="form-control rounded-3 py-2" placeholder="e.g., Important Update & New Offer Available!" maxlength="255" required>
                    <div class="form-text text-muted small mt-1">Max 255 characters</div>
                </div>

                <!-- Notification Message -->
                <div class="mb-4">
                    <label class="form-label fw-semibold small text-dark mb-1">
                        Notification Message <span class="text-danger">*</span>
                    </label>
                    <textarea name="message" id="notificationMessage" class="form-control rounded-3" rows="4" placeholder="Write your notification message here..." maxlength="1000" required></textarea>
                    <div class="form-text text-muted small mt-1">Max 1000 characters</div>
                </div>

                <!-- Image URL (Optional) -->
                <div class="mb-4">
                    <label class="form-label fw-semibold small text-dark mb-1">
                        Image URL (Optional)
                    </label>
                    <input type="url" name="image_url" class="form-control rounded-3 py-2" placeholder="https://example.com/image.jpg">
                    <div class="form-text text-muted small mt-1">
                        <i class="fa-solid fa-circle-info me-1"></i> Add an image URL to show banner image with notification
                    </div>
                </div>

                <!-- Action URL (Optional) -->
                <div class="mb-4">
                    <label class="form-label fw-semibold small text-dark mb-1">
                        Action URL (Optional)
                    </label>
                    <input type="text" name="action_url" class="form-control rounded-3 py-2" placeholder="/notifications" value="/notifications">
                    <div class="form-text text-muted small mt-1">
                        <i class="fa-solid fa-circle-info me-1"></i> Screen or URL opened when user taps notification
                    </div>
                </div>

                <!-- Send To -->
                <div class="mb-4">
                    <label class="form-label fw-bold small text-dark mb-2">
                        Send To <span class="text-danger">*</span>
                    </label>
                    <div class="d-flex align-items-center gap-4 flex-wrap mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="send_to" id="sendToAll" value="all" checked onchange="toggleUserSelection(false)">
                            <label class="form-check-label fw-semibold" for="sendToAll">
                                <i class="fa-solid fa-users text-primary me-1"></i> All App Users (Broadcast)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="send_to" id="sendToSpecific" value="specific" onchange="toggleUserSelection(true)">
                            <label class="form-check-label fw-semibold" for="sendToSpecific">
                                <i class="fa-solid fa-user-check text-warning me-1"></i> Specific Users
                            </label>
                        </div>
                    </div>

                    <!-- Specific User Multi-Select (Hidden by default) -->
                    <div id="specificUsersGroup" class="d-none bg-light p-3 rounded-3 border">
                        <label class="form-label fw-semibold small text-dark">Select Target Users:</label>
                        <select name="user_ids[]" class="form-select" multiple style="min-height: 120px;">
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->display_name ?: $u->name }} (ID: {{ $u->account_id ?: $u->id }} | {{ $u->email ?: 'No email' }})</option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted small mt-1">Hold Ctrl (or Cmd) to select multiple users.</div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-success px-4 py-2 rounded-3 fw-bold d-flex align-items-center gap-2 shadow-sm" style="background-color: #22c55e; border: none;">
                        <i class="fa-solid fa-paper-plane"></i> Send Push Notification
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Recent Dispatches Table Card (Screenshot 3) -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-0">
            <h6 class="fw-bold mb-0 text-dark">Recent Notifications Log</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Date & Time</th>
                        <th>Platform</th>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Send To</th>
                        <th>Sent</th>
                        <th>Failed</th>
                        <th class="pe-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentNotifications as $item)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark">{{ $item->created_at ? $item->created_at->format('d M, Y') : 'N/A' }}</div>
                            <small class="text-muted">{{ $item->created_at ? $item->created_at->format('h:i A') : '' }}</small>
                        </td>
                        <td>
                            @if($item->platform === 'firebase')
                                <span class="badge bg-warning text-dark fw-bold px-2 py-1 rounded">
                                    <i class="fa-solid fa-bell me-1"></i> FIREBASE
                                </span>
                            @elseif($item->platform === 'onesignal')
                                <span class="badge bg-danger text-white fw-bold px-2 py-1 rounded">
                                    <i class="fa-solid fa-tower-broadcast me-1"></i> ONESIGNAL
                                </span>
                            @else
                                <span class="badge bg-success text-white fw-bold px-2 py-1 rounded">
                                    <i class="fa-solid fa-arrows-rotate me-1"></i> BOTH
                                </span>
                            @endif
                        </td>
                        <td class="fw-bold text-dark" style="max-width: 180px;">
                            <div class="text-truncate">{{ $item->title }}</div>
                        </td>
                        <td style="max-width: 250px;">
                            <div class="text-truncate text-muted">{{ $item->message }}</div>
                        </td>
                        <td>
                            @if($item->send_to === 'all')
                                <span class="badge bg-primary px-2 py-1 rounded">All</span>
                            @else
                                <span class="badge bg-info text-dark px-2 py-1 rounded">Specific</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-success text-white px-2 py-1 rounded">
                                <i class="fa-solid fa-check"></i> {{ $item->sent_count }}
                            </span>
                        </td>
                        <td>
                            @if($item->failed_count > 0)
                                <span class="badge bg-danger text-white px-2 py-1 rounded">
                                    <i class="fa-solid fa-xmark"></i> {{ $item->failed_count }}
                                </span>
                            @else
                                <span class="text-muted small">0</span>
                            @endif
                        </td>
                        <td class="pe-4">
                            @if($item->status === 'delivered')
                                <span class="badge bg-success text-white px-3 py-1 rounded-pill fw-semibold">Delivered</span>
                            @elseif($item->status === 'partial')
                                <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-semibold">Partial</span>
                            @else
                                <span class="badge bg-danger text-white px-3 py-1 rounded-pill fw-semibold">Failed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted small">
                            No notifications sent yet. Use the form above to send your first push broadcast!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleUserSelection(isSpecific) {
    const group = document.getElementById('specificUsersGroup');
    if (isSpecific) {
        group.classList.remove('d-none');
    } else {
        group.classList.add('d-none');
    }
}
</script>
@endsection
