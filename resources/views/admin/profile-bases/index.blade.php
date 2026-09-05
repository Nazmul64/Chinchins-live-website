@extends('layouts.admin')

@section('title', 'Profile Bases & Level Badges')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Level Badges & Frames</span>
            </div>
            <h1 class="page-title mb-1">
                <i class="fa-solid fa-certificate text-amber" style="color: #f59e0b;"></i>
                <span>Profile Bases & Level Badges Management</span>
            </h1>
            <p class="page-subtitle mb-0">Configure required host earning coins for Level 1 to Level 10+, upload custom avatar frame badges (Base), and manage automatic profile picture frame wrapping in the app.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="scrollToPreview()" style="border-radius: 8px; font-weight: 600; font-size: 13px;">
                <i class="fa-solid fa-eye me-1"></i> Live Avatar Preview
            </button>
            <button type="button" class="btn-ch-primary" onclick="openCreateBaseModal()">
                <i class="fa-solid fa-plus-circle me-1"></i> Add Custom Level
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #f59e0b; background: #ffffff; border-radius: 12px; padding: 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Total Level Tiers</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ $totalBases }}</h3>
                    </div>
                    <div class="stat-icon-box d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: rgba(245, 158, 11, 0.15); color: #f59e0b; font-size: 20px;">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #10b981; background: #ffffff; border-radius: 12px; padding: 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Active in App</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ $activeBases }}</h3>
                    </div>
                    <div class="stat-icon-box d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: rgba(16, 185, 129, 0.15); color: #10b981; font-size: 20px;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #3b82f6; background: #ffffff; border-radius: 12px; padding: 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Max Level Tier</span>
                        <h3 class="fw-bolder mt-1 mb-0">Level {{ $maxLevel }}</h3>
                    </div>
                    <div class="stat-icon-box d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: rgba(59, 130, 246, 0.15); color: #3b82f6; font-size: 20px;">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #8b5cf6; background: #ffffff; border-radius: 12px; padding: 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Top Level Coins</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ number_format($maxCoins) }}</h3>
                    </div>
                    <div class="stat-icon-box d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: rgba(139, 92, 246, 0.15); color: #8b5cf6; font-size: 20px;">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Avatar Frame Preview Section -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" id="liveAvatarPreviewSection" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); color: #ffffff;">
        <div class="card-body p-4">
            <div class="row align-items-center g-4">
                <div class="col-12 col-lg-7">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill" style="font-size: 11px;">
                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Interactive Live Preview
                        </span>
                        <span class="text-white-50" style="font-size: 13px;">How the Base appears wrapped over user avatar</span>
                    </div>
                    <h3 class="fw-bold text-white mb-2">Dynamic Profile Picture Base Wrapping</h3>
                    <p class="text-white-50 mb-3" style="font-size: 13px; line-height: 1.6;">
                        When a female host talks with a caller (e.g. 100 coins/min, 50% split), each earned coin accumulates towards their lifetime level. As they cross each coin threshold, the app automatically overlays their unlocked Level Base frame around their circular avatar profile picture.
                    </p>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <label class="text-white fw-bold mb-0" style="font-size: 13px;">Test Level Preview:</label>
                        <select class="form-select form-select-sm bg-dark text-white border-secondary" id="previewLevelSelector" style="width: 220px; border-radius: 8px;" onchange="updateLivePreview(this.value)">
                            @foreach($bases as $b)
                                <option value="{{ $b->level }}" 
                                    data-name="{{ $b->name }}" 
                                    data-frame="{{ $b->base_frame_image_url }}" 
                                    data-coins="{{ number_format($b->required_coins) }}" 
                                    data-color="{{ $b->badge_color }}" 
                                    data-icon="{{ $b->badge_icon }}"
                                    data-privilege="{{ $b->privilege_text }}">
                                    Level {{ $b->level }} ({{ $b->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Live Stacking Avatar Box -->
                <div class="col-12 col-lg-5 text-center">
                    <div class="d-inline-flex flex-column align-items-center p-3 rounded-4" style="background: rgba(255, 255, 255, 0.06); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1);">
                        <!-- Avatar Frame Wrapper with precise aspect ratio -->
                        <div class="position-relative d-flex align-items-center justify-content-center" style="width: 140px; height: 140px; margin: 10px auto;">
                            <!-- Circular User Avatar -->
                            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80" 
                                alt="Sample User Avatar" 
                                id="previewAvatarImg"
                                class="rounded-circle shadow" 
                                style="width: 96px; height: 96px; object-fit: cover; z-index: 1;">
                            
                            <!-- Overlaid Base Frame (SVG / PNG) -->
                            <img src="{{ $bases->first()?->base_frame_image_url ?? asset('uploads/all_image/profile_base_royal_gold.svg') }}" 
                                alt="Profile Base Frame" 
                                id="previewBaseFrameImg"
                                class="position-absolute" 
                                style="width: 140px; height: 140px; top: 0; left: 0; pointer-events: none; z-index: 2; object-fit: contain; filter: drop-shadow(0 0 8px rgba(245, 158, 11, 0.5)); transition: all 0.3s ease;">
                            
                            <!-- Level Badge Tag at Bottom -->
                            <span class="position-absolute badge rounded-pill shadow-sm" 
                                id="previewLevelBadge" 
                                style="bottom: 2px; z-index: 3; font-size: 11px; padding: 3px 10px; background: #f59e0b; color: #ffffff; border: 2px solid #ffffff;">
                                <i class="fa-solid fa-star me-1" id="previewBadgeIcon"></i> Lv.1
                            </span>
                        </div>

                        <h5 class="fw-bold text-white mt-2 mb-1" id="previewLevelTitle">Level 1 - Bronze Star</h5>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-warning text-dark fw-bold rounded-pill" id="previewRequiredCoins">
                                <i class="fa-solid fa-coins me-1"></i> 1,000 Coins Required
                            </span>
                        </div>
                        <p class="text-white-50 mb-0 text-center" style="font-size: 12px; max-width: 280px;" id="previewPrivilegeText">
                            Unlocks Bronze Star Animated Avatar Frame
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Quick-Edit & Levels List Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-5" style="background: #ffffff;">
        <div class="card-header bg-transparent border-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1" style="color: #1e293b;">
                    <i class="fa-solid fa-sliders text-primary me-2"></i> Level 1 to 10+ Configuration Table
                </h4>
                <p class="text-muted mb-0" style="font-size: 13px;">Modify coins threshold, titles, badge styles, and frame selections. Click "Save All Level Changes" to update all levels at once.</p>
            </div>
            <button type="submit" form="batchLevelsForm" class="btn-ch-primary">
                <i class="fa-solid fa-floppy-disk me-1"></i> Save All Level Changes
            </button>
        </div>

        <div class="card-body p-0">
            <form action="{{ route('admin.profile-bases.batch-update') }}" method="POST" id="batchLevelsForm">
                @csrf
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead style="background: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; color: #475569; font-weight: 700;">
                            <tr>
                                <th class="ps-4" style="width: 80px;">Level</th>
                                <th style="width: 140px;">Frame Base</th>
                                <th style="min-width: 180px;">Level Name</th>
                                <th style="width: 160px;">Required Coins</th>
                                <th style="width: 150px;">Badge & Icon</th>
                                <th style="min-width: 220px;">Privilege Description</th>
                                <th style="width: 100px;" class="text-center">Status</th>
                                <th class="pe-4 text-end" style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bases as $base)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <!-- Level Number Badge -->
                                <td class="ps-4">
                                    <span class="badge rounded-pill fw-bold" style="background: {{ $base->badge_color }}; color: #ffffff; padding: 6px 12px; font-size: 12px;">
                                        Lv.{{ $base->level }}
                                    </span>
                                </td>

                                <!-- Frame Base Thumbnail & Selector -->
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="position-relative d-flex align-items-center justify-content-center rounded-3 p-1" style="width: 48px; height: 48px; background: #0f172a;">
                                            <img src="{{ $base->base_frame_image_url }}" alt="Base Frame" style="width: 44px; height: 44px; object-fit: contain;">
                                        </div>
                                        <div>
                                            <select name="levels[{{ $base->id }}][preset_frame]" class="form-select form-select-sm" style="font-size: 11px; width: 130px; border-radius: 6px;">
                                                @foreach($availablePresetFrames as $path => $label)
                                                    <option value="{{ $path }}" {{ $base->base_frame_image == $path ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </td>

                                <!-- Level Name Input -->
                                <td>
                                    <input type="text" name="levels[{{ $base->id }}][name]" class="form-control form-control-sm" value="{{ $base->name }}" required style="border-radius: 6px; font-weight: 600;">
                                </td>

                                <!-- Required Coins Input -->
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light border-end-0" style="color: #f59e0b;"><i class="fa-solid fa-coins"></i></span>
                                        <input type="number" name="levels[{{ $base->id }}][required_coins]" class="form-control form-control-sm border-start-0" value="{{ $base->required_coins }}" min="0" required style="font-weight: 600;">
                                    </div>
                                </td>

                                <!-- Badge Icon & Color -->
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <select name="levels[{{ $base->id }}][badge_icon]" class="form-select form-select-sm" style="width: 85px; font-size: 11px; border-radius: 6px;">
                                            <option value="star" {{ $base->badge_icon == 'star' ? 'selected' : '' }}>⭐ Star</option>
                                            <option value="crown" {{ $base->badge_icon == 'crown' ? 'selected' : '' }}>👑 Crown</option>
                                            <option value="gem" {{ $base->badge_icon == 'gem' ? 'selected' : '' }}>💎 Gem</option>
                                            <option value="fire" {{ $base->badge_icon == 'fire' ? 'selected' : '' }}>🔥 Fire</option>
                                            <option value="bolt" {{ $base->badge_icon == 'bolt' ? 'selected' : '' }}>⚡ Bolt</option>
                                            <option value="shield" {{ $base->badge_icon == 'shield' ? 'selected' : '' }}>🛡️ Shield</option>
                                            <option value="user" {{ $base->badge_icon == 'user' ? 'selected' : '' }}>👤 User</option>
                                        </select>
                                        <input type="color" name="levels[{{ $base->id }}][badge_color]" class="form-control form-control-color form-control-sm" value="{{ $base->badge_color }}" title="Choose badge color" style="width: 36px; height: 31px; padding: 2px; border-radius: 6px;">
                                    </div>
                                </td>

                                <!-- Privilege Description -->
                                <td>
                                    <input type="text" name="levels[{{ $base->id }}][privilege_text]" class="form-control form-control-sm" value="{{ $base->privilege_text }}" placeholder="Unlocks exclusive frame & perks" style="border-radius: 6px;">
                                </td>

                                <!-- Active Status -->
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input" type="checkbox" name="levels[{{ $base->id }}][is_active]" value="1" {{ $base->is_active ? 'checked' : '' }} style="cursor: pointer;">
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="pe-4 text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary" style="border-radius: 6px;" onclick="openEditBaseModal({{ json_encode($base) }})" title="Upload Custom Frame or Edit">
                                            <i class="fa-solid fa-arrow-up-from-bracket"></i> Edit
                                        </button>
                                        @if($base->level > 0)
                                        <button type="button" class="btn btn-sm btn-outline-danger" style="border-radius: 6px;" onclick="confirmDeleteBase({{ $base->id }}, {{ $base->level }})" title="Delete Level">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    No level bases found. Click "Add Custom Level" or reload page to seed defaults.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 d-flex justify-content-between align-items-center flex-wrap gap-3 bg-light border-top rounded-bottom-4">
                    <span class="text-muted" style="font-size: 13px;">
                        <i class="fa-solid fa-circle-info text-primary me-1"></i> Tip: Female hosts automatically unlock these level badges and frames as they earn coins in 1-on-1 audio/video calls.
                    </span>
                    <button type="submit" class="btn-ch-primary px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save All Level Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- ➕ Add Custom Level Modal -->
<!-- ========================================== -->
<div class="modal fade" id="createBaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 40px; height: 40px; background: rgba(245, 158, 11, 0.15); color: #f59e0b; font-size: 18px;">
                        <i class="fa-solid fa-plus-circle"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Create New Level Base Frame</h5>
                        <p class="text-muted mb-0" style="font-size: 12px;">Add a new level threshold, badge icon, and avatar frame image.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.profile-bases.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold" style="font-size: 13px;">Level Number <span class="text-danger">*</span></label>
                            <input type="number" name="level" class="form-control" placeholder="e.g. 11" min="1" required style="border-radius: 8px;">
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-bold" style="font-size: 13px;">Level Title / Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Level 11 - Cosmic Supreme" required style="border-radius: 8px;">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Required Earning Coins <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light" style="color: #f59e0b;"><i class="fa-solid fa-coins"></i></span>
                                <input type="number" name="required_coins" class="form-control" placeholder="e.g. 10000000" min="0" required style="border-radius: 0 8px 8px 0;">
                            </div>
                            <small class="text-muted" style="font-size: 11px;">Host must accumulate this amount of coins to unlock.</small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Choose Preset Base Frame</label>
                            <select name="preset_frame" class="form-select" style="border-radius: 8px;">
                                @foreach($availablePresetFrames as $path => $label)
                                    <option value="{{ $path }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold" style="font-size: 13px;">OR Upload Custom Base Frame Image (SVG / PNG / WebP)</label>
                            <input type="file" name="frame_image" class="form-control" accept=".svg,.png,.webp,.jpg,.jpeg,.gif" style="border-radius: 8px;">
                            <small class="text-muted" style="font-size: 11px;">Recommended: Transparent PNG or SVG with circular center opening (e.g. 512x512 px).</small>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold" style="font-size: 13px;">Badge Icon</label>
                            <select name="badge_icon" class="form-select" style="border-radius: 8px;">
                                <option value="crown">👑 Crown</option>
                                <option value="star">⭐ Star</option>
                                <option value="gem">💎 Gem</option>
                                <option value="fire">🔥 Fire</option>
                                <option value="bolt">⚡ Bolt</option>
                                <option value="shield">🛡️ Shield</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold" style="font-size: 13px;">Badge Color (Hex)</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" name="badge_color" class="form-control form-control-color" value="#f59e0b" style="width: 44px; height: 38px; padding: 2px; border-radius: 8px;">
                                <input type="text" class="form-control" value="#f59e0b" onchange="this.previousElementSibling.value=this.value" style="border-radius: 8px;">
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold" style="font-size: 13px;">Glow Color / RGBA</label>
                            <input type="text" name="glow_color" class="form-control" value="rgba(245, 158, 11, 0.5)" placeholder="rgba(245, 158, 11, 0.5)" style="border-radius: 8px;">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold" style="font-size: 13px;">Privilege / Unlock Perks Description</label>
                            <input type="text" name="privilege_text" class="form-control" placeholder="e.g. Unlocks Ultra Cosmic Frame & Global Live Notification" style="border-radius: 8px;">
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="createIsActive" checked style="cursor: pointer;">
                                <label class="form-check-label fw-bold" for="createIsActive" style="font-size: 13px;">Active in App Immediately</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">Cancel</button>
                    <button type="submit" class="btn-ch-primary">
                        <i class="fa-solid fa-plus-circle me-1"></i> Create Level Base
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- ✏️ Edit Level Base & Upload Frame Modal -->
<!-- ========================================== -->
<div class="modal fade" id="editBaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 40px; height: 40px; background: rgba(59, 130, 246, 0.15); color: #3b82f6; font-size: 18px;">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="editModalTitle">Edit Level Base</h5>
                        <p class="text-muted mb-0" style="font-size: 12px;">Upload custom avatar frame image or adjust parameters.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editBaseForm" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold" style="font-size: 13px;">Level Number</label>
                            <input type="text" id="editLevelNum" class="form-control bg-light" readonly style="border-radius: 8px; font-weight: 700;">
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-bold" style="font-size: 13px;">Level Title / Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editName" class="form-control" required style="border-radius: 8px;">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Required Earning Coins <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light" style="color: #f59e0b;"><i class="fa-solid fa-coins"></i></span>
                                <input type="number" name="required_coins" id="editRequiredCoins" class="form-control" min="0" required style="border-radius: 0 8px 8px 0;">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Choose Preset Frame</label>
                            <select name="preset_frame" id="editPresetFrame" class="form-select" style="border-radius: 8px;">
                                <option value="">-- Keep Current / Uploaded --</option>
                                @foreach($availablePresetFrames as $path => $label)
                                    <option value="{{ $path }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold" style="font-size: 13px;">Replace / Upload Custom Frame (SVG / PNG / WebP)</label>
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background: #0f172a; flex-shrink: 0;">
                                    <img src="" alt="Current Frame" id="editCurrentFrameImg" style="width: 50px; height: 50px; object-fit: contain;">
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="frame_image" class="form-control" accept=".svg,.png,.webp,.jpg,.jpeg,.gif" style="border-radius: 8px;">
                                    <small class="text-muted" style="font-size: 11px;">Upload high-res transparent SVG or PNG frame image.</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold" style="font-size: 13px;">Badge Icon</label>
                            <select name="badge_icon" id="editBadgeIcon" class="form-select" style="border-radius: 8px;">
                                <option value="star">⭐ Star</option>
                                <option value="crown">👑 Crown</option>
                                <option value="gem">💎 Gem</option>
                                <option value="fire">🔥 Fire</option>
                                <option value="bolt">⚡ Bolt</option>
                                <option value="shield">🛡️ Shield</option>
                                <option value="user">👤 User</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold" style="font-size: 13px;">Badge Color (Hex)</label>
                            <input type="color" name="badge_color" id="editBadgeColor" class="form-control form-control-color w-100" style="height: 38px; padding: 2px; border-radius: 8px;">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold" style="font-size: 13px;">Glow Color</label>
                            <input type="text" name="glow_color" id="editGlowColor" class="form-control" style="border-radius: 8px;">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold" style="font-size: 13px;">Privilege Description</label>
                            <input type="text" name="privilege_text" id="editPrivilegeText" class="form-control" style="border-radius: 8px;">
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editIsActive" style="cursor: pointer;">
                                <label class="form-check-label fw-bold" for="editIsActive" style="font-size: 13px;">Active in App</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">Cancel</button>
                    <button type="submit" class="btn-ch-primary">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Update Level Base
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 🗑️ Delete Confirmation Form -->
<!-- ========================================== -->
<form id="deleteBaseForm" action="" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
    // Live Preview Switcher
    function updateLivePreview(level) {
        const select = document.getElementById('previewLevelSelector');
        const opt = select.options[select.selectedIndex];
        if (!opt) return;

        const name = opt.getAttribute('data-name');
        const frameUrl = opt.getAttribute('data-frame');
        const coins = opt.getAttribute('data-coins');
        const color = opt.getAttribute('data-color');
        const icon = opt.getAttribute('data-icon');
        const privilege = opt.getAttribute('data-privilege');

        document.getElementById('previewBaseFrameImg').src = frameUrl;
        document.getElementById('previewLevelTitle').textContent = name;
        document.getElementById('previewRequiredCoins').innerHTML = `<i class="fa-solid fa-coins me-1"></i> ${coins} Coins Required`;
        document.getElementById('previewPrivilegeText').textContent = privilege || 'Standard Avatar Base Frame';

        const badge = document.getElementById('previewLevelBadge');
        badge.style.backgroundColor = color;
        badge.innerHTML = `<i class="fa-solid fa-${icon} me-1"></i> Lv.${level}`;
    }

    function scrollToPreview() {
        document.getElementById('liveAvatarPreviewSection')?.scrollIntoView({ behavior: 'smooth' });
    }

    function openCreateBaseModal() {
        new bootstrap.Modal(document.getElementById('createBaseModal')).show();
    }

    function openEditBaseModal(base) {
        const form = document.getElementById('editBaseForm');
        form.action = `/admin/profile-bases/${base.id}`;

        document.getElementById('editModalTitle').textContent = `Edit Level ${base.level} Base Frame`;
        document.getElementById('editLevelNum').value = `Level ${base.level}`;
        document.getElementById('editName').value = base.name || '';
        document.getElementById('editRequiredCoins').value = base.required_coins || 0;
        document.getElementById('editBadgeIcon').value = base.badge_icon || 'star';
        document.getElementById('editBadgeColor').value = base.badge_color || '#f59e0b';
        document.getElementById('editGlowColor').value = base.glow_color || 'rgba(245, 158, 11, 0.45)';
        document.getElementById('editPrivilegeText').value = base.privilege_text || '';
        document.getElementById('editIsActive').checked = !!base.is_active;
        document.getElementById('editCurrentFrameImg').src = base.base_frame_image_url || '';

        new bootstrap.Modal(document.getElementById('editBaseModal')).show();
    }

    function confirmDeleteBase(id, level) {
        if (confirm(`Are you sure you want to delete Level ${level}? Users will be reassigned to the previous level.`)) {
            const form = document.getElementById('deleteBaseForm');
            form.action = `/admin/profile-bases/${id}`;
            form.submit();
        }
    }
</script>
@endpush
