@extends('layouts.admin')

@section('title', 'Monthly & VIP Privilege Cards')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Monthly & VIP Cards</span>
            </div>
            <h1 class="page-title mb-1">
                <i class="fa-solid fa-crown text-amber" style="color: #f59e0b;"></i>
                <span>Monthly & Weekly VIP Privilege Cards</span>
            </h1>
            <p class="page-subtitle mb-0">Manage "Spend Less, Get More Gems!" monthly & weekly privilege cards, instant coin rewards, dynamic daily check-in schedules, and VIP outfits & frames.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.vip-cards.subscriptions') }}" class="btn btn-outline-secondary" style="border-radius: 8px; font-weight: 600; font-size: 13px;">
                <i class="fa-solid fa-users-viewfinder me-1"></i> View Subscriptions
            </a>
            <button type="button" class="btn-ch-primary" onclick="openCreateCardModal()">
                <i class="fa-solid fa-plus-circle me-1"></i> Add New VIP Card
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #f59e0b; background: #ffffff; border-radius: 12px; padding: 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Total VIP Cards</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ $totalCards }}</h3>
                    </div>
                    <div class="stat-icon-box d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: rgba(245, 158, 11, 0.15); color: #f59e0b; font-size: 20px;">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #10b981; background: #ffffff; border-radius: 12px; padding: 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Active in App</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ $activeCards }}</h3>
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
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Active Subscriptions</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ $activeSubscriptions }}</h3>
                    </div>
                    <div class="stat-icon-box d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: rgba(59, 130, 246, 0.15); color: #3b82f6; font-size: 20px;">
                        <i class="fa-solid fa-gem"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #ec4899; background: #ffffff; border-radius: 12px; padding: 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Total Subscriptions</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ $totalSubscriptions }}</h3>
                    </div>
                    <div class="stat-icon-box d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: rgba(236, 72, 153, 0.15); color: #ec4899; font-size: 20px;">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards Grid -->
    <div class="row g-4 mb-4">
        @forelse($cards as $card)
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden" style="background: #ffffff; border: 1px solid rgba(0,0,0,0.08) !important;">
                    <!-- Top Ribbon / Badge -->
                    <div class="d-flex justify-content-between align-items-center p-3" style="background: linear-gradient(135deg, {{ $card->card_color ?? '#FF4081' }}22, {{ $card->card_color ?? '#FF4081' }}44);">
                        <span class="badge" style="background: {{ $card->card_color ?? '#FF4081' }}; color: #fff; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px;">
                            {{ $card->badge_text ?? 'VIP' }}
                        </span>
                        <span class="badge bg-dark rounded-pill" style="font-size: 10px;">{{ $card->duration_days }} Days Duration</span>
                    </div>

                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <div class="mx-auto d-flex align-items-center justify-content-center rounded-circle shadow-sm" style="width: 64px; height: 64px; background: {{ $card->card_color ?? '#FF4081' }}20; color: {{ $card->card_color ?? '#FF4081' }}; font-size: 26px;">
                                <i class="fa-solid fa-crown"></i>
                            </div>
                        </div>

                        <h5 class="fw-bold text-dark mb-1">{{ $card->name }}</h5>
                        <p class="text-muted small mb-3">{{ $card->banner_tag ?? 'Spend Less, Get More Gems!' }}</p>

                        <!-- Price Box -->
                        <div class="p-2 rounded-3 mb-3" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                            <div class="fw-bolder text-primary fs-4">৳ {{ number_format($card->price_bdt, 2) }}</div>
                            <div class="text-muted small fw-semibold">{{ number_format($card->price_coins) }} Gems Required</div>
                        </div>

                        <!-- Rewards Breakdown -->
                        <div class="text-start mb-3" style="font-size: 12px;">
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Instant Reward:</span>
                                <span class="fw-bold text-success">+{{ number_format($card->instant_reward_coins) }} 💎</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Daily Bonus (Total):</span>
                                <span class="fw-bold text-warning">+{{ number_format($card->daily_checkin_total_coins) }} 💎</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted fw-bold">Total Return:</span>
                                <span class="fw-bold" style="color: {{ $card->card_color ?? '#ec4899' }};">{{ number_format($card->total_return_coins) }} 💎</span>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">Daily Schedule:</span>
                                <span class="fw-bold text-dark">{{ count($card->daily_schedule ?? []) }} Days Configured</span>
                            </div>
                        </div>

                        <!-- Extra Perks Preview -->
                        @if(!empty($card->extra_rewards))
                            <div class="d-flex flex-wrap gap-1 justify-content-center mb-3">
                                @foreach($card->extra_rewards as $perk)
                                    <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1" style="font-size: 10px; font-weight: 500; padding: 4px 8px;">
                                        @if(!empty($perk['image']))
                                            <img src="{{ str_starts_with($perk['image'], 'http') ? $perk['image'] : asset($perk['image']) }}" alt="" style="width: 14px; height: 14px; border-radius: 3px; object-fit: cover;">
                                        @else
                                            <i class="fa-solid fa-sparkles text-warning"></i>
                                        @endif
                                        <span>{{ $perk['title'] ?? ($perk['tag'] ?? 'Perk') }}</span>
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <!-- Status Badge -->
                        <div class="mb-3">
                            @if($card->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1" style="font-size: 11px;">Active in Mobile App</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1" style="font-size: 11px;">Disabled / Hidden</span>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="d-flex gap-2">
                            <form action="{{ route('admin.vip-cards.toggle-status', $card->id) }}" method="POST" class="flex-fill">
                                @csrf
                                <button type="submit" class="btn btn-sm w-100 {{ $card->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" style="font-size: 12px; font-weight: 600;">
                                    {{ $card->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            <button type="button" class="btn btn-sm btn-outline-primary flex-fill" style="font-size: 12px; font-weight: 600;" onclick='openEditCardModal(@json($card))'>
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </button>
                            <form action="{{ route('admin.vip-cards.destroy', $card->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this VIP Card package?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="fa-solid fa-crown text-muted mb-3" style="font-size: 48px;"></i>
                <h5 class="text-muted">No VIP Privilege Cards Configured Yet.</h5>
            </div>
        @endforelse
    </div>
</div>

<!-- ==================================================== -->
<!-- Dynamic Modal: Add / Edit VIP Privilege Card Package -->
<!-- ==================================================== -->
<div class="modal fade" id="vipCardModal" tabindex="-1" aria-labelledby="vipCardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom px-4 py-3" style="background: #f8fafc;">
                <h5 class="modal-title fw-bold d-flex align-items-center" id="vipCardModalLabel">
                    <i class="fa-solid fa-crown text-warning me-2"></i> VIP Privilege Card Package
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="vipCardForm" method="POST" action="{{ route('admin.vip-cards.store') }}" enctype="multipart/form-data">
                @csrf
                <div id="methodFieldContainer"></div>
                <div class="modal-body p-4" style="max-height: calc(85vh - 120px); overflow-y: auto;">
                    
                    <!-- Section 1: Basic Information -->
                    <div class="p-3 rounded-3 mb-4" style="background: #f1f5f9; border: 1px solid #e2e8f0;">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
                            <i class="fa-solid fa-id-card text-primary me-2"></i> 1. Card Basic Information
                        </h6>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Card Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="cardName" class="form-control" placeholder="e.g. New User Weekly Card" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Card Type Key <span class="text-danger">*</span></label>
                                <select name="card_type" id="cardType" class="form-select" required>
                                    <option value="new_user">New User Weekly (new_user)</option>
                                    <option value="super_monthly">Super Monthly (super_monthly)</option>
                                    <option value="luxury_monthly">Luxury Monthly (luxury_monthly)</option>
                                    <option value="super_weekly">Super Weekly (super_weekly)</option>
                                    <option value="custom_vip">Custom VIP Package (custom_vip)</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Price (BDT) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="0.01" name="price_bdt" id="cardPriceBdt" class="form-control" placeholder="300.00" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Price (Gems/Coins) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">💎</span>
                                    <input type="number" name="price_coins" id="cardPriceCoins" class="form-control" placeholder="8100" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Duration (Days) <span class="text-danger">*</span></label>
                                <input type="number" name="duration_days" id="cardDurationDays" class="form-control" placeholder="7 or 30" value="7" min="1" required onchange="onDurationChange()">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold text-success" style="font-size: 13px;">Instant Reward Coins <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text text-success">💎</span>
                                    <input type="number" name="instant_reward_coins" id="cardInstantCoins" class="form-control fw-bold" placeholder="8100" required oninput="calculateTotalReturn()">
                                </div>
                                <div class="form-text" style="font-size: 11px;">Credited to user wallet immediately upon purchase.</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold text-warning" style="font-size: 13px;">Daily Bonus Total Coins</label>
                                <div class="input-group">
                                    <span class="input-group-text text-warning">🎁</span>
                                    <input type="number" name="daily_checkin_total_coins" id="cardDailyBonusCoins" class="form-control fw-bold" placeholder="2020" oninput="calculateTotalReturn()">
                                </div>
                                <div class="form-text" style="font-size: 11px;">Sum of all daily schedule rewards below.</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold text-danger" style="font-size: 13px;">Total Return Coins</label>
                                <div class="input-group">
                                    <span class="input-group-text text-danger">💎</span>
                                    <input type="number" name="total_return_coins" id="cardTotalReturnCoins" class="form-control fw-bold" placeholder="10120">
                                </div>
                                <div class="form-text" style="font-size: 11px;">Instant + Daily Check-in Total.</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Badge Text</label>
                                <input type="text" name="badge_text" id="cardBadgeText" class="form-control" placeholder="HOT, 50% OFF, BEST VALUE">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Theme Color (Hex)</label>
                                <div class="input-group">
                                    <input type="color" id="cardColorPicker" class="form-control form-control-color" value="#FF4081" title="Choose color" onchange="document.getElementById('cardColor').value = this.value">
                                    <input type="text" name="card_color" id="cardColor" class="form-control" placeholder="#FF4081" value="#FF4081" oninput="document.getElementById('cardColorPicker').value = this.value">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Display Order</label>
                                <input type="number" name="sort_order" id="cardSortOrder" class="form-control" placeholder="0" value="0">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Banner Tagline</label>
                                <input type="text" name="banner_tag" id="cardBannerTag" class="form-control" placeholder="Spend Less, Get More Gems! Update to New User Weekly Card">
                            </div>

                            <!-- Card Icon, Animation & Background Media Assets -->
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Card Badge / Icon File</label>
                                <input type="file" name="icon" class="form-control" accept="image/*">
                                <small class="text-muted" style="font-size: 11px;">PNG, WebP, SVG for Card Header / Badge</small>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Card Animation File (.svga / .json / .webp)</label>
                                <input type="file" name="animation_file" class="form-control" accept=".svga,.json,.lottie,.webp,.gif,.mp4">
                                <small class="text-muted" style="font-size: 11px;">Lottie JSON, SVGA, WebP Animation</small>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Card Background Texture / Image</label>
                                <input type="file" name="bg_image" class="form-control" accept="image/*">
                                <small class="text-muted" style="font-size: 11px;">Custom card gradient / pattern banner</small>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Animation Format</label>
                                <select name="format" id="cardFormat" class="form-select">
                                    <option value="lottie" selected>Lottie JSON (.json)</option>
                                    <option value="svga">SVGA Animation (.svga)</option>
                                    <option value="webp">WebP Animation (.webp)</option>
                                    <option value="gif">Animated GIF (.gif)</option>
                                    <option value="image">Static Image</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-8">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Animation File URL (Remote URL fallback)</label>
                                <input type="text" name="animation_url" id="cardAnimationUrl" class="form-control" placeholder="https://.../vip_crown.json">
                            </div>
                            
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="cardIsActive" value="1" checked>
                                    <label class="form-check-label fw-semibold" for="cardIsActive">Active and Visible in Mobile App</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Dynamic Daily Check-in Schedule Builder (NO RAW JSON) -->
                    <div class="p-3 rounded-3 mb-4" style="background: #ffffff; border: 1px solid #cbd5e1; box-shadow: 0 1px 4px rgba(0,0,0,0.03);">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                            <div>
                                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center">
                                    <i class="fa-solid fa-calendar-days text-success me-2"></i> 2. "Get Schedule" Daily Check-in Rewards Breakdown
                                </h6>
                                <p class="text-muted mb-0" style="font-size: 11px;">Configure the coins and extra perk badge rewarded to users on each day of their subscription.</p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="autoGenerateSchedule()" style="font-size: 12px;">
                                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Auto-Generate Schedule
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="addScheduleRow()" style="font-size: 12px;">
                                    <i class="fa-solid fa-plus me-1"></i> Add Day Row
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearScheduleRows()" style="font-size: 12px;">
                                    <i class="fa-solid fa-trash-can"></i> Clear
                                </button>
                            </div>
                        </div>

                        <!-- Schedule Rows Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-2" id="scheduleTable" style="font-size: 13px;">
                                <thead style="background: #f8fafc;">
                                    <tr>
                                        <th style="width: 120px;" class="text-center">Day Number</th>
                                        <th style="width: 200px;">Check-in Coins (💎 Gems)</th>
                                        <th>Special Perk Badge / Extra Label (Optional)</th>
                                        <th style="width: 70px;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="scheduleRowsContainer">
                                    <!-- Dynamic Rows Rendered Here by JS -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Schedule Live Sum Indicator -->
                        <div class="d-flex justify-content-between align-items-center p-2 rounded-2 mt-2" style="background: #f8fafc; border: 1px dashed #94a3b8;">
                            <span class="text-muted" style="font-size: 12px;">
                                <i class="fa-solid fa-calculator text-primary me-1"></i> Total Daily Schedule Coins: <strong class="text-dark" id="scheduleTotalSum">0 💎</strong>
                            </span>
                            <button type="button" class="btn btn-sm btn-link text-primary p-0 text-decoration-none fw-semibold" style="font-size: 12px;" onclick="syncScheduleSumToForm()">
                                <i class="fa-solid fa-arrows-rotate me-1"></i> Sync with Daily Bonus Total field
                            </button>
                        </div>
                    </div>

                    <!-- Section 3: Extra Outfits & Rewards Perks Builder (NO RAW JSON) -->
                    <div class="p-3 rounded-3" style="background: #ffffff; border: 1px solid #cbd5e1; box-shadow: 0 1px 4px rgba(0,0,0,0.03);">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                            <div>
                                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center">
                                    <i class="fa-solid fa-sparkles text-warning me-2"></i> 3. Extra Outfits & Reward Perks (Icons, Frames, Badges)
                                </h6>
                                <p class="text-muted mb-0" style="font-size: 11px;">Add avatars, frames, VIP icons, or upload custom images shown in the mobile app Extra Reward section.</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-semibold" onclick="addPerkRow()" style="font-size: 12px;">
                                <i class="fa-solid fa-plus-circle me-1"></i> Add Reward Perk
                            </button>
                        </div>

                        <div id="perksContainer" class="d-flex flex-column gap-3">
                            <!-- Dynamic Perk Cards Rendered Here by JS -->
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-top px-4 py-3" style="background: #f8fafc;">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold" id="saveCardBtn">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save VIP Card Package
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Available Preset Icons for Extra Perks
const PRESET_ICONS = [
    { key: 'frame_avatar', label: 'Exclusive Avatar Frame', iconClass: 'fa-solid fa-circle-user text-primary' },
    { key: 'badge_svip', label: 'SVIP Badge Icon', iconClass: 'fa-solid fa-crown text-warning' },
    { key: 'lucky_card', label: 'Free Lucky Card', iconClass: 'fa-solid fa-ticket text-info' },
    { key: 'frame_gold', label: 'Super VIP Gold Frame', iconClass: 'fa-solid fa-gem text-warning' },
    { key: 'chat_bubble', label: 'Luxury Chat Bubble', iconClass: 'fa-solid fa-comment-dots text-purple' },
    { key: 'entry_anim', label: 'Privilege Entry Banner', iconClass: 'fa-solid fa-bullhorn text-danger' },
    { key: 'frame_diamond', label: 'Diamond Halo Frame', iconClass: 'fa-solid fa-diamond text-info' },
    { key: 'svip_crown', label: 'SVIP Crown Badge', iconClass: 'fa-solid fa-award text-amber' },
    { key: 'global_entry', label: 'Global Room Entry Effect', iconClass: 'fa-solid fa-earth-americas text-success' },
    { key: 'frame_green', label: 'Neon Green Frame', iconClass: 'fa-solid fa-shield-halved text-success' },
    { key: 'badge_green', label: 'VIP Weekly Icon', iconClass: 'fa-solid fa-medal text-success' },
];

let perkCounter = 0;

// ----------------------------------------------------
// Schedule Builder Functions
// ----------------------------------------------------
function addScheduleRow(day = null, coins = 300, extra = '') {
    const container = document.getElementById('scheduleRowsContainer');
    const rowCount = container.children.length;
    const currentDay = day !== null ? day : (rowCount + 1);

    const tr = document.createElement('tr');
    tr.id = `sched_row_${Date.now()}_${Math.random().toString(36).substr(2, 5)}`;
    tr.innerHTML = `
        <td class="text-center">
            <div class="input-group input-group-sm justify-content-center">
                <span class="input-group-text bg-light fw-bold">Day</span>
                <input type="number" name="schedule_day[]" class="form-control text-center fw-bold sched-day-input" value="${currentDay}" min="1" style="max-width: 60px;" required>
            </div>
        </td>
        <td>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light text-warning fw-bold">💎</span>
                <input type="number" name="schedule_coins[]" class="form-control fw-bold sched-coins-input" value="${coins}" placeholder="e.g. 500" required oninput="recalcScheduleSum()">
            </div>
        </td>
        <td>
            <input type="text" name="schedule_extra[]" class="form-control form-control-sm" value="${extra ? escapeHtml(extra) : ''}" placeholder="e.g. Card x1, Diamond Frame, Exclusive Badge">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeScheduleRow(this)" title="Remove Day">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        </td>
    `;
    container.appendChild(tr);
    recalcScheduleSum();
}

function removeScheduleRow(button) {
    const tr = button.closest('tr');
    if (tr) {
        tr.remove();
        reindexScheduleDays();
        recalcScheduleSum();
    }
}

function clearScheduleRows() {
    document.getElementById('scheduleRowsContainer').innerHTML = '';
    recalcScheduleSum();
}

function reindexScheduleDays() {
    const inputs = document.querySelectorAll('.sched-day-input');
    inputs.forEach((input, index) => {
        input.value = index + 1;
    });
}

function recalcScheduleSum() {
    const coinInputs = document.querySelectorAll('.sched-coins-input');
    let total = 0;
    coinInputs.forEach(input => {
        total += parseInt(input.value, 10) || 0;
    });
    document.getElementById('scheduleTotalSum').textContent = total.toLocaleString() + ' 💎';
}

function syncScheduleSumToForm() {
    const coinInputs = document.querySelectorAll('.sched-coins-input');
    let total = 0;
    coinInputs.forEach(input => {
        total += parseInt(input.value, 10) || 0;
    });
    document.getElementById('cardDailyBonusCoins').value = total;
    calculateTotalReturn();
}

function calculateTotalReturn() {
    const instant = parseInt(document.getElementById('cardInstantCoins').value, 10) || 0;
    const daily = parseInt(document.getElementById('cardDailyBonusCoins').value, 10) || 0;
    document.getElementById('cardTotalReturnCoins').value = instant + daily;
}

function onDurationChange() {
    const days = parseInt(document.getElementById('cardDurationDays').value, 10) || 7;
    const currentRows = document.querySelectorAll('#scheduleRowsContainer tr').length;
    if (currentRows === 0 || confirm(`Duration changed to ${days} days. Would you like to auto-generate the schedule rows for ${days} days?`)) {
        autoGenerateSchedule(days);
    }
}

function autoGenerateSchedule(forcedDays = null) {
    const days = forcedDays || parseInt(document.getElementById('cardDurationDays').value, 10) || 7;
    const instantCoins = parseInt(document.getElementById('cardInstantCoins').value, 10) || 8100;
    const cardType = document.getElementById('cardType').value;

    clearScheduleRows();

    if (days === 7 && cardType === 'new_user') {
        const presets = [
            { day: 1, coins: instantCoins > 0 ? instantCoins : 8100, extra: 'Card x1' },
            { day: 2, coins: 300, extra: '' },
            { day: 3, coins: 210, extra: '' },
            { day: 4, coins: 500, extra: '' },
            { day: 5, coins: 350, extra: '' },
            { day: 6, coins: 300, extra: '' },
            { day: 7, coins: 360, extra: 'Exclusive Badge' }
        ];
        presets.forEach(p => addScheduleRow(p.day, p.coins, p.extra));
    } else if (days === 7 && cardType === 'super_weekly') {
        const presets = [
            { day: 1, coins: instantCoins > 0 ? instantCoins : 16200, extra: 'Green Frame' },
            { day: 2, coins: 800, extra: '' },
            { day: 3, coins: 700, extra: '' },
            { day: 4, coins: 900, extra: '' },
            { day: 5, coins: 800, extra: '' },
            { day: 6, coins: 800, extra: '' },
            { day: 7, coins: 1000, extra: 'Weekly Card x1' }
        ];
        presets.forEach(p => addScheduleRow(p.day, p.coins, p.extra));
    } else if (days === 30 && cardType === 'super_monthly') {
        addScheduleRow(1, instantCoins > 0 ? instantCoins : 32940, 'Gold Frame');
        for (let d = 2; d <= 7; d++) {
            addScheduleRow(d, (d % 2 === 0) ? 1790 : 1210, '');
        }
        for (let d = 8; d <= 30; d++) {
            let extra = (d === 14) ? 'Bonus Card' : ((d === 30) ? 'SVIP 30D' : '');
            addScheduleRow(d, 656, extra);
        }
    } else if (days === 30 && cardType === 'luxury_monthly') {
        addScheduleRow(1, instantCoins > 0 ? instantCoins : 66600, 'Diamond Frame');
        addScheduleRow(2, 3500, '');
        addScheduleRow(3, 1790, '');
        addScheduleRow(4, 3500, '');
        addScheduleRow(5, 1790, '');
        addScheduleRow(6, 3500, '');
        addScheduleRow(7, 3500, '');
        for (let d = 8; d <= 30; d++) {
            let extra = (d === 14) ? 'Super Card x2' : ((d === 30) ? 'Luxury SVIP Crown' : '');
            addScheduleRow(d, 2953, extra);
        }
    } else {
        // Generic daily distribution
        addScheduleRow(1, instantCoins > 0 ? instantCoins : 1000, 'Bonus Outfit');
        for (let d = 2; d <= days; d++) {
            let extra = (d === days) ? 'Completion Badge' : '';
            addScheduleRow(d, 500, extra);
        }
    }

    syncScheduleSumToForm();
}

// ----------------------------------------------------
// Extra Rewards / Outfits Perks Builder Functions
// ----------------------------------------------------
function addPerkRow(perk = null) {
    const container = document.getElementById('perksContainer');
    const idx = perkCounter++;

    const title = perk ? (perk.title || '') : '';
    const tag = perk ? (perk.tag || '') : '';
    const icon = perk ? (perk.icon || 'frame_avatar') : 'frame_avatar';
    const image = perk ? (perk.image || '') : '';
    const isCustomImage = image && image.length > 0;

    const div = document.createElement('div');
    div.id = `perk_card_${idx}`;
    div.className = 'p-3 rounded-3 position-relative';
    div.style.background = '#f8fafc';
    div.style.border = '1px solid #e2e8f0';

    let optionsHtml = '';
    PRESET_ICONS.forEach(pi => {
        optionsHtml += `<option value="${pi.key}" ${icon === pi.key ? 'selected' : ''}>${pi.label}</option>`;
    });

    const previewSrc = isCustomImage ? (image.startsWith('http') ? image : '/' + image) : '';

    div.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge bg-secondary" style="font-size: 11px;">Perk #${container.children.length + 1}</span>
            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removePerkRow(${idx})" title="Remove Perk">
                <i class="fa-solid fa-xmark"></i> Remove
            </button>
        </div>
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <label class="form-label mb-1" style="font-size: 11px; font-weight: 600;">Perk Title <span class="text-danger">*</span></label>
                <input type="text" name="perk_title[]" class="form-control form-control-sm" value="${escapeHtml(title)}" placeholder="e.g. Exclusive Avatar Frame" required>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label mb-1" style="font-size: 11px; font-weight: 600;">Tag / Subtitle</label>
                <input type="text" name="perk_tag[]" class="form-control form-control-sm" value="${escapeHtml(tag)}" placeholder="e.g. Free Outfits, SVIP Icon">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label mb-1" style="font-size: 11px; font-weight: 600;">Preset Icon Key</label>
                <select name="perk_icon[]" class="form-select form-select-sm">
                    ${optionsHtml}
                </select>
            </div>
            <div class="col-12 col-md-2 text-center">
                <label class="form-label mb-1 d-block" style="font-size: 11px; font-weight: 600;">Upload Image / Icon</label>
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <label class="btn btn-sm btn-outline-primary mb-0 py-1 px-2" style="font-size: 11px; cursor: pointer;">
                        <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload
                        <input type="file" name="perk_file_${idx}" class="d-none" accept="image/*" onchange="previewPerkImage(this, ${idx})">
                    </label>
                    <input type="hidden" name="perk_existing_image[]" value="${escapeHtml(image)}">
                    <input type="hidden" name="perk_image_url[]" id="perk_url_${idx}" value="${escapeHtml(image)}">
                    
                    <div id="perk_preview_box_${idx}" class="d-inline-flex align-items-center justify-content-center rounded border bg-white" style="width: 32px; height: 32px; overflow: hidden;">
                        ${previewSrc ? `<img src="${previewSrc}" style="width: 100%; height: 100%; object-fit: cover;">` : `<i class="fa-solid fa-gift text-muted" style="font-size: 14px;"></i>`}
                    </div>
                </div>
            </div>
        </div>
    `;

    container.appendChild(div);
}

function previewPerkImage(input, idx) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewBox = document.getElementById(`perk_preview_box_${idx}`);
            if (previewBox) {
                previewBox.innerHTML = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removePerkRow(idx) {
    const el = document.getElementById(`perk_card_${idx}`);
    if (el) el.remove();
}

function clearPerkRows() {
    document.getElementById('perksContainer').innerHTML = '';
}

// ----------------------------------------------------
// Modal Openers (Create / Edit)
// ----------------------------------------------------
function openCreateCardModal() {
    document.getElementById('vipCardModalLabel').innerHTML = '<i class="fa-solid fa-crown text-warning me-2"></i> Add New VIP Privilege Card';
    document.getElementById('vipCardForm').action = "{{ route('admin.vip-cards.store') }}";
    document.getElementById('methodFieldContainer').innerHTML = '';
    
    document.getElementById('cardName').value = '';
    document.getElementById('cardType').value = 'new_user';
    document.getElementById('cardPriceBdt').value = '300.00';
    document.getElementById('cardPriceCoins').value = '8100';
    document.getElementById('cardDurationDays').value = '7';
    document.getElementById('cardInstantCoins').value = '8100';
    document.getElementById('cardDailyBonusCoins').value = '2020';
    document.getElementById('cardTotalReturnCoins').value = '10120';
    document.getElementById('cardBadgeText').value = 'HOT';
    document.getElementById('cardColor').value = '#FF4081';
    document.getElementById('cardColorPicker').value = '#FF4081';
    document.getElementById('cardBannerTag').value = 'Spend Less, Get More Gems! Update to New User Weekly Card';
    document.getElementById('cardSortOrder').value = '1';
    document.getElementById('cardIsActive').checked = true;

    // Populate default 7-day schedule
    autoGenerateSchedule(7);

    // Populate default perks
    clearPerkRows();
    addPerkRow({ title: 'Exclusive Avatar Frame', tag: 'Free Outfits', icon: 'frame_avatar' });
    addPerkRow({ title: 'Weekly Card Badge', tag: 'SVIP Icon', icon: 'badge_svip' });
    addPerkRow({ title: 'Free Lucky Card x1', tag: 'Free Card', icon: 'lucky_card' });

    new bootstrap.Modal(document.getElementById('vipCardModal')).show();
}

function openEditCardModal(card) {
    document.getElementById('vipCardModalLabel').innerHTML = '<i class="fa-solid fa-pen-to-square text-primary me-2"></i> Edit VIP Privilege Card: ' + card.name;
    document.getElementById('vipCardForm').action = "/admin/vip-cards/" + card.id;
    document.getElementById('methodFieldContainer').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    
    document.getElementById('cardName').value = card.name || '';
    document.getElementById('cardType').value = card.card_type || 'new_user';
    document.getElementById('cardPriceBdt').value = card.price_bdt || 0;
    document.getElementById('cardPriceCoins').value = card.price_coins || 0;
    document.getElementById('cardDurationDays').value = card.duration_days || 7;
    document.getElementById('cardInstantCoins').value = card.instant_reward_coins || 0;
    document.getElementById('cardDailyBonusCoins').value = card.daily_checkin_total_coins || 0;
    document.getElementById('cardTotalReturnCoins').value = card.total_return_coins || 0;
    document.getElementById('cardBadgeText').value = card.badge_text || '';
    document.getElementById('cardColor').value = card.card_color || '#FF4081';
    document.getElementById('cardColorPicker').value = card.card_color || '#FF4081';
    document.getElementById('cardBannerTag').value = card.banner_tag || '';
    document.getElementById('cardSortOrder').value = card.sort_order || 0;
    document.getElementById('cardIsActive').checked = !!card.is_active;

    // Render Daily Schedule
    clearScheduleRows();
    if (card.daily_schedule && Array.isArray(card.daily_schedule) && card.daily_schedule.length > 0) {
        card.daily_schedule.forEach(item => {
            addScheduleRow(item.day, item.coins, item.extra);
        });
    } else {
        autoGenerateSchedule(card.duration_days || 7);
    }

    // Render Extra Perks
    clearPerkRows();
    if (card.extra_rewards && Array.isArray(card.extra_rewards) && card.extra_rewards.length > 0) {
        card.extra_rewards.forEach(perk => {
            addPerkRow(perk);
        });
    } else {
        addPerkRow({ title: 'Exclusive Avatar Frame', tag: 'Free Outfits', icon: 'frame_avatar' });
    }

    new bootstrap.Modal(document.getElementById('vipCardModal')).show();
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>
@endpush
@endsection
