@extends('layouts.admin')

@section('title', 'Monthly & VIP Privilege Cards')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Monthly & VIP Cards</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-crown text-amber" style="color: #f59e0b;"></i>
                <span>Monthly & Weekly VIP Privilege Cards</span>
            </h1>
            <p class="page-subtitle">Manage "Spend Less, Get More Gems!" monthly & weekly privilege cards, instant coin rewards, daily check-in bonus schedules, and VIP outfits for mobile app users.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.vip-cards.subscriptions') }}" class="btn btn-outline-secondary" style="border-radius: 8px; font-weight: 600; font-size: 13px;">
                <i class="fa-solid fa-users-viewfinder me-1"></i> View Subscriptions
            </a>
            <button type="button" class="btn-ch-primary" onclick="openCreateCardModal()">
                <i class="fa-solid fa-plus-circle"></i> Add New VIP Card
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #f59e0b;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Total VIP Cards</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ $totalCards }}</h3>
                    </div>
                    <div class="stat-icon-box" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #10b981;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Active in App</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ $activeCards }}</h3>
                    </div>
                    <div class="stat-icon-box" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #3b82f6;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Active Subscriptions</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ $activeSubscriptions }}</h3>
                    </div>
                    <div class="stat-icon-box" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                        <i class="fa-solid fa-gem"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #ec4899;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Total Subscriptions</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ $totalSubscriptions }}</h3>
                    </div>
                    <div class="stat-icon-box" style="background: rgba(236, 72, 153, 0.15); color: #ec4899;">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards Grid -->
    <div class="row g-4">
        @forelse($cards as $card)
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden" style="background: #ffffff; border: 1px solid rgba(0,0,0,0.06) !important;">
                    <!-- Top Ribbon / Badge -->
                    <div class="d-flex justify-content-between align-items-center p-3" style="background: linear-gradient(135deg, {{ $card->card_color ?? '#FF4081' }}15, {{ $card->card_color ?? '#FF4081' }}35);">
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
                                <span class="fw-bold text-pink" style="color: #ec4899;">{{ number_format($card->total_return_coins) }} 💎</span>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">Daily Schedule Days:</span>
                                <span class="fw-bold text-dark">{{ count($card->daily_schedule ?? []) }} Days Breakdown</span>
                            </div>
                        </div>

                        <!-- Extra Perks Pills -->
                        @if(!empty($card->extra_rewards))
                            <div class="d-flex flex-wrap gap-1 justify-content-center mb-3">
                                @foreach($card->extra_rewards as $perk)
                                    <span class="badge bg-light text-dark border" style="font-size: 10px; font-weight: 500;">
                                        <i class="fa-solid fa-sparkles text-warning me-1"></i>{{ $perk['title'] ?? ($perk['tag'] ?? 'Perk') }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <!-- Status Badge -->
                        <div class="mb-3">
                            @if($card->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1" style="font-size: 11px;">Active in App</span>
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
                            <form action="{{ route('admin.vip-cards.destroy', $card->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this VIP Card?');">
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

<!-- Create / Edit Card Modal -->
<div class="modal fade" id="vipCardModal" tabindex="-1" aria-labelledby="vipCardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="vipCardModalLabel">
                    <i class="fa-solid fa-crown text-amber me-2" style="color: #f59e0b;"></i> VIP Privilege Card Package
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="vipCardForm" method="POST" action="{{ route('admin.vip-cards.store') }}">
                @csrf
                <div id="methodFieldContainer"></div>
                <div class="modal-body pt-3">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Card Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="cardName" class="form-control" placeholder="e.g. New User Weekly Card" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Card Type Key <span class="text-danger">*</span></label>
                            <select name="card_type" id="cardType" class="form-select" required>
                                <option value="new_user">New User Weekly (new_user)</option>
                                <option value="super_monthly">Super Monthly (super_monthly)</option>
                                <option value="luxury_monthly">Luxury Monthly (luxury_monthly)</option>
                                <option value="super_weekly">Super Weekly (super_weekly)</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Price (BDT) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" name="price_bdt" id="cardPriceBdt" class="form-control" placeholder="300.00" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Price (Gems/Coins) <span class="text-danger">*</span></label>
                            <input type="number" name="price_coins" id="cardPriceCoins" class="form-control" placeholder="8100" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Duration (Days) <span class="text-danger">*</span></label>
                            <input type="number" name="duration_days" id="cardDurationDays" class="form-control" placeholder="7 or 30" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Instant Reward Coins <span class="text-danger">*</span></label>
                            <input type="number" name="instant_reward_coins" id="cardInstantCoins" class="form-control" placeholder="8100" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Daily Bonus Total Coins <span class="text-danger">*</span></label>
                            <input type="number" name="daily_checkin_total_coins" id="cardDailyBonusCoins" class="form-control" placeholder="2020" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Total Return Coins <span class="text-danger">*</span></label>
                            <input type="number" name="total_return_coins" id="cardTotalReturnCoins" class="form-control" placeholder="10120" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Badge Text</label>
                            <input type="text" name="badge_text" id="cardBadgeText" class="form-control" placeholder="HOT, 50% OFF, BEST VALUE">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Theme Color (Hex)</label>
                            <input type="text" name="card_color" id="cardColor" class="form-control" placeholder="#FF4081">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Banner Tagline</label>
                            <input type="text" name="banner_tag" id="cardBannerTag" class="form-control" placeholder="Spend Less, Get More Gems! Update to New User Weekly Card">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Daily Schedule Breakdown (JSON)</label>
                            <textarea name="daily_schedule_raw" id="cardDailySchedule" class="form-control font-monospace" rows="4" placeholder='[{"day": 1, "coins": 8100, "extra": "Card x1"}, {"day": 2, "coins": 300}]'></textarea>
                            <div class="form-text text-muted" style="font-size: 11px;">JSON array specifying coins for each day in the check-in schedule.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Extra Outfits / Rewards Perks (JSON)</label>
                            <textarea name="extra_rewards_raw" id="cardExtraRewards" class="form-control font-monospace" rows="3" placeholder='[{"title": "Exclusive Avatar Frame", "tag": "Free Outfits"}, {"title": "Weekly Badge", "tag": "SVIP"}]'></textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="cardIsActive" value="1" checked>
                                <label class="form-check-label fw-semibold" for="cardIsActive">Active and Visible in Mobile App</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" id="saveCardBtn">Save Card Package</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openCreateCardModal() {
    document.getElementById('vipCardModalLabel').innerHTML = '<i class="fa-solid fa-crown text-amber me-2" style="color: #f59e0b;"></i> Add New VIP Privilege Card';
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
    document.getElementById('cardBannerTag').value = 'Spend Less, Get More Gems!';
    document.getElementById('cardDailySchedule').value = JSON.stringify([
        {"day": 1, "coins": 8100, "extra": "Card x1"},
        {"day": 2, "coins": 300, "extra": null},
        {"day": 3, "coins": 210, "extra": null},
        {"day": 4, "coins": 500, "extra": null},
        {"day": 5, "coins": 350, "extra": null},
        {"day": 6, "coins": 300, "extra": null},
        {"day": 7, "coins": 360, "extra": "Exclusive Badge"}
    ], null, 2);
    document.getElementById('cardExtraRewards').value = JSON.stringify([
        {"title": "Exclusive Avatar Frame", "tag": "Free Outfits", "icon": "frame_avatar"},
        {"title": "Weekly Card Badge", "tag": "SVIP Icon", "icon": "badge_svip"},
        {"title": "Free Lucky Card x1", "tag": "Free Card", "icon": "lucky_card"}
    ], null, 2);
    document.getElementById('cardIsActive').checked = true;

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
    document.getElementById('cardBannerTag').value = card.banner_tag || '';
    document.getElementById('cardDailySchedule').value = card.daily_schedule ? JSON.stringify(card.daily_schedule, null, 2) : '';
    document.getElementById('cardExtraRewards').value = card.extra_rewards ? JSON.stringify(card.extra_rewards, null, 2) : '';
    document.getElementById('cardIsActive').checked = !!card.is_active;

    new bootstrap.Modal(document.getElementById('vipCardModal')).show();
}
</script>
@endpush
@endsection
