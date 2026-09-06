@extends('layouts.admin')

@section('title', 'User & In-Chat Reports')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="page-title fw-bold mb-1" style="font-size: 24px;">
                <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i> User & In-Chat Reports
            </h2>
            <p class="text-muted mb-0" style="font-size: 13px;">
                Review in-chat complaints, abuse reports, sexual content, and scam reports from mobile app users.
            </p>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card-modern p-3 rounded-4 shadow-sm" style="background: var(--card-bg-light); border: 1px solid var(--border-color);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold" style="font-size: 12px; text-transform: uppercase;">Total Reports</span>
                        <h3 class="fw-bold mb-0 mt-1" style="font-size: 24px;">{{ number_format($stats['total']) }}</h3>
                    </div>
                    <div class="stat-icon-box" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fa-solid fa-flag"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card-modern p-3 rounded-4 shadow-sm" style="background: var(--card-bg-light); border: 1px solid var(--border-color);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold" style="font-size: 12px; text-transform: uppercase;">Pending Review</span>
                        <h3 class="fw-bold mb-0 mt-1 text-warning" style="font-size: 24px;">{{ number_format($stats['pending']) }}</h3>
                    </div>
                    <div class="stat-icon-box" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card-modern p-3 rounded-4 shadow-sm" style="background: var(--card-bg-light); border: 1px solid var(--border-color);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold" style="font-size: 12px; text-transform: uppercase;">Resolved & Actions Taken</span>
                        <h3 class="fw-bold mb-0 mt-1 text-success" style="font-size: 24px;">{{ number_format($stats['resolved']) }}</h3>
                    </div>
                    <div class="stat-icon-box" style="background: rgba(16, 185, 129, 0.15); color: #10b981; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card-modern p-3 rounded-4 shadow-sm" style="background: var(--card-bg-light); border: 1px solid var(--border-color);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold" style="font-size: 12px; text-transform: uppercase;">Dismissed</span>
                        <h3 class="fw-bold mb-0 mt-1 text-muted" style="font-size: 24px;">{{ number_format($stats['dismissed']) }}</h3>
                    </div>
                    <div class="stat-icon-box" style="background: rgba(100, 116, 139, 0.15); color: #64748b; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fa-solid fa-ban"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 rounded-4 shadow-sm p-3 mb-4" style="background: var(--card-bg-light);">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name, account ID, phone..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="pending" {{ request('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending Only</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="dismissed" {{ request('status') == 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="reason_type" class="form-select">
                    <option value="">All Reason Categories</option>
                    <option value="child_abuse" {{ request('reason_type') == 'child_abuse' ? 'selected' : '' }}>Child sexual abuse & exploitation</option>
                    <option value="unreasonable_demands" {{ request('reason_type') == 'unreasonable_demands' ? 'selected' : '' }}>Unreasonable demands / Harassment</option>
                    <option value="sexual_content" {{ request('reason_type') == 'sexual_content' ? 'selected' : '' }}>Adult / Sexual content</option>
                    <option value="harassment" {{ request('reason_type') == 'harassment' ? 'selected' : '' }}>Abuse & Hate speech</option>
                    <option value="fraud_scam" {{ request('reason_type') == 'fraud_scam' ? 'selected' : '' }}>Fraud / Scam</option>
                    <option value="other" {{ request('reason_type') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 rounded-3"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-light rounded-3" title="Reset"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>

    <!-- Reports Table Card -->
    <div class="card border-0 rounded-4 shadow-sm overflow-hidden" style="background: var(--card-bg-light);">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead style="background: rgba(0,0,0,0.03); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4">Reported User</th>
                        <th>Reporter</th>
                        <th>Reason Category</th>
                        <th>Details & Proof</th>
                        <th>Status</th>
                        <th>Reported At</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody style="font-size: 13px;">
                    @forelse($reports as $report)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $report->reportedUser?->avatar_url }}" alt="Avatar" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                    <div>
                                        <a href="{{ route('admin.users.show', $report->reported_user_id) }}" class="fw-bold text-decoration-none text-dark d-block">
                                            {{ $report->reportedUser?->display_name ?? 'Unknown' }}
                                        </a>
                                        <small class="text-muted">ID: {{ $report->reportedUser?->account_id }} | {{ $report->reportedUser?->phone }}</small>
                                        @if($report->reportedUser?->is_locked)
                                            <span class="badge bg-danger ms-1" style="font-size: 9px;">BLOCKED</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $report->reporter?->avatar_url }}" alt="Avatar" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                    <div>
                                        <span class="fw-semibold text-dark d-block">{{ $report->reporter?->display_name ?? 'Unknown' }}</span>
                                        <small class="text-muted">ID: {{ $report->reporter?->account_id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-danger-subtle text-danger fw-bold px-2 py-1 rounded-pill" style="font-size: 11px;">
                                    {{ $report->reason_title ?? $report->reason_type }}
                                </span>
                            </td>
                            <td style="max-width: 250px;">
                                <div class="text-truncate" title="{{ $report->description }}">
                                    {{ $report->description ?: 'No additional notes provided.' }}
                                </div>
                                @if($report->proof_image)
                                    <a href="{{ $report->proof_image_full_url }}" target="_blank" class="badge bg-info-subtle text-info text-decoration-none mt-1 d-inline-block">
                                        <i class="fa-solid fa-image me-1"></i> View Screenshot
                                    </a>
                                @endif
                            </td>
                            <td>
                                @if($report->status === 'pending')
                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i> Pending</span>
                                @elseif($report->status === 'resolved')
                                    <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Resolved</span>
                                @elseif($report->status === 'dismissed')
                                    <span class="badge bg-secondary">Dismissed</span>
                                @else
                                    <span class="badge bg-info">{{ ucfirst($report->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $report->created_at->format('M d, Y h:i A') }}</small>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <!-- Block & Resolve Action -->
                                    @if(!$report->reportedUser?->is_locked && $report->status !== 'resolved')
                                        <form action="{{ route('admin.reports.block-and-resolve', $report->id) }}" method="POST" onsubmit="return confirm('Block this user immediately and resolve report?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger rounded-3" title="Block User & Resolve">
                                                <i class="fa-solid fa-ban me-1"></i> Block User
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Dismiss Action -->
                                    @if($report->status === 'pending')
                                        <form action="{{ route('admin.reports.update-status', $report->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="dismissed">
                                            <button type="submit" class="btn btn-sm btn-light border rounded-3" title="Dismiss Report">
                                                <i class="fa-solid fa-xmark text-muted"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-shield-halved fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                                <h5 class="fw-bold">No User Reports Found</h5>
                                <p class="small mb-0">There are no reports matching your active filter criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reports->hasPages())
            <div class="p-3 border-top">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
