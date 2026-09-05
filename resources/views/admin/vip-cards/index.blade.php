@extends('layouts.admin')

@section('title', 'Premium VIP & Privilege Cards')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Premium VIP</span>
            </div>
            <h1 class="page-title mb-1">
                <i class="fa-solid fa-crown text-amber" style="color: #f59e0b;"></i>
                <span>Premium VIP Privilege Cards & Home Floating Banner</span>
            </h1>
            <p class="page-subtitle mb-0">Manage "Luxury Monthly Card", "Super Weekly Card", instant rewards, daily check-in bonuses, extra frames & outfits, and the home screen floating "Extra Gems" widget.</p>
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
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Total VIP Packages</span>
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

    <!-- Home Screen Floating VIP Widget Configuration Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: #ffffff; border: 1px solid rgba(0,0,0,0.08) !important;">
        <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                    <i class="fa-solid fa-cube"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0">Home Screen Floating VIP Widget ("Extra Gems" / Monthly Card)</h6>
                    <p class="text-muted mb-0" style="font-size: 12px;">Draggable floating button on the mobile app home screen that navigates users directly to the Premium VIP page.</p>
                </div>
            </div>
            <span class="badge {{ ($floatingBannerConfig['is_enabled'] ?? true) ? 'bg-success' : 'bg-secondary' }} rounded-pill px-3 py-2">
                {{ ($floatingBannerConfig['is_enabled'] ?? true) ? '● Widget Enabled' : '○ Widget Disabled' }}
            </span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.vip-cards.floating-banner') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-3 text-center">
                        <div class="p-3 rounded-4 position-relative d-inline-block shadow-sm" style="background: linear-gradient(135deg, #1e1b4b, #312e81); border: 2px solid #f59e0b; width: 140px;">
                            <img src="{{ $floatingBannerConfig['image_url'] ?? asset('assets/images/vip/floating_extra_gems.png') }}" alt="Floating Widget" class="img-fluid rounded-3 mb-2" style="max-height: 80px; object-fit: contain;">
                            <div class="badge bg-warning text-dark fw-bold rounded-pill px-2 py-1" style="font-size: 10px;">
                                {{ $floatingBannerConfig['title'] ?? 'Extra Gems' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-9">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Widget Title</label>
                                <input type="text" name="floating_vip_banner_title" class="form-control" value="{{ $floatingBannerConfig['title'] ?? 'Extra Gems' }}" placeholder="e.g. Extra Gems">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Tag / Subtitle</label>
                                <input type="text" name="floating_vip_banner_tag" class="form-control" value="{{ $floatingBannerConfig['tag'] ?? 'Monthly Card' }}" placeholder="e.g. Monthly Card">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Target Action</label>
                                <input type="text" name="floating_vip_banner_action" class="form-control" value="{{ $floatingBannerConfig['action_type'] ?? 'OPEN_PREMIUM_VIP' }}" readonly style="background: #f8fafc;">
                            </div>
                            <div class="col-12 col-md-8">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Upload Custom Floating Widget Image (PNG / WebP transparent)</label>
                                <input type="file" name="floating_banner_file" class="form-control" accept="image/*">
                            </div>
                            <div class="col-12 col-md-4 d-flex align-items-end">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="floating_vip_banner_enabled" id="floatingVipBannerSwitch" value="1" {{ ($floatingBannerConfig['is_enabled'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="floatingVipBannerSwitch" style="font-size: 13px;">Enable Floating Widget</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 text-end">
                            <button type="submit" class="btn btn-warning rounded-pill px-4 fw-semibold text-dark">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Save Floating Banner Settings
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Cards Grid -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-dark mb-0">
            <i class="fa-solid fa-layer-group text-primary me-2"></i> Active VIP Privilege Cards ({{ $cards->count() }})
        </h5>
        <span class="text-muted small">Cards displayed in the app matching screenshot layout.</span>
    </div>

    <div class="row g-4 mb-4">
        @forelse($cards as $card)
            <div class="col-12 col-md-6 col-xl-4">
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

                        <!-- Price Box with Strikethrough (Matching Screenshot) -->
                        <div class="p-2 rounded-3 mb-3" style="background: #0f172a; color: #ffffff; border-radius: 12px;">
                            <div class="fw-bolder text-warning fs-4">{{ $card->formatted_price_bdt }}</div>
                            @if($card->original_price_bdt)
                                <div class="text-secondary small" style="text-decoration: line-through; font-size: 12px;">
                                    {{ $card->formatted_original_price_bdt }}
                                    @if($card->discount_percent)
                                        <span class="badge bg-danger rounded-pill ms-1" style="font-size: 10px;">{{ $card->discount_percent }}% OFF</span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Rewards Breakdown matching Screenshot -->
                        <div class="text-start mb-3" style="font-size: 12px;">
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Instant Reward:</span>
                                <span class="fw-bold text-success">{{ $card->instant_reward_text ?: ('Gems in total ' . number_format($card->instant_reward_coins)) }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Daily Check-in Bonus:</span>
                                <span class="fw-bold text-warning">{{ $card->daily_checkin_text ?: ('Gems in total ' . number_format($card->daily_checkin_total_coins)) }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted fw-bold">Total Return:</span>
                                <span class="fw-bold" style="color: {{ $card->card_color ?? '#ec4899' }};">{{ number_format($card->total_return_coins) }} 💎</span>
                            </div>
                        </div>

                        <!-- Extra Perks Preview with Duration Tag -->
                        @if(!empty($card->extra_rewards))
                            <div class="text-start mb-1 text-muted small fw-semibold">Extra Rewards:</div>
                            <div class="d-flex flex-wrap gap-1 justify-content-start mb-3">
                                @foreach($card->extra_rewards as $perk)
                                    <span class="badge bg-dark text-light border d-inline-flex align-items-center gap-1" style="font-size: 10px; font-weight: 500; padding: 4px 8px; border-radius: 6px;">
                                        <span class="badge bg-warning text-dark" style="font-size: 9px; padding: 1px 4px;">{{ $perk['validity'] ?? ($perk['tag'] ?? 'Perk') }}</span>
                                        <span>{{ $perk['title'] ?? 'VIP Reward' }}</span>
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="d-flex flex-wrap gap-2 justify-content-center mt-3 pt-3 border-top">
                            <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3" onclick='previewCardRewards(@json($card))' title="Preview Avatar Frame & Animated Rewards Modal">
                                <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Preview Frame
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick='openEditCardModal(@json($card))'>
                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                            </button>
                            <form action="{{ route('admin.vip-cards.toggle-status', $card->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $card->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }} rounded-pill px-3">
                                    <i class="fa-solid {{ $card->is_active ? 'fa-eye-slash' : 'fa-eye' }} me-1"></i> {{ $card->is_active ? 'Disable' : 'Enable' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.vip-cards.destroy', $card->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this VIP Card?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info rounded-3 p-4 text-center">
                    <i class="fa-solid fa-circle-info fs-3 mb-2"></i>
                    <p class="mb-0">No VIP Privilege Cards created yet. Click "Add New VIP Card" above to get started.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Extra Rewards & Avatar Frame Preview Modal (Matching Screenshot 5) -->
<div class="modal fade" id="framePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg text-white" style="background: radial-gradient(circle at center, #1e1b4b 0%, #09090b 100%); border: 2px solid rgba(245, 158, 11, 0.4) !important;">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill" id="previewBadgeText" style="font-size: 11px;">REWARD PREVIEW</span>
                    <h6 class="modal-title fw-bold text-light mb-0" id="previewCardTitle">Card Outfits & Rewards</h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div class="position-relative d-inline-block my-3">
                    <!-- Glow effect halo behind frame -->
                    <div class="position-absolute top-50 start-50 translate-middle rounded-circle" style="width: 200px; height: 200px; background: radial-gradient(circle, rgba(236,72,153,0.35) 0%, rgba(245,158,11,0.15) 50%, transparent 70%); filter: blur(15px); pointer-events: none;"></div>
                    
                    <!-- Avatar Frame & Placeholder Profile -->
                    <div class="position-relative d-flex align-items-center justify-content-center" style="width: 240px; height: 240px;">
                        <!-- User Avatar in center -->
                        <div class="rounded-circle overflow-hidden shadow-lg position-relative" style="width: 100px; height: 100px; background: #334155; border: 3px solid #f59e0b;">
                            <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=200&auto=format&fit=crop&q=80" alt="Avatar Preview" class="w-100 h-100 object-fit-cover">
                        </div>
                        <!-- Animated Frame Overlay -->
                        <img id="previewFrameImage" src="{{ asset('assets/images/vip/new_star_frame.png') }}" alt="Frame Preview" class="position-absolute top-0 start-0 w-100 h-100" style="object-fit: contain; pointer-events: none; animation: pulseGlow 3s infinite alternate;">
                    </div>
                </div>

                <h5 class="fw-bolder text-warning mt-2 mb-1" id="previewFrameTitle">7 Days NEW STAR Frame</h5>
                <p class="text-secondary small mb-3" id="previewValidityText">Active on user profile for 7 Days upon purchase</p>

                <!-- Perks List in Modal -->
                <div class="p-3 rounded-3 text-start mb-3" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
                    <div class="small fw-bold text-warning mb-2"><i class="fa-solid fa-gift me-1"></i> Included Extra Outfits & Perks:</div>
                    <div id="previewPerksList" class="d-flex flex-column gap-2" style="font-size: 12px;"></div>
                </div>

                <div class="d-flex justify-content-between align-items-center p-2 rounded-3" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3);">
                    <span class="small fw-bold text-light">Card Selling Price:</span>
                    <span class="fs-6 fw-bold text-warning" id="previewPriceText">BDT 300.00</span>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 text-center justify-content-center">
                <button type="button" class="btn btn-warning text-dark fw-bold rounded-pill px-4" data-bs-dismiss="modal">
                    <i class="fa-solid fa-check me-1"></i> Got it
                </button>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes pulseGlow {
    0% { transform: scale(1); filter: drop-shadow(0 0 8px rgba(245,158,11,0.6)); }
    100% { transform: scale(1.03); filter: drop-shadow(0 0 18px rgba(236,72,153,0.8)); }
}
</style>

<!-- Add / Edit VIP Privilege Card Modal -->
<div class="modal fade" id="vipCardModal" tabindex="-1" aria-labelledby="vipCardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom px-4 py-3" style="background: #0f172a; color: #ffffff;">
                <h5 class="modal-title fw-bold" id="vipCardModalLabel">
                    <i class="fa-solid fa-crown text-warning me-2"></i> Premium VIP Card Package
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                <input type="text" name="name" id="cardName" class="form-control" placeholder="e.g. Luxury Monthly Card" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Card Type Key <span class="text-danger">*</span></label>
                                <select name="card_type" id="cardType" class="form-select" required onchange="onCardTypeChange()">
                                    <option value="new_user_weekly">New User Weekly (new_user_weekly)</option>
                                    <option value="super_monthly">Super Monthly (super_monthly)</option>
                                    <option value="luxury_monthly">Luxury Monthly (luxury_monthly)</option>
                                    <option value="super_weekly">Super Weekly (super_weekly)</option>
                                    <option value="trial_starter">Trial 3-Day Starter (trial_starter)</option>
                                    <option value="svip_quarterly">SVIP 90-Day Pass (svip_quarterly)</option>
                                    <option value="royal_semi_annual">Royal Sovereign 180-Day (royal_semi_annual)</option>
                                    <option value="custom_vip">Custom VIP Package (custom_vip)</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Selling / Offer Price (BDT) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="0.01" name="price_bdt" id="cardPriceBdt" class="form-control fw-bold text-primary" placeholder="2400.00" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Original / Crossed Price (BDT)</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="0.01" name="original_price_bdt" id="cardOriginalPriceBdt" class="form-control text-secondary" placeholder="4800.00">
                                </div>
                                <div class="form-text" style="font-size: 11px;">Displayed with strikethrough in UI (e.g. ৳ 4800.00).</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Price in Gems / Coins</label>
                                <div class="input-group">
                                    <span class="input-group-text">💎</span>
                                    <input type="number" name="price_coins" id="cardPriceCoins" class="form-control" placeholder="66600" required>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Duration (Days) <span class="text-danger">*</span></label>
                                <input type="number" name="duration_days" id="cardDurationDays" class="form-control" placeholder="30 or 7" value="30" min="1" required onchange="onDurationChange()">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold text-success" style="font-size: 13px;">Instant Reward Coins <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text text-success">💎</span>
                                    <input type="number" name="instant_reward_coins" id="cardInstantCoins" class="form-control fw-bold" placeholder="66600" required oninput="calculateTotalReturn()">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold text-success" style="font-size: 13px;">Instant Reward Custom Text</label>
                                <input type="text" name="instant_reward_text" id="cardInstantRewardText" class="form-control" placeholder="Gems in total 66600">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold text-warning" style="font-size: 13px;">Daily Bonus Total Coins</label>
                                <div class="input-group">
                                    <span class="input-group-text text-warning">🎁</span>
                                    <input type="number" name="daily_checkin_total_coins" id="cardDailyBonusCoins" class="form-control fw-bold" placeholder="87110" oninput="calculateTotalReturn()">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold text-warning" style="font-size: 13px;">Daily Check-in Custom Text</label>
                                <input type="text" name="daily_checkin_text" id="cardDailyCheckinText" class="form-control" placeholder="Gems in total 87110">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold text-danger" style="font-size: 13px;">Total Return Coins</label>
                                <div class="input-group">
                                    <span class="input-group-text text-danger">💎</span>
                                    <input type="number" name="total_return_coins" id="cardTotalReturnCoins" class="form-control fw-bold" placeholder="153710">
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Badge Text</label>
                                <input type="text" name="badge_text" id="cardBadgeText" class="form-control" placeholder="50% OFF, HOT, BEST VALUE">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Theme Color (Hex)</label>
                                <div class="input-group">
                                    <input type="color" id="cardColorPicker" class="form-control form-control-color" value="#2979FF" title="Choose color" onchange="document.getElementById('cardColor').value = this.value">
                                    <input type="text" name="card_color" id="cardColor" class="form-control" placeholder="#2979FF" value="#2979FF" oninput="document.getElementById('cardColorPicker').value = this.value">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Display Order</label>
                                <input type="number" name="sort_order" id="cardSortOrder" class="form-control" placeholder="0" value="0">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Banner Tagline</label>
                                <input type="text" name="banner_tag" id="cardBannerTag" class="form-control" placeholder="Luxury Monthly Card: 153,710 Gems + Outfits + Free Cards!">
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="cardIsActive" value="1" checked>
                                    <label class="form-check-label fw-semibold" for="cardIsActive">Active and Visible in Mobile App</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Dynamic Daily Check-in Schedule Builder -->
                    <div class="p-3 rounded-3 mb-4" style="background: #ffffff; border: 1px solid #cbd5e1;">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                            <div>
                                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center">
                                    <i class="fa-solid fa-calendar-days text-success me-2"></i> 2. Daily Check-in Rewards Schedule
                                </h6>
                                <p class="text-muted mb-0" style="font-size: 11px;">Configure coins and extra badges rewarded on each check-in day.</p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="autoGenerateSchedule()" style="font-size: 12px;">
                                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Auto-Generate
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

                    <!-- Section 3: Extra Outfits & Rewards Perks Builder -->
                    <div class="p-3 rounded-3" style="background: #ffffff; border: 1px solid #cbd5e1;">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                            <div>
                                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center">
                                    <i class="fa-solid fa-sparkles text-warning me-2"></i> 3. Extra Outfits & Reward Perks (30days Frame, Bubbles, Titles)
                                </h6>
                                <p class="text-muted mb-0" style="font-size: 11px;">Configure extra rewards shown in the 4-box grid (e.g. 30days Avatar Frame, 30days Chat Bubble, x30 Tickets).</p>
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
    { key: 'frame_diamond', label: '30D Diamond Halo Frame', iconClass: 'fa-solid fa-diamond text-info' },
    { key: 'frame_avatar', label: 'Avatar Frame (7D / 30D)', iconClass: 'fa-solid fa-circle-user text-primary' },
    { key: 'chat_bubble', label: 'Luxury Chat Bubble', iconClass: 'fa-solid fa-comment-dots text-purple' },
    { key: 'svip_crown', label: 'SVIP Crown Badge / Title', iconClass: 'fa-solid fa-award text-amber' },
    { key: 'lucky_card', label: 'Bonus Cards (x7 / x30)', iconClass: 'fa-solid fa-ticket text-info' },
    { key: 'frame_gold', label: 'Super VIP Gold Frame', iconClass: 'fa-solid fa-gem text-warning' },
    { key: 'badge_svip', label: 'SVIP Badge Icon', iconClass: 'fa-solid fa-crown text-warning' },
    { key: 'entry_anim', label: 'Privilege Entry Banner', iconClass: 'fa-solid fa-bullhorn text-danger' },
];

let perkCounter = 0;

function addScheduleRow(day = null, coins = 300, extra = '') {
    const container = document.getElementById('scheduleRowsContainer');
    const rowCount = container.children.length;
    const currentDay = day !== null ? day : (rowCount + 1);

    const tr = document.createElement('tr');
    tr.id = `sched_row_${Date.now()}_${Math.random().toString(36).substr(2, 5)}`;
    tr.innerHTML = `
        <td class="text-center fw-bold">
            <span class="badge bg-light text-dark border">Day ${currentDay}</span>
            <input type="hidden" name="schedule_day[]" value="${currentDay}">
        </td>
        <td>
            <div class="input-group input-group-sm">
                <span class="input-group-text">💎</span>
                <input type="number" name="schedule_coins[]" class="form-control form-control-sm fw-bold sched-coin-input" value="${coins}" required oninput="calculateScheduleSum()">
            </div>
        </td>
        <td>
            <input type="text" name="schedule_extra[]" class="form-control form-control-sm" value="${extra || ''}" placeholder="e.g. Diamond Frame or null">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger p-1" onclick="removeScheduleRow('${tr.id}')" title="Delete Row">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </td>
    `;
    container.appendChild(tr);
    calculateScheduleSum();
}

function removeScheduleRow(rowId) {
    const row = document.getElementById(rowId);
    if (row) {
        row.remove();
        renumberScheduleDays();
        calculateScheduleSum();
    }
}

function clearScheduleRows() {
    document.getElementById('scheduleRowsContainer').innerHTML = '';
    calculateScheduleSum();
}

function renumberScheduleDays() {
    const rows = document.querySelectorAll('#scheduleRowsContainer tr');
    rows.forEach((row, idx) => {
        const dayNum = idx + 1;
        const badge = row.querySelector('.badge');
        const hiddenInput = row.querySelector('input[name="schedule_day[]"]');
        if (badge) badge.innerText = `Day ${dayNum}`;
        if (hiddenInput) hiddenInput.value = dayNum;
    });
}

function calculateScheduleSum() {
    const inputs = document.querySelectorAll('.sched-coin-input');
    let total = 0;
    inputs.forEach(input => {
        total += parseInt(input.value || 0, 10);
    });
    document.getElementById('scheduleTotalSum').innerText = `${total.toLocaleString()} 💎`;
    return total;
}

function syncScheduleSumToForm() {
    const sum = calculateScheduleSum();
    document.getElementById('cardDailyBonusCoins').value = sum;
    calculateTotalReturn();
}

function onCardTypeChange() {
    const cardType = document.getElementById('cardType').value;
    if (cardType === 'new_user_weekly') {
        document.getElementById('cardName').value = 'New User Weekly Card';
        document.getElementById('cardPriceBdt').value = '300.00';
        document.getElementById('cardOriginalPriceBdt').value = '750.00';
        document.getElementById('cardPriceCoins').value = '8100';
        document.getElementById('cardDurationDays').value = '7';
        document.getElementById('cardInstantCoins').value = '8100';
        document.getElementById('cardInstantRewardText').value = 'Gems in total 8100';
        document.getElementById('cardDailyBonusCoins').value = '2020';
        document.getElementById('cardDailyCheckinText').value = 'Gems in total 2020';
        document.getElementById('cardTotalReturnCoins').value = '10120';
        document.getElementById('cardBadgeText').value = '60% OFF';
        document.getElementById('cardColor').value = '#EC4899';
        document.getElementById('cardColorPicker').value = '#EC4899';
        document.getElementById('cardBannerTag').value = 'New User Weekly Card: 10,120 Gems + New Star 3D Avatar Frame!';
        
        // Reset Perks
        document.getElementById('perksContainer').innerHTML = '';
        addPerkRow('7 Days NEW STAR Frame', '7days', '7days', 'frame_diamond', 'assets/images/vip/new_star_frame.png');
        addPerkRow('7 Days Newbie Glow', '7days', '7days', 'chat_bubble');
        addPerkRow('Bonus Lucky Cards x7', 'x7', 'x7', 'lucky_card');
    } else if (cardType === 'super_monthly') {
        document.getElementById('cardName').value = 'Super Monthly Card';
        document.getElementById('cardPriceBdt').value = '1200.00';
        document.getElementById('cardOriginalPriceBdt').value = '2400.00';
        document.getElementById('cardPriceCoins').value = '32940';
        document.getElementById('cardDurationDays').value = '30';
        document.getElementById('cardInstantCoins').value = '32940';
        document.getElementById('cardInstantRewardText').value = 'Gems in total 32940';
        document.getElementById('cardDailyBonusCoins').value = '26330';
        document.getElementById('cardDailyCheckinText').value = 'Gems in total 26330';
        document.getElementById('cardTotalReturnCoins').value = '59270';
        document.getElementById('cardBadgeText').value = '50% OFF';
        document.getElementById('cardColor').value = '#7C4DFF';
        document.getElementById('cardColorPicker').value = '#7C4DFF';
        document.getElementById('cardBannerTag').value = 'Super Monthly Card: 59,270 Gems + Outfits & Rewards!';
        
        document.getElementById('perksContainer').innerHTML = '';
        addPerkRow('30 Days Gold Frame', '30days', '30days', 'frame_gold');
        addPerkRow('30 Days Luxury Bubble', '30days', '30days', 'chat_bubble');
        addPerkRow('30 Days Privilege Badge', '30days', '30days', 'badge_svip');
        addPerkRow('Bonus Lucky Cards x15', 'x15', 'x15', 'lucky_card');
    } else if (cardType === 'luxury_monthly') {
        document.getElementById('cardName').value = 'Luxury Monthly Card';
        document.getElementById('cardPriceBdt').value = '2400.00';
        document.getElementById('cardOriginalPriceBdt').value = '4800.00';
        document.getElementById('cardPriceCoins').value = '66600';
        document.getElementById('cardDurationDays').value = '30';
        document.getElementById('cardInstantCoins').value = '66600';
        document.getElementById('cardInstantRewardText').value = 'Gems in total 66600';
        document.getElementById('cardDailyBonusCoins').value = '87110';
        document.getElementById('cardDailyCheckinText').value = 'Gems in total 87110';
        document.getElementById('cardTotalReturnCoins').value = '153710';
        document.getElementById('cardBadgeText').value = '50% OFF';
        document.getElementById('cardColor').value = '#2979FF';
        document.getElementById('cardColorPicker').value = '#2979FF';
        document.getElementById('cardBannerTag').value = 'Luxury Monthly Card: 153,710 Gems + Outfits + Free Cards!';
        
        document.getElementById('perksContainer').innerHTML = '';
        addPerkRow('30 Days Diamond Frame', '30days', '30days', 'frame_diamond');
        addPerkRow('30 Days Chat Bubble', '30days', '30days', 'chat_bubble');
        addPerkRow('30 Days VIP Title', '30days', '30days', 'svip_crown');
        addPerkRow('Bonus Lucky Cards x30', 'x30', 'x30', 'lucky_card');
    } else if (cardType === 'super_weekly') {
        document.getElementById('cardName').value = 'Super Weekly Card';
        document.getElementById('cardPriceBdt').value = '450.00';
        document.getElementById('cardOriginalPriceBdt').value = '642.86';
        document.getElementById('cardPriceCoins').value = '12150';
        document.getElementById('cardDurationDays').value = '7';
        document.getElementById('cardInstantCoins').value = '12150';
        document.getElementById('cardInstantRewardText').value = 'Gems in total 12150';
        document.getElementById('cardDailyBonusCoins').value = '2540';
        document.getElementById('cardDailyCheckinText').value = 'Gems in total 2540';
        document.getElementById('cardTotalReturnCoins').value = '14690';
        document.getElementById('cardBadgeText').value = '30% OFF';
        document.getElementById('cardColor').value = '#FF4081';
        document.getElementById('cardColorPicker').value = '#FF4081';
        document.getElementById('cardBannerTag').value = 'Super Weekly Card: 14,690 Gems + Outfits!';
        
        document.getElementById('perksContainer').innerHTML = '';
        addPerkRow('7 Days Emerald Frame', '7days', '7days', 'frame_avatar');
        addPerkRow('7 Days Chat Bubble', '7days', '7days', 'chat_bubble');
        addPerkRow('7 Days VIP Badge', '7days', '7days', 'badge_svip');
        addPerkRow('Bonus Lucky Cards x7', 'x7', 'x7', 'lucky_card');
    }
    autoGenerateSchedule();
}

function onDurationChange() {
    const duration = parseInt(document.getElementById('cardDurationDays').value || 7, 10);
    const rows = document.querySelectorAll('#scheduleRowsContainer tr');
    if (rows.length === 0 || rows.length !== duration) {
        autoGenerateSchedule();
    }
}

function autoGenerateSchedule() {
    const duration = parseInt(document.getElementById('cardDurationDays').value || 7, 10);
    const cardType = document.getElementById('cardType').value;
    clearScheduleRows();

    if (cardType === 'new_user_weekly') {
        addScheduleRow(1, 8100, 'NEW STAR Avatar Frame');
        addScheduleRow(2, 300, '');
        addScheduleRow(3, 210, '');
        addScheduleRow(4, 500, '');
        addScheduleRow(5, 300, '');
        addScheduleRow(6, 210, '');
        addScheduleRow(7, 500, 'New Star Title Badge');
    } else if (cardType === 'super_monthly') {
        addScheduleRow(1, 32940, 'Gold Frame');
        addScheduleRow(2, 1790, '');
        addScheduleRow(3, 1210, '');
        addScheduleRow(4, 1790, '');
        addScheduleRow(5, 1210, '');
        addScheduleRow(6, 1790, '');
        addScheduleRow(7, 1790, '');
        for (let d = 8; d <= 30; d++) {
            let extra = d === 14 ? 'Bonus Card' : (d === 30 ? 'SVIP 30D' : '');
            addScheduleRow(d, 656, extra);
        }
    } else if (cardType === 'luxury_monthly') {
        addScheduleRow(1, 66600, 'Diamond Frame');
        addScheduleRow(2, 3500, '');
        addScheduleRow(3, 1790, '');
        addScheduleRow(4, 3500, '');
        addScheduleRow(5, 1790, '');
        addScheduleRow(6, 3500, '');
        addScheduleRow(7, 3500, '');
        for (let d = 8; d <= 30; d++) {
            let extra = d === 14 ? 'Super Card x2' : (d === 30 ? 'Luxury SVIP Crown' : '');
            addScheduleRow(d, 2953, extra);
        }
    } else if (cardType === 'super_weekly') {
        addScheduleRow(1, 12150, '7D Emerald Frame');
        addScheduleRow(2, 240, '');
        addScheduleRow(3, 400, '');
        addScheduleRow(4, 500, '');
        addScheduleRow(5, 600, '');
        addScheduleRow(6, 300, '');
        addScheduleRow(7, 500, 'Weekly Card Badge');
    } else {
        const instantCoins = parseInt(document.getElementById('cardInstantCoins').value || 1000, 10);
        addScheduleRow(1, instantCoins, 'VIP Badge');
        for (let d = 2; d <= duration; d++) {
            addScheduleRow(d, 300, d === duration ? 'Exclusive Crown' : '');
        }
    }
    syncScheduleSumToForm();
}

function calculateTotalReturn() {
    const instant = parseInt(document.getElementById('cardInstantCoins').value || 0, 10);
    const daily = parseInt(document.getElementById('cardDailyBonusCoins').value || 0, 10);
    document.getElementById('cardTotalReturnCoins').value = instant + daily;
}

function previewCardRewards(card) {
    document.getElementById('previewBadgeText').innerText = card.badge_text || 'EXTRA REWARDS';
    document.getElementById('previewCardTitle').innerText = card.name || 'VIP Package';
    document.getElementById('previewPriceText').innerText = card.formatted_price_bdt || ('৳ ' + card.price_bdt);
    
    // Check if custom frame image exists or fallback to new_star_frame
    let frameImg = "{{ asset('assets/images/vip/new_star_frame.png') }}";
    let frameTitle = 'Special VIP Avatar Frame';
    let validityText = `Active on user profile for ${card.duration_days || 7} Days upon purchase`;
    
    if (Array.isArray(card.extra_rewards) && card.extra_rewards.length > 0) {
        const firstPerk = card.extra_rewards[0];
        frameTitle = firstPerk.title || `${card.duration_days} Days Avatar Frame`;
        if (firstPerk.image) {
            frameImg = firstPerk.image.startsWith('http') ? firstPerk.image : `/${firstPerk.image.replace(/^\//, '')}`;
        }
    }
    
    document.getElementById('previewFrameImage').src = frameImg;
    document.getElementById('previewFrameTitle').innerText = frameTitle;
    document.getElementById('previewValidityText').innerText = validityText;

    // Populate perks list
    const perksList = document.getElementById('previewPerksList');
    perksList.innerHTML = '';
    
    if (Array.isArray(card.extra_rewards) && card.extra_rewards.length > 0) {
        card.extra_rewards.forEach(p => {
            const row = document.createElement('div');
            row.className = 'd-flex align-items-center justify-content-between p-2 rounded';
            row.style.background = 'rgba(255,255,255,0.07)';
            row.innerHTML = `
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark">${p.validity || p.tag || 'VIP'}</span>
                    <span class="text-light fw-semibold">${p.title || 'Privilege Outfit'}</span>
                </div>
                <i class="fa-solid fa-circle-check text-success"></i>
            `;
            perksList.appendChild(row);
        });
    } else {
        perksList.innerHTML = `<div class="text-muted small">No extra perks specified for this package.</div>`;
    }

    new bootstrap.Modal(document.getElementById('framePreviewModal')).show();
}

function addPerkRow(title = '', tag = '30days', validity = '30days', icon = 'frame_diamond', image = '') {
    perkCounter++;
    const idx = perkCounter;
    const container = document.getElementById('perksContainer');

    let iconOptionsHtml = '';
    PRESET_ICONS.forEach(pi => {
        const selected = pi.key === icon ? 'selected' : '';
        iconOptionsHtml += `<option value="${pi.key}" ${selected}>${pi.label}</option>`;
    });

    const div = document.createElement('div');
    div.className = 'p-3 rounded-3 border perk-row-box';
    div.id = `perk_box_${idx}`;
    div.style.background = '#f8fafc';
    div.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
            <span class="badge bg-warning text-dark fw-bold" style="font-size: 11px;">
                <i class="fa-solid fa-sparkles me-1"></i> Reward Perk #${container.children.length + 1}
            </span>
            <button type="button" class="btn btn-sm btn-outline-danger p-1" onclick="document.getElementById('perk_box_${idx}').remove()" title="Remove Perk">
                <i class="fa-solid fa-trash-can"></i> Remove
            </button>
        </div>
        <div class="row g-2">
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold mb-1">Perk Title <span class="text-danger">*</span></label>
                <input type="text" name="perk_title[]" class="form-control form-control-sm" placeholder="e.g. 30 Days Avatar Frame" value="${title}" required>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small fw-semibold mb-1">Validity Tag</label>
                <input type="text" name="perk_validity[]" class="form-control form-control-sm" placeholder="30days / 7days / x30" value="${validity || tag || '30days'}">
                <input type="hidden" name="perk_tag[]" value="${tag || validity || '30days'}">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold mb-1">Preset Icon</label>
                <select name="perk_icon[]" class="form-select form-select-sm">
                    ${iconOptionsHtml}
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold mb-1">Upload Custom Icon/Image</label>
                <input type="file" name="perk_file_${idx}" class="form-control form-control-sm" accept="image/*">
                <input type="hidden" name="perk_existing_image[]" value="${image || ''}">
            </div>
        </div>
    `;
    container.appendChild(div);
}

function openCreateCardModal() {
    document.getElementById('vipCardModalLabel').innerHTML = '<i class="fa-solid fa-crown text-warning me-2"></i> Create New Premium VIP Card';
    document.getElementById('vipCardForm').action = "{{ route('admin.vip-cards.store') }}";
    document.getElementById('methodFieldContainer').innerHTML = '';
    document.getElementById('vipCardForm').reset();
    document.getElementById('scheduleRowsContainer').innerHTML = '';
    document.getElementById('perksContainer').innerHTML = '';

    // Add default perks
    addPerkRow('30 Days Avatar Frame', '30days', '30days', 'frame_diamond');
    addPerkRow('30 Days Chat Bubble', '30days', '30days', 'chat_bubble');
    addPerkRow('30 Days VIP Title Badge', '30days', '30days', 'svip_crown');
    addPerkRow('Free Bonus Cards x30', 'x30', 'x30', 'lucky_card');

    autoGenerateSchedule();
    new bootstrap.Modal(document.getElementById('vipCardModal')).show();
}

function openEditCardModal(card) {
    document.getElementById('vipCardModalLabel').innerHTML = `<i class="fa-solid fa-crown text-warning me-2"></i> Edit "${card.name}"`;
    document.getElementById('vipCardForm').action = `/admin/vip-cards/${card.id}`;
    document.getElementById('methodFieldContainer').innerHTML = '@method("PUT")';

    document.getElementById('cardName').value = card.name || '';
    document.getElementById('cardType').value = card.card_type || 'luxury_monthly';
    document.getElementById('cardPriceBdt').value = card.price_bdt || '';
    document.getElementById('cardOriginalPriceBdt').value = card.original_price_bdt || '';
    document.getElementById('cardPriceCoins').value = card.price_coins || '';
    document.getElementById('cardDurationDays').value = card.duration_days || 30;
    document.getElementById('cardInstantCoins').value = card.instant_reward_coins || '';
    document.getElementById('cardInstantRewardText').value = card.instant_reward_text || '';
    document.getElementById('cardDailyBonusCoins').value = card.daily_checkin_total_coins || '';
    document.getElementById('cardDailyCheckinText').value = card.daily_checkin_text || '';
    document.getElementById('cardTotalReturnCoins').value = card.total_return_coins || '';
    document.getElementById('cardBadgeText').value = card.badge_text || '';
    document.getElementById('cardColor').value = card.card_color || '#2979FF';
    document.getElementById('cardColorPicker').value = card.card_color || '#2979FF';
    document.getElementById('cardSortOrder').value = card.sort_order || 0;
    document.getElementById('cardBannerTag').value = card.banner_tag || '';
    document.getElementById('cardIsActive').checked = !!card.is_active;

    // Populate Schedule Rows
    document.getElementById('scheduleRowsContainer').innerHTML = '';
    if (Array.isArray(card.daily_schedule) && card.daily_schedule.length > 0) {
        card.daily_schedule.forEach(item => {
            addScheduleRow(item.day, item.coins, item.extra || '');
        });
    } else {
        autoGenerateSchedule();
    }
    calculateScheduleSum();

    // Populate Perks
    document.getElementById('perksContainer').innerHTML = '';
    if (Array.isArray(card.extra_rewards) && card.extra_rewards.length > 0) {
        card.extra_rewards.forEach(p => {
            addPerkRow(p.title || '', p.tag || '30days', p.validity || p.tag || '30days', p.icon || 'frame_diamond', p.image || '');
        });
    } else {
        addPerkRow('30 Days Avatar Frame', '30days', '30days', 'frame_diamond');
        addPerkRow('30 Days Chat Bubble', '30days', '30days', 'chat_bubble');
        addPerkRow('30 Days VIP Title Badge', '30days', '30days', 'svip_crown');
        addPerkRow('Free Bonus Cards x30', 'x30', 'x30', 'lucky_card');
    }

    new bootstrap.Modal(document.getElementById('vipCardModal')).show();
}
</script>
@endpush
@endsection
