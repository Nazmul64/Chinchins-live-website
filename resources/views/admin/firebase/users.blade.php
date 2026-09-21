@extends('layouts.admin')

@section('title', 'Users with FCM Tokens')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Top Statistics Cards (Screenshot 5 top) -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 text-center bg-white">
                <div class="d-flex justify-content-center mb-2">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center text-primary" style="width: 44px; height: 44px;">
                        <i class="fa-solid fa-users fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bolder text-primary mb-1">{{ $totalUsersWithFcm }}</h3>
                <div class="text-muted fw-semibold small">Total Users with FCM</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 text-center bg-white">
                <div class="d-flex justify-content-center mb-2">
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center text-success" style="width: 44px; height: 44px;">
                        <i class="fa-solid fa-circle-check fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bolder text-success mb-1">{{ $activeTokens }}</h3>
                <div class="text-muted fw-semibold small">Active Tokens</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 text-center bg-white">
                <div class="d-flex justify-content-center mb-2">
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center text-success" style="width: 44px; height: 44px;">
                        <i class="fa-brands fa-android fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bolder text-success mb-1">{{ $androidUsers }}</h3>
                <div class="text-muted fw-semibold small">Android Users</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 text-center bg-white">
                <div class="d-flex justify-content-center mb-2">
                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center text-secondary" style="width: 44px; height: 44px;">
                        <i class="fa-brands fa-apple fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bolder text-secondary mb-1">{{ $iosUsers }}</h3>
                <div class="text-muted fw-semibold small">iOS Users</div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <!-- Card Header with Send Notification Button -->
        <div class="card-header border-0 py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2" style="background: #eef2ff;">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-users text-primary fs-5"></i>
                <h5 class="fw-bold mb-0 text-dark">Users with FCM Tokens</h5>
            </div>
            <div>
                <a href="{{ route('admin.firebase.send') }}" class="btn btn-outline-dark btn-sm rounded-3 px-3 py-2 fw-semibold d-flex align-items-center gap-2 bg-white">
                    <i class="fa-solid fa-paper-plane text-primary"></i> Send Notification
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="p-3 bg-white border-bottom">
            <form action="{{ route('admin.firebase.users') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-md-3 col-sm-12">
                    <label class="form-label fw-semibold small text-muted mb-1">Firebase App</label>
                    <select name="app_id" class="form-select rounded-3">
                        <option value="">All Apps</option>
                        @foreach($apps as $app)
                            <option value="{{ $app->id }}" {{ request('app_id') == $app->id ? 'selected' : '' }}>
                                {{ $app->app_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-12">
                    <label class="form-label fw-semibold small text-muted mb-1">Device Type</label>
                    <select name="device_type" class="form-select rounded-3">
                        <option value="all">All Devices</option>
                        <option value="android" {{ request('device_type') == 'android' ? 'selected' : '' }}>Android</option>
                        <option value="ios" {{ request('device_type') == 'ios' ? 'selected' : '' }}>iOS</option>
                        <option value="web" {{ request('device_type') == 'web' ? 'selected' : '' }}>Web</option>
                    </select>
                </div>
                <div class="col-md-4 col-sm-12">
                    <label class="form-label fw-semibold small text-muted mb-1">Search</label>
                    <input type="text" name="search" class="form-control rounded-3" placeholder="Name or email..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 col-sm-12 align-self-end">
                    <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                        <i class="fa-solid fa-magnifying-glass"></i> Search
                    </button>
                </div>
            </form>
        </div>

        <!-- Devices Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4" style="width: 50px;">#</th>
                        <th>User Info</th>
                        <th>Firebase App</th>
                        <th>Device</th>
                        <th>FCM Status</th>
                        <th>Last Updated</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devices as $index => $device)
                    @php
                        $user = $device->user;
                        $initials = strtoupper(substr($user ? ($user->display_name ?: $user->name ?: 'User') : 'D', 0, 2));
                        $colors = ['#8b5cf6', '#ec4899', '#3b82f6', '#10b981', '#f59e0b', '#06b6d4'];
                        $color = $colors[($device->id % count($colors))];
                    @endphp
                    <tr>
                        <td class="ps-4 fw-bold text-muted">{{ $devices->firstItem() + $index }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($user && ($user->avatar_url || $user->profile_image))
                                    <img src="{{ $user->avatar_url ?: $user->profile_image }}" alt="Avatar" class="rounded-circle object-fit-cover shadow-xs" style="width: 36px; height: 36px;">
                                @else
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-xs small" style="width: 36px; height: 36px; background-color: {{ $color }};">
                                        {{ $initials }}
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark">{{ $user ? ($user->display_name ?: $user->name) : 'Guest Device #' . $device->id }}</div>
                                    <small class="text-muted">{{ $user ? ($user->email ?: 'ID: ' . ($user->account_id ?: $user->id)) : ($device->device_model ?: 'Android Device') }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-75 text-white px-2 py-1 rounded">
                                {{ $device->firebaseApp ? $device->firebaseApp->app_name : 'globalmoneyltd' }}
                            </span>
                        </td>
                        <td>
                            @if(strtolower($device->device_type) === 'ios')
                                <span class="badge bg-dark text-white px-2 py-1 rounded">
                                    <i class="fa-brands fa-apple me-1"></i> iOS
                                </span>
                            @elseif(strtolower($device->device_type) === 'web')
                                <span class="badge bg-info text-white px-2 py-1 rounded">
                                    <i class="fa-solid fa-globe me-1"></i> Web
                                </span>
                            @else
                                <span class="badge bg-success text-white px-2 py-1 rounded">
                                    <i class="fa-brands fa-android me-1"></i> Android
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($device->is_active && !empty($device->fcm_token))
                                <span class="badge bg-success px-2 py-1 rounded-pill">
                                    <i class="fa-solid fa-check me-1"></i> Active
                                </span>
                            @else
                                <span class="badge bg-secondary px-2 py-1 rounded-pill">Inactive</span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            {{ $device->last_active_at ? $device->last_active_at->diffForHumans() : 'Never' }}
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewDeviceModal{{ $device->id }}" title="View Token & Device">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                @if($user)
                                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#sendDirectModal{{ $device->id }}" title="Send Direct Push">
                                    <i class="fa-solid fa-paper-plane"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- View Device Details Modal -->
                    <div class="modal fade" id="viewDeviceModal{{ $device->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4 border-0 shadow">
                                <div class="modal-header bg-light">
                                    <h5 class="modal-title fw-bold">Device & FCM Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="mb-3">
                                        <label class="text-muted small fw-bold">User</label>
                                        <p class="fw-bold fs-6 mb-0">{{ $user ? ($user->name . ' (' . ($user->email ?: $user->phone) . ')') : 'Unassigned' }}</p>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <label class="text-muted small fw-bold">Device Brand / Model</label>
                                            <p class="mb-0">{{ $device->device_brand ?: 'N/A' }} {{ $device->device_model ?: '' }}</p>
                                        </div>
                                        <div class="col-6">
                                            <label class="text-muted small fw-bold">OS Version</label>
                                            <p class="mb-0">{{ $device->os_version ?: 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small fw-bold">Device ID / UID</label>
                                        <p class="mb-0 font-monospace small bg-light p-2 rounded">{{ $device->device_id ?: 'None' }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small fw-bold">FCM Registration Token</label>
                                        <textarea class="form-control font-monospace small" rows="3" readonly>{{ $device->fcm_token }}</textarea>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Direct Push Notification Modal -->
                    @if($user)
                    <div class="modal fade" id="sendDirectModal{{ $device->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4 border-0 shadow">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title fw-bold">Send Push to {{ $user->display_name ?: $user->name }}</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('admin.firebase.users.send', $user->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-body p-4">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Notification Title <span class="text-danger">*</span></label>
                                            <input type="text" name="title" class="form-control rounded-3" placeholder="Hello {{ $user->display_name ?: $user->name }}!" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Notification Message <span class="text-danger">*</span></label>
                                            <textarea name="message" class="form-control rounded-3" rows="3" placeholder="Enter custom message..." required></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Image URL (Optional)</label>
                                            <input type="url" name="image_url" class="form-control rounded-3" placeholder="https://...">
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa-solid fa-paper-plane me-1"></i> Send Now</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted small">
                            No devices registered with FCM tokens yet. Once users log in from the Flutter mobile app, their device tokens will appear here automatically.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($devices->hasPages())
        <div class="p-3 border-top d-flex justify-content-end">
            {{ $devices->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
