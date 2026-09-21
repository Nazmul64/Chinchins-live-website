@extends('layouts.admin')

@section('title', 'Notification History & Reports')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-primary"></i> Notification History & Reports
            </h4>
            <p class="text-muted small mb-0">View historical push notification dispatches, success rates, and delivery logs.</p>
        </div>
        <div>
            <a href="{{ route('admin.firebase.send') }}" class="btn btn-primary d-flex align-items-center gap-2 px-3 py-2 rounded-3 shadow-sm">
                <i class="fa-solid fa-plus"></i> Send New
            </a>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
        <form action="{{ route('admin.firebase.history') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4 col-sm-12">
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
            <div class="col-md-3 col-sm-6">
                <label class="form-label fw-semibold small text-muted mb-1">Date From</label>
                <input type="date" name="date_from" class="form-control rounded-3" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label fw-semibold small text-muted mb-1">Date To</label>
                <input type="date" name="date_to" class="form-control rounded-3" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 col-sm-12">
                <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Notifications Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4" style="width: 50px;">#</th>
                        <th>Date & Time</th>
                        <th>App</th>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Send To</th>
                        <th>Sent</th>
                        <th>Failed</th>
                        <th>Success Rate</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $index => $item)
                    <tr>
                        <td class="ps-4 fw-bold text-muted">{{ $notifications->firstItem() + $index }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $item->created_at ? $item->created_at->format('d M, Y') : 'N/A' }}</div>
                            <small class="text-muted">{{ $item->created_at ? $item->created_at->format('h:i A') : '' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-75 text-white px-2 py-1 rounded">
                                {{ $item->firebaseApp ? $item->firebaseApp->app_name : 'globalmoneyltd' }}
                            </span>
                        </td>
                        <td class="fw-bold text-dark" style="max-width: 150px;">
                            <div class="text-truncate">{{ $item->title }}</div>
                        </td>
                        <td style="max-width: 220px;">
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
                        <td style="width: 140px;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 10px; border-radius: 6px; background-color: #e2e8f0;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $item->success_rate }}%;" aria-valuenow="{{ $item->success_rate }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="small fw-bold text-dark">{{ $item->success_rate }}%</span>
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 d-flex align-items-center gap-1 ms-auto" data-bs-toggle="modal" data-bs-target="#viewNotificationModal{{ $item->id }}">
                                <i class="fa-solid fa-eye"></i> View
                            </button>
                        </td>
                    </tr>

                    <!-- Details Modal -->
                    <div class="modal fade" id="viewNotificationModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4 border-0 shadow">
                                <div class="modal-header bg-light">
                                    <h5 class="modal-title fw-bold">Notification Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="mb-3">
                                        <label class="text-muted small fw-bold">Title</label>
                                        <p class="fw-bold fs-6 mb-0">{{ $item->title }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small fw-bold">Message Content</label>
                                        <p class="mb-0 bg-light p-3 rounded-3">{{ $item->message }}</p>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <label class="text-muted small fw-bold">Platform</label>
                                            <p class="mb-0 text-uppercase fw-semibold">{{ $item->platform }}</p>
                                        </div>
                                        <div class="col-6">
                                            <label class="text-muted small fw-bold">Status</label>
                                            <p class="mb-0"><span class="badge bg-success">{{ ucfirst($item->status) }}</span></p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <label class="text-muted small fw-bold">Sent Count</label>
                                            <p class="mb-0 text-success fw-bold">{{ $item->sent_count }}</p>
                                        </div>
                                        <div class="col-6">
                                            <label class="text-muted small fw-bold">Failed Count</label>
                                            <p class="mb-0 text-danger fw-bold">{{ $item->failed_count }}</p>
                                        </div>
                                    </div>
                                    @if($item->image_url)
                                    <div class="mb-3">
                                        <label class="text-muted small fw-bold">Banner Image</label>
                                        <div><img src="{{ $item->image_url }}" alt="Banner" class="img-fluid rounded-3" style="max-height: 150px;"></div>
                                    </div>
                                    @endif
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted small">
                            No notifications match your filter criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($notifications->hasPages())
        <div class="p-3 border-top d-flex justify-content-end">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>

    <!-- Summary Statistics Cards (Screenshot 4 bottom) -->
    <div class="row g-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white">
                <h2 class="fw-bolder text-primary mb-1">{{ $totalNotifications }}</h2>
                <div class="text-muted fw-semibold small">Total Notifications</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white">
                <h2 class="fw-bolder text-success mb-1">{{ $totalSent }}</h2>
                <div class="text-muted fw-semibold small">Total Sent</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white">
                <h2 class="fw-bolder text-danger mb-1">{{ $totalFailed }}</h2>
                <div class="text-muted fw-semibold small">Total Failed</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white">
                <h2 class="fw-bolder text-info mb-1">{{ $avgSuccessRate }}%</h2>
                <div class="text-muted fw-semibold small">Avg Success Rate</div>
            </div>
        </div>
    </div>
</div>
@endsection
