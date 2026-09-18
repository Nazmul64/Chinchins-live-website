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
            <p class="page-subtitle mb-0">
                Configure required host earning coins for Level 1 to Level 10+, upload custom avatar frame images (Base) directly to <code class="bg-light px-2 py-1 rounded text-primary fw-bold">public/uploads/bases/</code>, and manage automatic profile picture frame wrapping in the mobile app.
            </p>
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
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Upload Storage</span>
                        <h3 class="fw-bolder mt-1 mb-0" style="font-size: 15px; color: #3b82f6;">uploads/bases/</h3>
                    </div>
                    <div class="stat-icon-box d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: rgba(59, 130, 246, 0.15); color: #3b82f6; font-size: 20px;">
                        <i class="fa-solid fa-folder-open"></i>
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
                        <select class="form-select form-select-sm bg-dark text-white border-secondary" id="previewLevelSelector" style="width: 240px; border-radius: 8px;" onchange="updateLivePreview(this.value)">
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
                            <img src="{{ $bases->first()?->base_frame_image_url ?? asset('uploads/bases/profile_base_royal_gold.svg') }}" 
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
                    <i class="fa-solid fa-sliders text-primary me-2"></i> Level 1 to 10+ Configuration & Direct Image Upload Table
                </h4>
                <p class="text-muted mb-0" style="font-size: 13px;">
                    Set coin threshold, level title, and directly upload new custom Base Frame images (stored in <code class="text-primary">uploads/bases/</code>). Click "Save All Level Changes" to update all levels at once.
                </p>
            </div>
            <button type="submit" form="batchLevelsForm" class="btn-ch-primary">
                <i class="fa-solid fa-floppy-disk me-1"></i> Save All Level Changes
            </button>
        </div>

        <div class="card-body p-0">
            <form action="{{ route('admin.profile-bases.batch-update') }}" method="POST" enctype="multipart/form-data" id="batchLevelsForm">
                @csrf
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead style="background: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; color: #475569; font-weight: 700;">
                            <tr>
                                <th class="ps-4" style="width: 80px;">Level</th>
                                <th style="width: 240px;">Base Frame & Direct Upload</th>
                                <th style="min-width: 170px;">Level Name</th>
                                <th style="width: 170px;">Required Coins</th>
                                <th style="width: 140px;">Badge & Icon</th>
                                <th style="min-width: 200px;">Privilege Description</th>
                                <th style="width: 80px;" class="text-center">Active</th>
                                <th class="pe-4 text-end" style="width: 110px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bases as $base)
                            <tr style="border-bottom: 1px solid #f1f5f9;" id="levelRow_{{ $base->level }}">
                                <!-- Level Number Badge -->
                                <td class="ps-4">
                                    <span class="badge rounded-pill fw-bold" onclick="selectLevelPreview({{ $base->level }})" style="background: {{ $base->badge_color }}; color: #ffffff; padding: 6px 12px; font-size: 12px; cursor: pointer;" title="Click to preview Lv.{{ $base->level }} in top card">
                                        Lv.{{ $base->level }}
                                    </span>
                                </td>

                                <!-- Frame Base Thumbnail & Direct Upload -->
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative d-flex align-items-center justify-content-center" onclick="selectLevelPreview({{ $base->level }})" style="width: 60px; height: 60px; flex-shrink: 0; cursor: pointer; transition: transform 0.2s ease;" title="Click to preview this frame in top Live Preview card" onmouseover="this.style.transform='scale(1.12)'" onmouseout="this.style.transform='scale(1)'">
                                            <!-- Sample Avatar inside frame -->
                                            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80" alt="Avatar" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; z-index: 1;">
                                            <!-- Overlay Profile Base Frame -->
                                            <img src="{{ $base->base_frame_image_url }}" alt="Base Frame" id="rowPreview_{{ $base->id }}" style="position: absolute; top: 0; left: 0; width: 60px; height: 60px; object-fit: contain; z-index: 2; pointer-events: none;">
                                        </div>
                                        <div class="d-flex flex-column gap-1">
                                            <!-- Direct Instant AJAX File Upload for this row -->
                                            <label class="btn btn-sm btn-light border d-flex align-items-center gap-1 mb-0 py-1 px-2" id="uploadLabel_{{ $base->id }}" style="font-size: 11px; cursor: pointer; border-radius: 6px;">
                                                <i class="fa-solid fa-upload text-primary" id="uploadIcon_{{ $base->id }}"></i> 
                                                <span id="uploadText_{{ $base->id }}">Upload Image</span>
                                                <input type="file" name="frame_files[{{ $base->id }}]" accept=".svg,.png,.webp,.jpg,.jpeg,.gif" class="d-none" onchange="ajaxUploadRowFrame(this, {{ $base->id }}, 'rowPreview_{{ $base->id }}', {{ $base->level }})">
                                            </label>
                                            
                                            <!-- Preset Dropdown with Instant Live Preview -->
                                            <select name="levels[{{ $base->id }}][preset_frame]" class="form-select form-select-sm" style="font-size: 10px; width: 145px; border-radius: 6px; padding: 2px 6px;" onchange="previewPresetChange(this, 'rowPreview_{{ $base->id }}', {{ $base->level }})">
                                                @if(!array_key_exists($base->base_frame_image, $availablePresetFrames))
                                                    <option value="{{ $base->base_frame_image }}" selected>
                                                        ★ Current Custom Frame
                                                    </option>
                                                @endif
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
                                        <input type="number" name="levels[{{ $base->id }}][required_coins]" class="form-control form-control-sm border-start-0" value="{{ $base->required_coins }}" min="0" required style="font-weight: 700; color: #0f172a;">
                                    </div>
                                </td>

                                <!-- Badge Icon & Color -->
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <select name="levels[{{ $base->id }}][badge_icon]" class="form-select form-select-sm" style="width: 80px; font-size: 11px; border-radius: 6px;">
                                            <option value="star" {{ $base->badge_icon == 'star' ? 'selected' : '' }}>⭐ Star</option>
                                            <option value="crown" {{ $base->badge_icon == 'crown' ? 'selected' : '' }}>👑 Crown</option>
                                            <option value="gem" {{ $base->badge_icon == 'gem' ? 'selected' : '' }}>💎 Gem</option>
                                            <option value="fire" {{ $base->badge_icon == 'fire' ? 'selected' : '' }}>🔥 Fire</option>
                                            <option value="bolt" {{ $base->badge_icon == 'bolt' ? 'selected' : '' }}>⚡ Bolt</option>
                                            <option value="shield" {{ $base->badge_icon == 'shield' ? 'selected' : '' }}>🛡️ Shield</option>
                                            <option value="user" {{ $base->badge_icon == 'user' ? 'selected' : '' }}>👤 User</option>
                                        </select>
                                        <input type="color" name="levels[{{ $base->id }}][badge_color]" class="form-control form-control-color form-control-sm" value="{{ $base->badge_color }}" title="Choose badge color" style="width: 32px; height: 31px; padding: 2px; border-radius: 6px;">
                                    </div>
                                </td>

                                <!-- Privilege Description -->
                                <td>
                                    <input type="text" name="levels[{{ $base->id }}][privilege_text]" class="form-control form-control-sm" value="{{ $base->privilege_text }}" placeholder="Unlocks frame & perks" style="border-radius: 6px;">
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
                                        <button type="button" class="btn btn-sm btn-outline-primary" style="border-radius: 6px; font-size: 11px;" onclick="openEditBaseModal({{ json_encode($base) }})" title="Modal Edit & Frame Upload">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </button>
                                        @if($base->level > 0)
                                        <button type="button" class="btn btn-sm btn-outline-danger" style="border-radius: 6px; font-size: 11px;" onclick="confirmDeleteBase({{ $base->id }}, {{ $base->level }})" title="Delete Level">
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
                        <i class="fa-solid fa-folder-open text-primary me-1"></i> All uploaded images are automatically saved in <code class="text-primary fw-bold">public/uploads/bases/</code>
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
                        <p class="text-muted mb-0" style="font-size: 12px;">Saved directly into <code class="text-primary">public/uploads/bases/</code></p>
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
                            <small class="text-muted" style="font-size: 11px;">Image will be uploaded to <code>public/uploads/bases/</code>.</small>
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
<!-- ========================================== -->
<!-- ✏️ Edit Level Base & Upload Frame Modal -->
<!-- ========================================== -->
<div class="modal fade" id="editBaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 42px; height: 42px; background: rgba(59, 130, 246, 0.15); color: #3b82f6; font-size: 18px;">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="editModalTitle">Edit Level Base & Frame</h5>
                        <p class="text-muted mb-0" style="font-size: 12px;">Real-time live preview of frame, badge, and level configuration.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="editBaseForm" action="" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Left Column: Live Interactive Avatar & Frame Preview -->
                        <div class="col-12 col-lg-5">
                            <div class="card border-0 rounded-4 p-4 text-center h-100 position-relative shadow-sm" style="background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%); min-height: 380px;">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="badge rounded-pill px-3 py-1 text-white" style="background: rgba(255,255,255,0.12); font-size: 11px; letter-spacing: 0.5px;">
                                        <i class="fa-solid fa-circle text-success me-1 fa-beat-fade" style="font-size: 8px;"></i> LIVE PREVIEW
                                    </span>
                                    <small class="text-white-50" style="font-size: 11px;">Updates in real-time</small>
                                </div>

                                <!-- Live Avatar & Frame Container -->
                                <div class="my-auto py-3">
                                    <div class="position-relative d-inline-block mx-auto" style="width: 150px; height: 150px;">
                                        <!-- Avatar Profile Photo -->
                                        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=120&auto=format&fit=crop&q=80" 
                                             alt="Avatar" 
                                             id="modalPreviewAvatarImg"
                                             class="rounded-circle shadow" 
                                             style="width: 104px; height: 104px; object-fit: cover; position: absolute; top: 23px; left: 23px; z-index: 1;">
                                        
                                        <!-- Overlaid Custom / Selected Base Frame -->
                                        <img src="" 
                                             alt="Base Frame" 
                                             id="modalPreviewFrameImg" 
                                             class="position-absolute" 
                                             style="width: 150px; height: 150px; top: 0; left: 0; pointer-events: none; z-index: 2; object-fit: contain; transition: all 0.25s ease;">
                                        
                                        <!-- Level Badge Tag at Bottom -->
                                        <span class="position-absolute badge rounded-pill shadow" 
                                              id="modalPreviewBadge" 
                                              style="bottom: 2px; left: 50%; transform: translateX(-50%); z-index: 3; font-size: 11px; padding: 4px 12px; background: #f59e0b; color: #ffffff; border: 2px solid #ffffff; white-space: nowrap;">
                                            <i class="fa-solid fa-star me-1" id="modalPreviewBadgeIcon"></i> <span id="modalPreviewBadgeText">Lv.1</span>
                                        </span>
                                    </div>

                                    <!-- Level Title & Required Coins -->
                                    <h5 class="fw-bold text-white mt-3 mb-1" id="modalPreviewTitle">Level 1</h5>
                                    <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                        <span class="badge bg-warning text-dark fw-bold rounded-pill" id="modalPreviewCoins" style="font-size: 11px;">
                                            <i class="fa-solid fa-coins me-1"></i> 1,000 Coins Required
                                        </span>
                                    </div>
                                    <p class="text-white-50 mb-3 px-2" style="font-size: 12px;" id="modalPreviewPrivilege">
                                        Standard Avatar Base Frame
                                    </p>

                                    <!-- Avatar Switcher to Test Frame On Different Models -->
                                    <div class="d-flex align-items-center justify-content-center gap-2 pt-2 border-top border-secondary border-opacity-25">
                                        <small class="text-white-50" style="font-size: 10px;">Test Avatar:</small>
                                        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=60&auto=format&fit=crop&q=80" class="rounded-circle border border-2 border-white" style="width: 26px; height: 26px; object-fit: cover; cursor: pointer;" onclick="changeModalPreviewAvatar(this.src)" title="Model 1">
                                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=60&auto=format&fit=crop&q=80" class="rounded-circle border border-2 border-white" style="width: 26px; height: 26px; object-fit: cover; cursor: pointer;" onclick="changeModalPreviewAvatar(this.src)" title="Model 2">
                                        <img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?w=60&auto=format&fit=crop&q=80" class="rounded-circle border border-2 border-white" style="width: 26px; height: 26px; object-fit: cover; cursor: pointer;" onclick="changeModalPreviewAvatar(this.src)" title="Model 3">
                                        <img src="{{ asset('assets/images/users/avatar-1.jpg') }}" class="rounded-circle border border-2 border-white" style="width: 26px; height: 26px; object-fit: cover; cursor: pointer;" onclick="changeModalPreviewAvatar(this.src)" title="Default Avatar">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Edit Controls & File Upload -->
                        <div class="col-12 col-lg-7">
                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold" style="font-size: 13px;">Level Number</label>
                                    <input type="text" id="editLevelNum" class="form-control bg-light" readonly style="border-radius: 8px; font-weight: 700;">
                                </div>
                                <div class="col-12 col-md-8">
                                    <label class="form-label fw-bold" style="font-size: 13px;">Level Title / Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="editName" class="form-control" required style="border-radius: 8px;" oninput="updateModalPreviewName(this.value)">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold" style="font-size: 13px;">Required Earning Coins <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light" style="color: #f59e0b;"><i class="fa-solid fa-coins"></i></span>
                                        <input type="number" name="required_coins" id="editRequiredCoins" class="form-control" min="0" required style="border-radius: 0 8px 8px 0;" oninput="updateModalPreviewCoins(this.value)">
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold" style="font-size: 13px;">Choose Preset Base Frame</label>
                                    <select name="preset_frame" id="editPresetFrame" class="form-select" style="border-radius: 8px;" onchange="previewEditModalPreset(this)">
                                        <option value="">-- Keep Current / Uploaded --</option>
                                        @foreach($availablePresetFrames as $path => $label)
                                            <option value="{{ $path }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted" style="font-size: 11px;">Select to instantly preview on avatar.</small>
                                </div>

                                <!-- File Upload with instant live preview -->
                                <div class="col-12">
                                    <label class="form-label fw-bold" style="font-size: 13px;">OR Replace / Upload Custom Frame (PNG / SVG / WebP)</label>
                                    <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                                        <div class="rounded-3 p-1 d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px; background: #0f172a; border: 1px solid #334155; flex-shrink: 0;">
                                            <img src="" alt="Thumbnail" id="editCurrentFrameImg" style="width: 52px; height: 52px; object-fit: contain;">
                                        </div>
                                        <div class="flex-grow-1">
                                            <input type="file" name="frame_image" id="editFrameFileInput" class="form-control" accept=".png,.svg,.webp,.jpg,.jpeg,.gif" style="border-radius: 8px;" onchange="previewEditModalFile(this)">
                                            <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                                <i class="fa-solid fa-cloud-arrow-up text-primary me-1"></i> Uploads directly to <code class="text-primary">public/uploads/bases/</code> with instant preview on avatar.
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold" style="font-size: 13px;">Badge Icon</label>
                                    <select name="badge_icon" id="editBadgeIcon" class="form-select" style="border-radius: 8px;" onchange="updateModalPreviewIcon(this.value)">
                                        <option value="star">⭐ Star</option>
                                        <option value="crown">👑 Crown</option>
                                        <option value="gem">💎 Gem</option>
                                        <option value="fire">🔥 Fire</option>
                                        <option value="bolt">⚡ Bolt</option>
                                        <option value="shield">🛡️ Shield</option>
                                        <option value="trophy">🏆 Trophy</option>
                                        <option value="dollar-sign">💲 Dollar</option>
                                        <option value="user">👤 User</option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold" style="font-size: 13px;">Badge Color (Hex)</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" name="badge_color" id="editBadgeColor" class="form-control form-control-color" style="width: 44px; height: 38px; padding: 2px; border-radius: 8px;" oninput="updateModalPreviewColor(this.value)">
                                        <input type="text" id="editBadgeColorText" class="form-control" style="border-radius: 8px; font-family: monospace; font-size: 12px;" oninput="document.getElementById('editBadgeColor').value=this.value; updateModalPreviewColor(this.value);">
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold" style="font-size: 13px;">Glow Color / Aura</label>
                                    <input type="text" name="glow_color" id="editGlowColor" class="form-control" placeholder="rgba(245, 158, 11, 0.45)" style="border-radius: 8px;" oninput="updateModalPreviewGlow(this.value)">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold" style="font-size: 13px;">Privilege / Unlock Perks Description</label>
                                    <input type="text" name="privilege_text" id="editPrivilegeText" class="form-control" placeholder="e.g. Unlocks Royal Crown Frame & VIP Entrance" style="border-radius: 8px;" oninput="updateModalPreviewPrivilege(this.value)">
                                </div>

                                <div class="col-12">
                                    <div class="form-check form-switch mt-1">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editIsActive" style="cursor: pointer;">
                                        <label class="form-check-label fw-bold" for="editIsActive" style="font-size: 13px;">Active in App (Unlocked for eligible users)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 pb-4 px-4 bg-light d-flex justify-content-between align-items-center">
                    <span class="text-muted" style="font-size: 12px;">
                        <i class="fa-solid fa-circle-info text-primary me-1"></i> Saving will update the level base and apply the frame in the app.
                    </span>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">Cancel</button>
                        <button type="submit" class="btn-ch-primary px-4">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Update Level Base
                        </button>
                    </div>
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
    // Instant AJAX single-file upload for table rows (avoids 413 huge batch payload)
    function ajaxUploadRowFrame(input, baseId, targetImgId, level) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];

        // Preview locally first
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById(targetImgId);
            if (img) img.src = e.target.result;
        };
        reader.readAsDataURL(file);

        // UI Feedback
        const icon = document.getElementById('uploadIcon_' + baseId);
        const text = document.getElementById('uploadText_' + baseId);
        if (icon) icon.className = 'fa-solid fa-spinner fa-spin text-primary';
        if (text) text.textContent = 'Uploading...';

        const formData = new FormData();
        formData.append('frame_image', file);
        formData.append('_token', '{{ csrf_token() }}');

        fetch(`/admin/profile-bases/${baseId}/upload-frame`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) {
                if (res.status === 413) {
                    throw new Error('Image size is too large for the Nginx web server buffer! Please check Nginx client_max_body_size.');
                }
                return res.json().then(data => { throw new Error(data.message || 'Upload failed'); });
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                if (icon) icon.className = 'fa-solid fa-check text-success';
                if (text) text.textContent = 'Saved!';
                setTimeout(() => {
                    if (icon) icon.className = 'fa-solid fa-upload text-primary';
                    if (text) text.textContent = 'Upload Image';
                }, 2500);

                // Update select dropdown data attribute and live preview
                const select = document.getElementById('previewLevelSelector');
                if (select) {
                    const opt = select.querySelector(`option[value="${level}"]`);
                    if (opt) {
                        opt.setAttribute('data-frame', data.image_url);
                        if (select.value == level) {
                            updateLivePreview(level);
                        }
                    }
                }
            }
        })
        .catch(err => {
            alert('Upload notice: ' + err.message);
            if (icon) icon.className = 'fa-solid fa-triangle-exclamation text-danger';
            if (text) text.textContent = 'Error';
            setTimeout(() => {
                if (icon) icon.className = 'fa-solid fa-upload text-primary';
                if (text) text.textContent = 'Upload Image';
            }, 3000);
        });
    }

    // Instant live preview when a preset frame is selected in dropdown
    function previewPresetChange(selectElement, targetImgId, level) {
        const val = selectElement.value;
        if (!val) return;
        
        // Resolve full URL
        const url = val.startsWith('http') ? val : ('/' + val.replace(/^\/+/, ''));
        const img = document.getElementById(targetImgId);
        if (img) img.src = url;

        // Also update top Interactive Live Preview card if this level is selected
        const topSelect = document.getElementById('previewLevelSelector');
        if (topSelect) {
            const opt = topSelect.querySelector(`option[value="${level}"]`);
            if (opt) {
                opt.setAttribute('data-frame', url);
                if (topSelect.value == level) {
                    updateLivePreview(level);
                }
            }
        }
    }

    // Live Preview for file inputs in table rows
    function previewRowFile(input, targetImgId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById(targetImgId);
                if (img) img.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Live Preview Switcher in Header Section
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

    // Select and preview a specific level from table click
    function selectLevelPreview(level) {
        const select = document.getElementById('previewLevelSelector');
        if (select) {
            select.value = level;
            updateLivePreview(level);
            scrollToPreview();
        }
    }

    // Auto-sync preview on initial page load
    document.addEventListener('DOMContentLoaded', function() {
        const select = document.getElementById('previewLevelSelector');
        if (select && select.value !== undefined) {
            updateLivePreview(select.value);
        }
    });

    function scrollToPreview() {
        document.getElementById('liveAvatarPreviewSection')?.scrollIntoView({ behavior: 'smooth' });
    }

    function openCreateBaseModal() {
        new bootstrap.Modal(document.getElementById('createBaseModal')).show();
    }

    // Switch avatar model in modal preview
    function changeModalPreviewAvatar(src) {
        const img = document.getElementById('modalPreviewAvatarImg');
        if (img) img.src = src;
    }

    // Real-time text & badge listeners for modal preview
    function updateModalPreviewName(val) {
        const el = document.getElementById('modalPreviewTitle');
        if (el) el.textContent = val || 'Level Base';
    }

    function updateModalPreviewCoins(val) {
        const el = document.getElementById('modalPreviewCoins');
        const count = parseInt(val || 0);
        if (el) el.innerHTML = `<i class="fa-solid fa-coins me-1"></i> ${count.toLocaleString()} Coins Required`;
    }

    function updateModalPreviewColor(hex) {
        const badge = document.getElementById('modalPreviewBadge');
        if (badge) badge.style.backgroundColor = hex;
        const colorInput = document.getElementById('editBadgeColor');
        const colorText = document.getElementById('editBadgeColorText');
        if (colorInput && colorInput.value !== hex) colorInput.value = hex;
        if (colorText && colorText.value !== hex) colorText.value = hex;
    }

    function updateModalPreviewIcon(icon) {
        const iconEl = document.getElementById('modalPreviewBadgeIcon');
        if (iconEl) iconEl.className = `fa-solid fa-${icon} me-1`;
    }

    function updateModalPreviewGlow(glow) {
        const frameImg = document.getElementById('modalPreviewFrameImg');
        if (frameImg && glow) {
            frameImg.style.filter = `drop-shadow(0 0 10px ${glow})`;
        }
    }

    function updateModalPreviewPrivilege(text) {
        const el = document.getElementById('modalPreviewPrivilege');
        if (el) el.textContent = text || 'Standard Avatar Base Frame';
    }

    // Live preview when a new file is chosen in the Edit modal
    function previewEditModalFile(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const dataUrl = e.target.result;
                // Update small thumbnail in form
                const thumb = document.getElementById('editCurrentFrameImg');
                if (thumb) thumb.src = dataUrl;
                // Update large avatar overlaid frame in modal live preview card
                const previewFrame = document.getElementById('modalPreviewFrameImg');
                if (previewFrame) previewFrame.src = dataUrl;

                // Reset preset frame dropdown so uploaded file has clean precedence
                const presetSelect = document.getElementById('editPresetFrame');
                if (presetSelect) presetSelect.value = '';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Live preview when a preset is selected in the Edit modal
    function previewEditModalPreset(select) {
        const val = select.value;
        if (val) {
            const url = val.startsWith('http') ? val : ('/' + val.replace(/^\/+/, ''));
            // Update small thumbnail
            const thumb = document.getElementById('editCurrentFrameImg');
            if (thumb) thumb.src = url;
            // Update large avatar overlaid frame
            const previewFrame = document.getElementById('modalPreviewFrameImg');
            if (previewFrame) previewFrame.src = url;

            // Clear file input so chosen preset takes effect
            const fileInput = document.getElementById('editFrameFileInput');
            if (fileInput) fileInput.value = '';
        }
    }

    function openEditBaseModal(base) {
        const form = document.getElementById('editBaseForm');
        form.action = `/admin/profile-bases/${base.id}`;

        document.getElementById('editModalTitle').textContent = `Edit Level ${base.level} Base & Frame`;
        document.getElementById('editLevelNum').value = `Level ${base.level}`;
        document.getElementById('editName').value = base.name || '';
        document.getElementById('editRequiredCoins').value = base.required_coins || 0;
        document.getElementById('editBadgeIcon').value = base.badge_icon || 'star';
        document.getElementById('editBadgeColor').value = base.badge_color || '#f59e0b';
        document.getElementById('editBadgeColorText').value = base.badge_color || '#f59e0b';
        document.getElementById('editGlowColor').value = base.glow_color || 'rgba(245, 158, 11, 0.45)';
        document.getElementById('editPrivilegeText').value = base.privilege_text || '';
        document.getElementById('editIsActive').checked = !!base.is_active;

        // Resolve frame URL
        const currentUrl = base.base_frame_image_url || (base.base_frame_image ? ('/' + base.base_frame_image.replace(/^\/+/, '')) : '');
        
        // Update both thumbnail and modal live avatar preview
        const thumb = document.getElementById('editCurrentFrameImg');
        if (thumb) thumb.src = currentUrl;

        const previewFrame = document.getElementById('modalPreviewFrameImg');
        if (previewFrame) {
            previewFrame.src = currentUrl;
            previewFrame.style.filter = `drop-shadow(0 0 10px ${base.glow_color || 'rgba(245, 158, 11, 0.45)'})`;
        }

        // Update modal preview texts & badges
        updateModalPreviewName(base.name || `Level ${base.level}`);
        updateModalPreviewCoins(base.required_coins || 0);
        updateModalPreviewColor(base.badge_color || '#f59e0b');
        updateModalPreviewIcon(base.badge_icon || 'star');
        updateModalPreviewPrivilege(base.privilege_text || 'Standard Avatar Base Frame');
        document.getElementById('modalPreviewBadgeText').textContent = `Lv.${base.level}`;

        // Pre-select preset dropdown if matches
        const presetSelect = document.getElementById('editPresetFrame');
        if (presetSelect) {
            presetSelect.value = base.base_frame_image || '';
        }

        // Reset file input
        const fileInput = document.getElementById('editFrameFileInput');
        if (fileInput) fileInput.value = '';

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
