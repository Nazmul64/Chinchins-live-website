@extends('layouts.admin')

@section('title', 'KYC Identity Verification')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">KYC Verification</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-id-card text-info"></i>
                <span>Identity Verification (KYC)</span>
            </h1>
            <p class="page-subtitle">Review official identity documents (NID Card, Passport, Birth Certificate) & live selfie scans. Approving an identity grants the verified blue checkmark on profile.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-users text-primary me-1"></i> User Directory
            </a>
            <a href="{{ route('admin.profile') }}" class="btn-ch-primary">
                <i class="fa-solid fa-mobile-screen"></i> Live App Preview
            </a>
        </div>
    </div>

    <!-- Metric Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #f59e0b;">
                <div class="stat-icon-box stat-icon-gold">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Pending Reviews</span>
                    <h3 class="stat-count-value" style="color: #d97706;">{{ number_format($stats['pending']) }}</h3>
                    @if($stats['pending'] > 0)
                        <span class="stat-badge-chip" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                            <span class="status-pulsing-dot"></span> Action Required
                        </span>
                    @else
                        <span class="stat-badge-chip" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                            <i class="fa-solid fa-check"></i> All Caught Up
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card">
                <div class="stat-icon-box stat-icon-green">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Verified Streamers</span>
                    <h3 class="stat-count-value" style="color: #10b981;">{{ number_format($stats['approved']) }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i class="fa-solid fa-shield-halved"></i> Active Badges
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card">
                <div class="stat-icon-box stat-icon-blue">
                    <i class="fa-solid fa-passport"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Total Submissions</span>
                    <h3 class="stat-count-value">{{ number_format($stats['total']) }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                        <i class="fa-solid fa-database"></i> All Records
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card">
                <div class="stat-icon-box" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-title-label">Rejected / Invalid</span>
                    <h3 class="stat-count-value" style="color: #ef4444;">{{ number_format($stats['rejected']) }}</h3>
                    <span class="stat-badge-chip" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                        <i class="fa-solid fa-ban"></i> Declined
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Navigation Tabs & Search -->
    <div class="filter-card-wrapper">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <!-- Filter Tabs -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('admin.kyc.index', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}" 
                   class="filter-tab-pill {{ $status === 'all' ? 'active' : '' }}">
                    <span>All Records</span>
                    <span class="tab-badge">{{ $stats['total'] }}</span>
                </a>
                <a href="{{ route('admin.kyc.index', array_merge(request()->except('status', 'page'), ['status' => 'pending'])) }}" 
                   class="filter-tab-pill {{ $status === 'pending' ? 'active' : '' }}" style="{{ $stats['pending'] > 0 ? 'border-color: #f59e0b;' : '' }}">
                    <span>Pending</span>
                    <span class="tab-badge bg-warning text-dark">{{ $stats['pending'] }}</span>
                </a>
                <a href="{{ route('admin.kyc.index', array_merge(request()->except('status', 'page'), ['status' => 'approved'])) }}" 
                   class="filter-tab-pill {{ $status === 'approved' ? 'active' : '' }}">
                    <span>Approved / Verified</span>
                    <span class="tab-badge bg-success">{{ $stats['approved'] }}</span>
                </a>
                <a href="{{ route('admin.kyc.index', array_merge(request()->except('status', 'page'), ['status' => 'rejected'])) }}" 
                   class="filter-tab-pill {{ $status === 'rejected' ? 'active' : '' }}">
                    <span>Rejected</span>
                    <span class="tab-badge bg-danger">{{ $stats['rejected'] }}</span>
                </a>
            </div>

            <!-- Document Type Filter & Search Bar -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Document Type Select -->
                <form action="{{ route('admin.kyc.index') }}" method="GET" class="d-flex align-items-center gap-2" id="filterForm">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <select name="document_type" class="form-select form-select-sm" style="border-radius: 10px; font-size: 13px; min-width: 170px;" onchange="this.form.submit()">
                        <option value="all" {{ $docType === 'all' ? 'selected' : '' }}>📄 All Documents</option>
                        <option value="nid" {{ $docType === 'nid' ? 'selected' : '' }}>🪪 NID Card ({{ $stats['nid_count'] }})</option>
                        <option value="passport" {{ $docType === 'passport' ? 'selected' : '' }}>🛂 Passport ({{ $stats['passport_count'] }})</option>
                        <option value="birth_certificate" {{ $docType === 'birth_certificate' ? 'selected' : '' }}>📜 Birth Cert ({{ $stats['birth_cert_count'] }})</option>
                    </select>

                    <div class="search-input-group" style="position: relative; min-width: 250px;">
                        <i class="fa-solid fa-magnifying-glass search-icon" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 13px;"></i>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               class="form-control form-control-sm" 
                               placeholder="Search Name, NID, Phone..." 
                               style="padding-left: 32px; border-radius: 10px; font-size: 13px;">
                        @if(request('search') || request('document_type') !== 'all')
                            <a href="{{ route('admin.kyc.index', ['status' => $status]) }}" class="clear-search-btn" title="Reset Filters" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted); text-decoration: none;">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Data Table Card -->
    <div class="custom-card">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>User Account</th>
                        <th>Doc Type</th>
                        <th>Legal Name & ID Number</th>
                        <th>Document & Selfie Preview</th>
                        <th>Status</th>
                        <th>Submitted At</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kycList as $item)
                        <tr>
                            <!-- User Account Column -->
                            <td>
                                @if($item->user)
                                    <div class="user-avatar-group">
                                        <div class="user-avatar-wrapper">
                                            <img src="{{ $item->user->avatar_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($item->user->display_name) . '&background=3b82f6&color=fff' }}" 
                                                 alt="{{ $item->user->display_name }}" 
                                                 class="user-avatar-img">
                                            <span class="online-pulse-dot {{ $item->user->is_active ? 'online' : 'offline' }}"></span>
                                        </div>
                                        <div>
                                            <div class="user-name-title">
                                                <a href="{{ route('admin.users.show', $item->user->id) }}" class="text-decoration-none" style="color: var(--text-primary);">
                                                    {{ $item->user->display_name }}
                                                </a>
                                                @if($item->user->is_verified)
                                                    <i class="fa-solid fa-circle-check text-primary" title="Verified Streamer" style="font-size: 13px;"></i>
                                                @endif
                                            </div>
                                            <div class="user-sub-info">
                                                <span class="font-monospace">{{ $item->user->account_id }}</span> &bull; {{ $item->user->phone ?: 'No phone' }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-danger">Deleted User (#{{ $item->user_id }})</span>
                                @endif
                            </td>

                            <!-- Document Type Column -->
                            <td>
                                @if($item->document_type === 'nid')
                                    <span class="badge" style="background: rgba(6, 182, 212, 0.12); color: #0891b2; font-size: 12px; font-weight: 700; padding: 6px 10px; border-radius: 8px;">
                                        <i class="fa-solid fa-id-card me-1"></i> NID Card
                                    </span>
                                @elseif($item->document_type === 'passport')
                                    <span class="badge" style="background: rgba(139, 92, 246, 0.12); color: #7c3aed; font-size: 12px; font-weight: 700; padding: 6px 10px; border-radius: 8px;">
                                        <i class="fa-solid fa-passport me-1"></i> Passport
                                    </span>
                                @else
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #059669; font-size: 12px; font-weight: 700; padding: 6px 10px; border-radius: 8px;">
                                        <i class="fa-solid fa-certificate me-1"></i> Birth Cert
                                    </span>
                                @endif
                            </td>

                            <!-- Legal Name & ID Number -->
                            <td>
                                <div class="fw-bold text-truncate" style="max-width: 220px; font-size: 14px; color: var(--text-primary);" title="{{ $item->full_name }}">
                                    {{ $item->full_name }}
                                </div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <div class="copyable-chip" onclick="copyToClipboard('{{ $item->document_number }}', 'Document number copied!')" title="Click to copy">
                                        <span class="font-monospace fw-semibold">{{ $item->document_number }}</span>
                                        <i class="fa-regular fa-copy text-muted" style="font-size: 11px;"></i>
                                    </div>
                                    @if($item->date_of_birth)
                                        <span class="text-muted" style="font-size: 12px;">
                                            DOB: {{ $item->date_of_birth->format('M d, Y') }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Document & Selfie Thumbnails -->
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <!-- Front Image Thumbnail -->
                                    <div class="kyc-thumb-box" title="Click to inspect Front Image"
                                         onclick="openKycInspectionModal({{ json_encode($item) }})">
                                        <img src="{{ $item->front_image_url }}" alt="Front" class="kyc-thumb-img">
                                        <span class="kyc-thumb-label">Front</span>
                                    </div>

                                    <!-- Back Image Thumbnail (if exists) -->
                                    @if($item->back_image_url)
                                        <div class="kyc-thumb-box" title="Click to inspect Back Image"
                                             onclick="openKycInspectionModal({{ json_encode($item) }})">
                                            <img src="{{ $item->back_image_url }}" alt="Back" class="kyc-thumb-img">
                                            <span class="kyc-thumb-label">Back</span>
                                        </div>
                                    @endif

                                    <!-- Selfie with Document Thumbnail -->
                                    <div class="kyc-thumb-box selfie-box" title="Click to inspect Selfie with Doc"
                                         onclick="openKycInspectionModal({{ json_encode($item) }})">
                                        <img src="{{ $item->selfie_image_url }}" alt="Selfie" class="kyc-thumb-img">
                                        <span class="kyc-thumb-label" style="background: rgba(236,72,153,0.85);">Selfie</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Status Column -->
                            <td>
                                @if($item->status === 'approved')
                                    <span class="status-pill-modern active" title="Approved on {{ $item->reviewed_at?->format('M d, Y H:i') }}">
                                        <i class="fa-solid fa-circle-check text-success"></i>
                                        Verified
                                    </span>
                                @elseif($item->status === 'rejected')
                                    <span class="status-pill-modern inactive" title="{{ $item->rejection_reason }}">
                                        <i class="fa-solid fa-circle-xmark text-danger"></i>
                                        Rejected
                                    </span>
                                @else
                                    <span class="status-pill-modern" style="background: rgba(245, 158, 11, 0.12); color: #d97706; border-color: rgba(245, 158, 11, 0.3);">
                                        <span class="status-pulsing-dot" style="background: #f59e0b;"></span>
                                        Pending Review
                                    </span>
                                @endif
                            </td>

                            <!-- Submitted At -->
                            <td>
                                <span class="text-muted" style="font-size: 13px;">
                                    {{ $item->submitted_at ? $item->submitted_at->format('M d, Y h:i A') : $item->created_at->format('M d, Y') }}
                                </span>
                            </td>

                            <!-- Action Column -->
                            <td style="text-align: right;">
                                <div class="d-inline-flex gap-2 align-items-center">
                                    <!-- Full Inspection Modal Trigger -->
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary" 
                                            style="border-radius: 8px; font-weight: 600; font-size: 12px; padding: 5px 12px;"
                                            onclick="openKycInspectionModal({{ json_encode($item) }})">
                                        <i class="fa-solid fa-eye me-1"></i> Review
                                    </button>

                                    @if($item->status === 'pending')
                                        <!-- Quick Approve Form -->
                                        <form action="{{ route('admin.kyc.approve', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Approve verification for {{ addslashes($item->full_name) }}? User will receive verified badge.');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" style="border-radius: 8px; font-weight: 600; font-size: 12px; padding: 5px 10px;" title="Quick Approve">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>

                                        <!-- Quick Reject Modal Trigger -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger" 
                                                style="border-radius: 8px; font-weight: 600; font-size: 12px; padding: 5px 10px;" 
                                                title="Reject Verification"
                                                onclick="openRejectModal('{{ $item->id }}', '{{ addslashes($item->full_name) }}')">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    @elseif($item->status === 'approved')
                                        <!-- Revoke Button -->
                                        <form action="{{ route('admin.kyc.revoke', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Revoke verification for {{ addslashes($item->full_name) }}? Verified badge will be removed.');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-weight: 600; font-size: 11px; padding: 4px 8px;" title="Revoke Verification">
                                                Revoke
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="fa-solid fa-id-card-clip fa-3x mb-3 text-muted" style="opacity: 0.35;"></i>
                                    <h5 class="fw-bold">No KYC Applications Found</h5>
                                    <p class="text-muted mb-0">No verification requests matching your filter.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kycList->hasPages())
            <div class="p-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-top: 1px solid var(--card-border-light);">
                <span class="text-muted" style="font-size: 13px;">Showing page {{ $kycList->currentPage() }} of {{ $kycList->lastPage() }}</span>
                {{ $kycList->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

<!-- ========================================== -->
<!-- 🔍 HIGH-RES KYC INSPECTION & APPROVAL MODAL -->
<!-- ========================================== -->
<div class="modal fade" id="kycInspectModal" tabindex="-1" aria-labelledby="kycInspectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 18px; border: 1px solid var(--card-border-light); background: var(--bg-card); overflow: hidden;">
            <div class="modal-header" style="background: var(--bg-card-header); border-bottom: 1px solid var(--card-border-light); padding: 16px 24px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="modal-icon-badge" style="width: 44px; height: 44px; border-radius: 12px; background: rgba(6, 182, 212, 0.12); color: #06b6d4; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="kycInspectModalLabel" style="color: var(--text-primary);">KYC Identity Verification Details</h5>
                        <span class="text-muted" style="font-size: 13px;" id="modalSubmissionId">Submission ID #</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <div class="row g-4">
                    <!-- Left Column: User & Document Data Card -->
                    <div class="col-lg-4">
                        <div class="p-3 mb-3" style="background: var(--bg-secondary); border-radius: 14px; border: 1px solid var(--card-border-light);">
                            <h6 class="fw-bold mb-3" style="color: var(--text-primary); font-size: 14px;">
                                <i class="fa-solid fa-user-circle text-primary me-2"></i> User Profile
                            </h6>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <img src="" id="modalUserAvatar" class="rounded-circle" style="width: 54px; height: 54px; object-fit: cover; border: 2px solid #3b82f6;">
                                <div>
                                    <h6 class="fw-bold mb-0" id="modalUserDisplayName" style="color: var(--text-primary);"></h6>
                                    <div class="text-muted font-monospace" style="font-size: 12px;" id="modalUserAccountId"></div>
                                    <div class="text-muted" style="font-size: 12px;" id="modalUserPhone"></div>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 mb-3" style="background: var(--bg-secondary); border-radius: 14px; border: 1px solid var(--card-border-light);">
                            <h6 class="fw-bold mb-3" style="color: var(--text-primary); font-size: 14px;">
                                <i class="fa-solid fa-file-signature text-info me-2"></i> Submitted Data
                            </h6>
                            <table class="table table-sm table-borderless mb-0" style="font-size: 13px;">
                                <tr>
                                    <td class="text-muted ps-0" style="width: 40%;">Document Type:</td>
                                    <td class="fw-bold text-end pe-0" id="modalDocType"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">Full Legal Name:</td>
                                    <td class="fw-bold text-end pe-0" id="modalLegalName"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">Document Number:</td>
                                    <td class="fw-bold font-monospace text-end pe-0" id="modalDocNumber"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">Date of Birth:</td>
                                    <td class="fw-bold text-end pe-0" id="modalDob"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">Current Status:</td>
                                    <td class="text-end pe-0" id="modalStatusBadge"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">Submitted On:</td>
                                    <td class="text-end pe-0" id="modalSubmittedAt"></td>
                                </tr>
                            </table>
                        </div>

                        <!-- AI Liveness Pre-Check Box -->
                        <div class="p-3" style="background: rgba(16, 185, 129, 0.08); border-radius: 14px; border: 1px solid rgba(16, 185, 129, 0.25);">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold text-success" style="font-size: 13px;">
                                    <i class="fa-solid fa-robot me-1"></i> AI Verification Check
                                </span>
                                <span class="badge bg-success" style="font-size: 10px;">Passed (98%)</span>
                            </div>
                            <div style="font-size: 12px; color: var(--text-secondary);">
                                <i class="fa-solid fa-check text-success me-1"></i> Face detected and centered<br>
                                <i class="fa-solid fa-check text-success me-1"></i> Document edges and text readable<br>
                                <i class="fa-solid fa-check text-success me-1"></i> Liveness multi-angle confirmed
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Document Images & Selfie Preview Gallery -->
                    <div class="col-lg-8">
                        <h6 class="fw-bold mb-3" style="color: var(--text-primary); font-size: 14px;">
                            <i class="fa-solid fa-images text-primary me-2"></i> Document & Selfie High-Resolution Previews
                        </h6>
                        
                        <div class="row g-3">
                            <!-- Front Image Card -->
                            <div class="col-md-6" id="modalFrontWrapper">
                                <div class="doc-preview-card">
                                    <div class="doc-preview-header">
                                        <span class="badge bg-primary">1. Document Front Side</span>
                                        <a href="#" target="_blank" id="modalFrontLink" class="text-white" title="Open Full Resolution">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                    </div>
                                    <div class="doc-preview-img-box">
                                        <img src="" id="modalFrontImg" alt="Front Part" class="img-fluid">
                                    </div>
                                </div>
                            </div>

                            <!-- Back Image Card -->
                            <div class="col-md-6" id="modalBackWrapper">
                                <div class="doc-preview-card">
                                    <div class="doc-preview-header">
                                        <span class="badge bg-secondary">2. Document Back Side</span>
                                        <a href="#" target="_blank" id="modalBackLink" class="text-white" title="Open Full Resolution">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                    </div>
                                    <div class="doc-preview-img-box">
                                        <img src="" id="modalBackImg" alt="Back Part" class="img-fluid">
                                    </div>
                                </div>
                            </div>

                            <!-- Selfie with Document / Center Face Card -->
                            <div class="col-12" id="modalSelfieWrapper">
                                <div class="doc-preview-card" style="border-color: rgba(236,72,153,0.4);">
                                    <div class="doc-preview-header" style="background: linear-gradient(135deg, #ec4899, #f43f5e);">
                                        <span class="badge bg-white text-dark fw-bold">3. Live Selfie & Center Face</span>
                                        <a href="#" target="_blank" id="modalSelfieLink" class="text-white" title="Open Full Resolution">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                    </div>
                                    <div class="doc-preview-img-box" style="max-height: 280px;">
                                        <img src="" id="modalSelfieImg" alt="Selfie with Document" class="img-fluid">
                                    </div>
                                </div>
                            </div>

                            <!-- Left Turn Face Card -->
                            <div class="col-md-4" id="modalFaceLeftWrapper" style="display: none;">
                                <div class="doc-preview-card" style="border-color: rgba(6,182,212,0.4);">
                                    <div class="doc-preview-header" style="background: #0891b2;">
                                        <span class="badge bg-white text-dark fw-bold">4. Turn Left</span>
                                        <a href="#" target="_blank" id="modalFaceLeftLink" class="text-white" title="Open Full Resolution">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                    </div>
                                    <div class="doc-preview-img-box" style="max-height: 200px;">
                                        <img src="" id="modalFaceLeftImg" alt="Left Face" class="img-fluid">
                                    </div>
                                </div>
                            </div>

                            <!-- Right Turn Face Card -->
                            <div class="col-md-4" id="modalFaceRightWrapper" style="display: none;">
                                <div class="doc-preview-card" style="border-color: rgba(245,158,11,0.4);">
                                    <div class="doc-preview-header" style="background: #d97706;">
                                        <span class="badge bg-white text-dark fw-bold">5. Turn Right</span>
                                        <a href="#" target="_blank" id="modalFaceRightLink" class="text-white" title="Open Full Resolution">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                    </div>
                                    <div class="doc-preview-img-box" style="max-height: 200px;">
                                        <img src="" id="modalFaceRightImg" alt="Right Face" class="img-fluid">
                                    </div>
                                </div>
                            </div>

                            <!-- Blink / Liveness Face Card -->
                            <div class="col-md-4" id="modalFaceBlinkWrapper" style="display: none;">
                                <div class="doc-preview-card" style="border-color: rgba(16,185,129,0.4);">
                                    <div class="doc-preview-header" style="background: #059669;">
                                        <span class="badge bg-white text-dark fw-bold">6. Eye Blink</span>
                                        <a href="#" target="_blank" id="modalFaceBlinkLink" class="text-white" title="Open Full Resolution">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                    </div>
                                    <div class="doc-preview-img-box" style="max-height: 200px;">
                                        <img src="" id="modalFaceBlinkImg" alt="Blink Face" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer with Action Buttons -->
            <div class="modal-footer" style="background: var(--bg-card-header); border-top: 1px solid var(--card-border-light); padding: 14px 24px;">
                <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
                    <div>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 10px;">Close</button>
                    </div>
                    <div class="d-flex align-items-center gap-2" id="modalActionButtons">
                        <!-- Forms will be dynamically injected or submitted -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 🛑 REJECT KYC MODAL -->
<!-- ========================================== -->
<div class="modal fade" id="rejectKycModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" id="rejectKycForm" class="modal-content" style="border-radius: 18px; border: 1px solid var(--card-border-light); background: var(--bg-card);">
            @csrf
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(239, 68, 68, 0.12); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" style="color: var(--text-primary);">Reject KYC Submission</h5>
                        <span class="text-muted" style="font-size: 13px;" id="rejectTargetName"></span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size: 13px; color: var(--text-primary);">Reason for Rejection *</label>
                    <select class="form-select mb-2" id="quickReasonSelect" onchange="document.getElementById('rejectionReasonInput').value = this.value" style="border-radius: 10px; font-size: 13px;">
                        <option value="">-- Choose a standard reason --</option>
                        <option value="Document photo is blurry, dark or illegible. Please re-upload a clear copy.">Blurry / Illegible Document</option>
                        <option value="Selfie does not match the photo on the identity document.">Selfie does not match photo</option>
                        <option value="Document is expired or invalid.">Expired or Invalid Document</option>
                        <option value="Missing back side of the NID Card.">Missing Back Side of NID</option>
                        <option value="Full legal name or document number does not match document.">Name/Number Mismatch</option>
                    </select>
                    <textarea name="rejection_reason" id="rejectionReasonInput" class="form-control" rows="3" placeholder="Enter custom rejection reason provided to the user..." style="border-radius: 10px; font-size: 13px;" required></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 10px;">Cancel</button>
                <button type="submit" class="btn btn-danger" style="border-radius: 10px; font-weight: 600;">
                    <i class="fa-solid fa-ban me-1"></i> Confirm Rejection
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .kyc-thumb-box {
        position: relative;
        width: 58px;
        height: 44px;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid var(--card-border-light);
        cursor: pointer;
        background: #111;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kyc-thumb-box:hover {
        transform: scale(1.08);
        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    }
    .kyc-thumb-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .kyc-thumb-label {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0,0,0,0.75);
        color: #fff;
        font-size: 9px;
        font-weight: 700;
        text-align: center;
        padding: 1px 0;
        text-transform: uppercase;
    }
    .doc-preview-card {
        border-radius: 14px;
        border: 1px solid var(--card-border-light);
        background: var(--bg-secondary);
        overflow: hidden;
    }
    .doc-preview-header {
        padding: 8px 14px;
        background: var(--bg-card-header);
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid var(--card-border-light);
    }
    .doc-preview-img-box {
        padding: 12px;
        text-align: center;
        max-height: 240px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #09090f;
    }
    .doc-preview-img-box img {
        max-height: 220px;
        width: auto;
        max-width: 100%;
        object-fit: contain;
        border-radius: 6px;
    }
</style>
@endpush

@push('scripts')
<script>
    function copyToClipboard(text, message) {
        navigator.clipboard.writeText(text).then(() => {
            if (window.showToast) {
                window.showToast(message, 'info', 'Copied');
            } else {
                alert(message);
            }
        });
    }

    function openRejectModal(id, name) {
        const form = document.getElementById('rejectKycForm');
        form.action = `/admin/kyc/${id}/reject`;
        document.getElementById('rejectTargetName').innerText = `User: ${name}`;
        document.getElementById('rejectionReasonInput').value = '';
        new bootstrap.Modal(document.getElementById('rejectKycModal')).show();
    }

    function openKycInspectionModal(item) {
        document.getElementById('modalSubmissionId').innerText = `Submission #${item.id} • ${item.document_type_label}`;
        
        // User Profile Info
        if (item.user) {
            document.getElementById('modalUserAvatar').src = item.user.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(item.user.display_name)}&background=3b82f6&color=fff`;
            document.getElementById('modalUserDisplayName').innerText = item.user.display_name;
            document.getElementById('modalUserAccountId').innerText = `Account ID: ${item.user.account_id || ('#' + item.user.id)}`;
            document.getElementById('modalUserPhone').innerText = `Phone: ${item.user.phone || 'N/A'}`;
        }

        document.getElementById('modalDocType').innerText = item.document_type_label;
        document.getElementById('modalLegalName').innerText = item.full_name;
        document.getElementById('modalDocNumber').innerText = item.document_number;
        document.getElementById('modalDob').innerText = item.date_of_birth ? new Date(item.date_of_birth).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';
        
        // Status Badge
        let statusHtml = '';
        if (item.status === 'approved') {
            statusHtml = '<span class="badge bg-success"><i class="fa-solid fa-circle-check me-1"></i> Verified</span>';
        } else if (item.status === 'rejected') {
            statusHtml = `<span class="badge bg-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Rejected</span>`;
        } else {
            statusHtml = '<span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i> Pending Review</span>';
        }
        document.getElementById('modalStatusBadge').innerHTML = statusHtml;
        document.getElementById('modalSubmittedAt').innerText = item.submitted_at ? new Date(item.submitted_at).toLocaleString() : new Date(item.created_at).toLocaleDateString();

        // Image previews
        document.getElementById('modalFrontImg').src = item.front_image_url;
        document.getElementById('modalFrontLink').href = item.front_image_url;

        if (item.back_image_url) {
            document.getElementById('modalBackWrapper').style.display = 'block';
            document.getElementById('modalBackImg').src = item.back_image_url;
            document.getElementById('modalBackLink').href = item.back_image_url;
        } else {
            document.getElementById('modalBackWrapper').style.display = 'none';
        }

        document.getElementById('modalSelfieImg').src = item.selfie_image_url || item.face_center_image_url;
        document.getElementById('modalSelfieLink').href = item.selfie_image_url || item.face_center_image_url;

        // Turn Left Image
        if (item.face_left_image_url) {
            document.getElementById('modalFaceLeftWrapper').style.display = 'block';
            document.getElementById('modalFaceLeftImg').src = item.face_left_image_url;
            document.getElementById('modalFaceLeftLink').href = item.face_left_image_url;
        } else {
            document.getElementById('modalFaceLeftWrapper').style.display = 'none';
        }

        // Turn Right Image
        if (item.face_right_image_url) {
            document.getElementById('modalFaceRightWrapper').style.display = 'block';
            document.getElementById('modalFaceRightImg').src = item.face_right_image_url;
            document.getElementById('modalFaceRightLink').href = item.face_right_image_url;
        } else {
            document.getElementById('modalFaceRightWrapper').style.display = 'none';
        }

        // Eye Blink Image
        if (item.face_blink_image_url) {
            document.getElementById('modalFaceBlinkWrapper').style.display = 'block';
            document.getElementById('modalFaceBlinkImg').src = item.face_blink_image_url;
            document.getElementById('modalFaceBlinkLink').href = item.face_blink_image_url;
        } else {
            document.getElementById('modalFaceBlinkWrapper').style.display = 'none';
        }

        // Modal Action buttons
        const actionBtnContainer = document.getElementById('modalActionButtons');
        actionBtnContainer.innerHTML = '';

        if (item.status === 'pending') {
            actionBtnContainer.innerHTML = `
                <button type="button" class="btn btn-outline-danger" style="border-radius: 10px;" onclick="bootstrap.Modal.getInstance(document.getElementById('kycInspectModal')).hide(); openRejectModal('${item.id}', '${item.full_name.replace(/'/g, "\\'")}');">
                    <i class="fa-solid fa-ban me-1"></i> Reject
                </button>
                <form action="/admin/kyc/${item.id}/approve" method="POST" class="d-inline">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
                    <button type="submit" class="btn btn-success" style="border-radius: 10px; font-weight: 600;">
                        <i class="fa-solid fa-circle-check me-1"></i> Approve & Grant Verified Badge
                    </button>
                </form>
            `;
        } else if (item.status === 'approved') {
            actionBtnContainer.innerHTML = `
                <form action="/admin/kyc/${item.id}/revoke" method="POST" class="d-inline" onsubmit="return confirm('Revoke verification?');">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
                    <button type="submit" class="btn btn-outline-warning" style="border-radius: 10px;">
                        <i class="fa-solid fa-rotate-left me-1"></i> Revoke Verification
                    </button>
                </form>
            `;
        }

        new bootstrap.Modal(document.getElementById('kycInspectModal')).show();
    }
</script>
@endpush
@endsection
