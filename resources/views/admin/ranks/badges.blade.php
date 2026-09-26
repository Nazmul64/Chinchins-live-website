@extends('layouts.admin')

@section('title', 'Daily, Weekly & Monthly Rank Badges')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Rank Badges</span>
            </div>
            <h1 class="page-title mb-1">
                <i class="fa-solid fa-award text-warning"></i>
                <span>Daily, Weekly & Monthly Rank Badges</span>
            </h1>
            <p class="page-subtitle mb-0">Configure top rank badges & avatar frames for Daily, Weekly, and Monthly leaderboards (Rich & Charm). Synced instantly with Flutter client Hive local storage.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ url('/api/app/rank-badges-config') }}" target="_blank" class="btn btn-outline-secondary" style="border-radius: 8px; font-weight: 600; font-size: 13px;">
                <i class="fa-solid fa-code me-1"></i> Preview API JSON
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadBadgeModal" style="border-radius: 8px; font-weight: 600; font-size: 13px; background: linear-gradient(135deg, #f59e0b, #d97706); border: none;">
                <i class="fa-solid fa-plus-circle me-1"></i> Upload New Rank Badge
            </button>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert" style="border-radius: 10px;">
            <i class="fa-solid fa-circle-check fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 10px;">
            <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> Please fix the following errors:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card" style="border-left: 4px solid #f59e0b; background: #ffffff; border-radius: 12px; padding: 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Total Badges</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ $dailyBadges->count() + $weeklyBadges->count() + $monthlyBadges->count() }}</h3>
                    </div>
                    <div class="stat-icon-box d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: rgba(245, 158, 11, 0.15); color: #f59e0b; font-size: 20px;">
                        <i class="fa-solid fa-trophy"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card" style="border-left: 4px solid #3b82f6; background: #ffffff; border-radius: 12px; padding: 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Daily Badges</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ $dailyBadges->count() }}</h3>
                    </div>
                    <div class="stat-icon-box d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: rgba(59, 130, 246, 0.15); color: #3b82f6; font-size: 20px;">
                        <i class="fa-solid fa-calendar-day"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card" style="border-left: 4px solid #8b5cf6; background: #ffffff; border-radius: 12px; padding: 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Weekly Badges</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ $weeklyBadges->count() }}</h3>
                    </div>
                    <div class="stat-icon-box d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: rgba(139, 92, 246, 0.15); color: #8b5cf6; font-size: 20px;">
                        <i class="fa-solid fa-calendar-week"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card" style="border-left: 4px solid #ec4899; background: #ffffff; border-radius: 12px; padding: 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Monthly Badges</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ $monthlyBadges->count() }}</h3>
                    </div>
                    <div class="stat-icon-box d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: rgba(236, 72, 153, 0.15); color: #ec4899; font-size: 20px;">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- API Info Card for Mobile App Sync -->
    <div class="card border-0 mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #1e293b, #0f172a); color: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.2); color: #f59e0b; font-size: 22px;">
                        <i class="fa-solid fa-bolt-lightning"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Flutter Mobile App Fast Offline & Instant Hive Sync</h5>
                        <p class="mb-0 text-white-50" style="font-size: 13px;">
                            Endpoint: <code class="text-warning px-2 py-1 rounded" style="background: rgba(0,0,0,0.3); font-size: 13px;">GET /api/app/rank-badges-config</code>
                            — Data is cached with atomic invalidation whenever badges are added or updated.
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-warning text-nowrap" onclick="copyApiUrl()">
                        <i class="fa-solid fa-copy me-1"></i> Copy API URL
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Period Badges Tabs -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav nav-tabs nav-fill border-0" id="badgeTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-3 fw-bold" id="daily-tab" data-bs-toggle="tab" data-bs-target="#daily" type="button" role="tab" style="font-size: 14px;">
                        <i class="fa-solid fa-sun text-warning me-2"></i> Daily Badges ({{ $dailyBadges->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold" id="weekly-tab" data-bs-toggle="tab" data-bs-target="#weekly" type="button" role="tab" style="font-size: 14px;">
                        <i class="fa-solid fa-calendar-week text-primary me-2"></i> Weekly Badges ({{ $weeklyBadges->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold" id="monthly-tab" data-bs-toggle="tab" data-bs-target="#monthly" type="button" role="tab" style="font-size: 14px;">
                        <i class="fa-solid fa-crown text-danger me-2"></i> Monthly Badges ({{ $monthlyBadges->count() }})
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="badgeTabsContent">
                <!-- Daily Tab -->
                <div class="tab-pane fade show active" id="daily" role="tabpanel">
                    @include('admin.ranks.partials.badge_table', ['badges' => $dailyBadges, 'period' => 'Daily'])
                </div>

                <!-- Weekly Tab -->
                <div class="tab-pane fade" id="weekly" role="tabpanel">
                    @include('admin.ranks.partials.badge_table', ['badges' => $weeklyBadges, 'period' => 'Weekly'])
                </div>

                <!-- Monthly Tab -->
                <div class="tab-pane fade" id="monthly" role="tabpanel">
                    @include('admin.ranks.partials.badge_table', ['badges' => $monthlyBadges, 'period' => 'Monthly'])
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Badge Modal -->
<div class="modal fade" id="uploadBadgeModal" tabindex="-1" aria-labelledby="uploadBadgeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e293b, #0f172a); border-bottom: none;">
                <h5 class="modal-title fw-bold" id="uploadBadgeModalLabel">
                    <i class="fa-solid fa-cloud-arrow-up text-warning me-2"></i> Upload Period Rank Badge & Frame
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.rank-badges.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Badge Name -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark" style="font-size: 13px;">Badge Name <span class="text-danger">*</span></label>
                            <input type="text" name="badge_name" class="form-control" placeholder="e.g. Daily Top Star #1, Weekly Champion" required style="border-radius: 8px;">
                            <small class="text-muted">Descriptive title shown in app leaderboards.</small>
                        </div>

                        <!-- Period Type -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark" style="font-size: 13px;">Period Type <span class="text-danger">*</span></label>
                            <select name="period_type" class="form-select" required style="border-radius: 8px;">
                                <option value="daily">Daily (প্রতিদিনের র্যাংক)</option>
                                <option value="weekly">Weekly (সাপ্তাহিক র্যাংক)</option>
                                <option value="monthly">Monthly (মাসিক র্যাংক)</option>
                            </select>
                        </div>

                        <!-- Category -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark" style="font-size: 13px;">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required style="border-radius: 8px;">
                                <option value="rich">Rich (সর্বোচ্চ খরচকারী / Gifter)</option>
                                <option value="charm">Charm (সর্বোচ্চ আকর্ষণীয় / Host Earner)</option>
                            </select>
                        </div>

                        <!-- Rank Position -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-dark" style="font-size: 13px;">Rank Position <span class="text-danger">*</span></label>
                            <input type="number" name="rank_position" min="1" max="100" class="form-control" value="1" required style="border-radius: 8px;">
                            <small class="text-muted">1 = 1st, 2 = 2nd, etc.</small>
                        </div>

                        <!-- Min Required Coins -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-dark" style="font-size: 13px;">Min Coins <span class="text-danger">*</span></label>
                            <input type="number" name="min_required_coins" min="0" class="form-control" value="0" required style="border-radius: 8px;">
                            <small class="text-muted">Threshold to earn</small>
                        </div>

                        <!-- Badge Icon File -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                                <i class="fa-solid fa-image text-primary me-1"></i> Badge Icon (PNG / WEBP) <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="badge_icon" class="form-control" accept="image/png,image/webp" required style="border-radius: 8px;" onchange="previewFile(this, 'badge_preview')">
                            <small class="text-muted d-block mt-1">Saves to: <code>public/uploads/ranks/badges/</code></small>
                            <div class="mt-2 text-center p-2 border rounded" style="background: #f8fafc; height: 90px; display: flex; align-items: center; justify-content: center;">
                                <img id="badge_preview" src="" alt="Badge Preview" style="max-height: 75px; max-width: 100%; display: none;">
                                <span id="badge_preview_placeholder" class="text-muted" style="font-size: 12px;">No badge icon selected</span>
                            </div>
                        </div>

                        <!-- Avatar Frame File -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                                <i class="fa-solid fa-circle-notch text-warning me-1"></i> Avatar Frame (Optional)
                            </label>
                            <input type="file" name="avatar_frame" class="form-control" accept="image/png,image/webp" style="border-radius: 8px;" onchange="previewFile(this, 'frame_preview')">
                            <small class="text-muted d-block mt-1">Saves to: <code>public/uploads/ranks/frames/</code></small>
                            <div class="mt-2 text-center p-2 border rounded" style="background: #f8fafc; height: 90px; display: flex; align-items: center; justify-content: center;">
                                <img id="frame_preview" src="" alt="Frame Preview" style="max-height: 75px; max-width: 100%; display: none;">
                                <span id="frame_preview_placeholder" class="text-muted" style="font-size: 12px;">No frame selected (Optional)</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-top: 1px solid #e2e8f0;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #f59e0b, #d97706); border: none;">
                        <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload Badge
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewFile(input, targetId) {
    const file = input.files[0];
    const preview = document.getElementById(targetId);
    const placeholder = document.getElementById(targetId + '_placeholder');
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        }
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
        if (placeholder) placeholder.style.display = 'block';
    }
}

function copyApiUrl() {
    const url = window.location.origin + '/api/app/rank-badges-config';
    navigator.clipboard.writeText(url).then(() => {
        alert('API URL copied to clipboard:\n' + url);
    });
}
</script>
@endsection
