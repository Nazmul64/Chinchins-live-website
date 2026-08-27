@extends('layouts.admin')

@section('title', 'KYC Review #' . $kyc->id . ' - ' . $kyc->full_name)

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <a href="{{ route('admin.kyc.index') }}" class="text-muted text-decoration-none" style="font-size: 13px;">KYC Verifications</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Review #{{ $kyc->id }}</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-id-card text-info"></i>
                <span>Review Identity Verification: {{ $kyc->full_name }}</span>
            </h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.kyc.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to KYC List
            </a>
        </div>
    </div>

    <!-- Review Content -->
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="custom-card p-4 mb-4">
                <h5 class="fw-bold mb-3" style="color: var(--text-primary);">User Profile</h5>
                @if($kyc->user)
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="{{ $kyc->user->avatar_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($kyc->user->display_name) . '&background=3b82f6&color=fff' }}" 
                             alt="{{ $kyc->user->display_name }}" class="rounded-circle" style="width: 60px; height: 60px; object-fit: cover;">
                        <div>
                            <h6 class="fw-bold mb-0" style="color: var(--text-primary);">{{ $kyc->user->display_name }}</h6>
                            <div class="text-muted font-monospace" style="font-size: 12px;">ID: {{ $kyc->user->account_id }}</div>
                            <div class="text-muted" style="font-size: 12px;">Phone: {{ $kyc->user->phone ?: 'N/A' }}</div>
                        </div>
                    </div>
                    <a href="{{ route('admin.users.show', $kyc->user->id) }}" class="btn btn-sm btn-outline-primary w-100" style="border-radius: 8px;">
                        <i class="fa-solid fa-user me-1"></i> View Full User Profile
                    </a>
                @endif
            </div>

            <div class="custom-card p-4">
                <h5 class="fw-bold mb-3" style="color: var(--text-primary);">Submission Details</h5>
                <ul class="list-group list-group-flush" style="font-size: 13px;">
                    <li class="list-group-item d-flex justify-content-between px-0 bg-transparent" style="color: var(--text-primary);">
                        <span class="text-muted">Document Type:</span>
                        <span class="fw-bold">{{ $kyc->document_type_label }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 bg-transparent" style="color: var(--text-primary);">
                        <span class="text-muted">Document Number:</span>
                        <span class="fw-bold font-monospace">{{ $kyc->document_number }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 bg-transparent" style="color: var(--text-primary);">
                        <span class="text-muted">Date of Birth:</span>
                        <span class="fw-bold">{{ $kyc->date_of_birth ? $kyc->date_of_birth->format('M d, Y') : 'N/A' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 bg-transparent" style="color: var(--text-primary);">
                        <span class="text-muted">Status:</span>
                        <span>
                            @if($kyc->status === 'approved')
                                <span class="badge bg-success">Verified</span>
                            @elseif($kyc->status === 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 bg-transparent" style="color: var(--text-primary);">
                        <span class="text-muted">Submitted At:</span>
                        <span>{{ $kyc->submitted_at?->format('M d, Y h:i A') }}</span>
                    </li>
                </ul>

                <!-- Action Form -->
                <div class="mt-4 pt-3 border-top">
                    @if($kyc->status === 'pending')
                        <div class="d-grid gap-2">
                            <form action="{{ route('admin.kyc.approve', $kyc->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100" style="border-radius: 10px; font-weight: 600;">
                                    <i class="fa-solid fa-circle-check me-1"></i> Approve & Verify
                                </button>
                            </form>
                            <button type="button" class="btn btn-outline-danger w-100" style="border-radius: 10px;" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="fa-solid fa-ban me-1"></i> Reject
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="custom-card p-4 mb-4">
                <h5 class="fw-bold mb-3" style="color: var(--text-primary);">Document Images & Face Video Recording</h5>
                <div class="row g-3">
                    @if($kyc->face_video_url)
                        <div class="col-12">
                            <div class="p-3 border rounded text-center" style="background: rgba(139, 92, 246, 0.1); border-color: rgba(139, 92, 246, 0.4) !important;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-purple text-white fw-bold" style="background: #8b5cf6;">
                                        <i class="fa-solid fa-video me-1"></i> Live Face Scan Video Recording
                                    </span>
                                    <a href="{{ $kyc->face_video_url }}" target="_blank" class="btn btn-sm btn-outline-light">
                                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open Video
                                    </a>
                                </div>
                                <video controls playsinline class="rounded w-100" style="max-height: 320px; background: #000;">
                                    <source src="{{ $kyc->face_video_url }}" type="video/mp4">
                                    Your browser does not support HTML5 video.
                                </video>
                            </div>
                        </div>
                    @endif

                    <div class="col-md-6">
                        <div class="p-2 border rounded text-center bg-dark">
                            <span class="badge bg-primary mb-2">Front Side</span>
                            <a href="{{ $kyc->front_image_url }}" target="_blank">
                                <img src="{{ $kyc->front_image_url }}" alt="Front" class="img-fluid rounded" style="max-height: 260px; object-fit: contain;">
                            </a>
                        </div>
                    </div>
                    @if($kyc->back_image_url)
                        <div class="col-md-6">
                            <div class="p-2 border rounded text-center bg-dark">
                                <span class="badge bg-secondary mb-2">Back Side</span>
                                <a href="{{ $kyc->back_image_url }}" target="_blank">
                                    <img src="{{ $kyc->back_image_url }}" alt="Back" class="img-fluid rounded" style="max-height: 260px; object-fit: contain;">
                                </a>
                            </div>
                        </div>
                    @endif
                    <div class="col-12">
                        <div class="p-2 border rounded text-center bg-dark">
                            <span class="badge bg-danger mb-2">1. Live Selfie / Center Face</span>
                            <a href="{{ $kyc->selfie_image_url }}" target="_blank">
                                <img src="{{ $kyc->selfie_image_url }}" alt="Selfie" class="img-fluid rounded" style="max-height: 280px; object-fit: contain;">
                            </a>
                        </div>
                    </div>

                    @if($kyc->face_left_image_url)
                        <div class="col-md-4">
                            <div class="p-2 border rounded text-center bg-dark">
                                <span class="badge bg-info mb-2">2. Left Side Face (Turn Left)</span>
                                <a href="{{ $kyc->face_left_image_url }}" target="_blank">
                                    <img src="{{ $kyc->face_left_image_url }}" alt="Left Face" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;">
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($kyc->face_right_image_url)
                        <div class="col-md-4">
                            <div class="p-2 border rounded text-center bg-dark">
                                <span class="badge bg-warning text-dark mb-2">3. Right Side Face (Turn Right)</span>
                                <a href="{{ $kyc->face_right_image_url }}" target="_blank">
                                    <img src="{{ $kyc->face_right_image_url }}" alt="Right Face" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;">
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($kyc->face_blink_image_url)
                        <div class="col-md-4">
                            <div class="p-2 border rounded text-center bg-dark">
                                <span class="badge bg-success mb-2">4. Eye Blink / Liveness Scan</span>
                                <a href="{{ $kyc->face_blink_image_url }}" target="_blank">
                                    <img src="{{ $kyc->face_blink_image_url }}" alt="Blink Face" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;">
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.kyc.reject', $kyc->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Reject KYC Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Reason for rejection *</label>
                    <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Provide reason for rejection..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Confirm Reject</button>
            </div>
        </form>
    </div>
</div>
@endsection
