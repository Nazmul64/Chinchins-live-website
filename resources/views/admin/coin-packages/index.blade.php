@extends('layouts.admin')

@section('title', 'Coin Packages & Store Pricing')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Coin Packages</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-gem text-pink" style="color: #ec4899;"></i>
                <span>Coin Packages & Store Offers</span>
            </h1>
            <p class="page-subtitle">Configure deposit coin tiers, bonus coins (+Bonus), discount badges (e.g. 50% OFF, Best Value) for the mobile app recharge screen.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn-ch-primary" onclick="openCreatePackageModal()">
                <i class="fa-solid fa-plus-circle"></i> Add New Package
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #ec4899;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Total Packages</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ $packages->count() }}</h3>
                    </div>
                    <div class="stat-icon-box" style="background: rgba(236, 72, 153, 0.15); color: #ec4899;">
                        <i class="fa-solid fa-gem"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #10b981;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Active in App</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ $packages->where('is_active', true)->count() }}</h3>
                    </div>
                    <div class="stat-icon-box" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #f59e0b;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Featured / Popular</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ $packages->where('is_popular', true)->count() }}</h3>
                    </div>
                    <div class="stat-icon-box" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                        <i class="fa-solid fa-fire"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #3b82f6;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Highest Tier</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ number_format($packages->max('total_coins') ?: 0) }}</h3>
                    </div>
                    <div class="stat-icon-box" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Packages Grid (Live Mobile App Mockup Style) -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0" style="font-size: 16px;">
            <i class="fa-solid fa-mobile-screen-button text-primary me-2"></i> Mobile App Store Packages Preview
        </h5>
        <small class="text-muted">Users see these cards on the Gems/Coins Recharge screen</small>
    </div>

    <div class="row g-4 mb-5">
        @forelse($packages as $package)
            <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                <div class="app-pkg-card {{ $package->is_popular ? 'popular-card' : '' }} {{ !$package->is_active ? 'opacity-60' : '' }}">
                    <!-- Card Top Tag / Badge -->
                    <div class="d-flex justify-content-between align-items-center mb-3" style="min-height: 26px;">
                        @if($package->badge)
                            <span class="pkg-badge {{ $package->is_popular ? 'badge-pink-glow' : 'badge-subtle' }}">
                                {{ $package->badge }}
                            </span>
                        @else
                            <span></span>
                        @endif

                        <!-- Active Toggle Form -->
                        <form action="{{ route('admin.coin-packages.toggle-status', $package->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="status-pill-modern {{ $package->is_active ? 'active' : 'inactive' }}" style="border: none; cursor: pointer;" title="Click to toggle status">
                                <span class="status-pulsing-dot"></span>
                                {{ $package->is_active ? 'Active' : 'Disabled' }}
                            </button>
                        </form>
                    </div>

                    <!-- Diamond Icon + Base Coins -->
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="pkg-gem-icon">
                            <i class="fa-solid fa-gem" style="color: #fbbf24;"></i>
                        </div>
                        <div class="pkg-coins-amount">
                            {{ number_format($package->coins) }}
                        </div>
                    </div>

                    <!-- Bonus Coins line -->
                    @if($package->bonus_coins > 0)
                        <div class="pkg-bonus-text mb-3">
                            <i class="fa-solid fa-plus" style="font-size: 10px;"></i> {{ number_format($package->bonus_coins) }} Bonus
                            @if($package->bonus_percentage > 0)
                                <span class="text-muted" style="font-size: 11px; font-weight: 500;">({{ $package->bonus_percentage }}%)</span>
                            @endif
                        </div>
                    @else
                        <div class="pkg-bonus-placeholder mb-3">Standard tier</div>
                    @endif

                    <!-- Price Button Preview -->
                    <div class="pkg-price-btn {{ $package->is_popular ? 'price-pink' : 'price-dark' }}">
                        ৳{{ number_format($package->price) }}
                    </div>

                    <!-- Total summary & Action Footer -->
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top" style="border-color: rgba(255,255,255,0.08) !important;">
                        <small class="text-muted" style="font-size: 11px;">
                            Total: <strong class="text-light">{{ number_format($package->total_coins) }}</strong> Gems
                        </small>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn-ch-icon" style="width: 30px; height: 30px;" title="Edit Package" onclick='openEditPackageModal(@json($package))'>
                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                            </button>
                            <form action="{{ route('admin.coin-packages.destroy', $package->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this coin package?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-ch-icon" style="width: 30px; height: 30px; color: var(--danger);" title="Delete Package">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="card p-5 border-0 rounded-4 shadow-sm text-center" style="background: var(--card-bg-light); border: 1px dashed var(--border-color) !important;">
                    <i class="fa-solid fa-gem fa-4x text-muted mb-3" style="opacity: 0.3;"></i>
                    <h4 class="fw-bold">No Coin Packages Configured</h4>
                    <p class="text-muted mb-4">Create coin packages and bonus promotions for mobile app users.</p>
                    <button type="button" class="btn-ch-primary mx-auto" onclick="openCreatePackageModal()">
                        <i class="fa-solid fa-plus-circle"></i> Add First Package
                    </button>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Modal for Create / Edit Package -->
<div class="modal fade" id="packageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-modern-dialog" style="max-width: 680px;">
        <div class="modal-content modal-modern-content">
            <div class="modal-modern-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-box" style="width: 44px; height: 44px; font-size: 18px; background: rgba(236, 72, 153, 0.15); color: #ec4899;">
                        <i class="fa-solid fa-gem"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="pkgModalTitle">Add Coin Package</h5>
                        <small class="text-muted">Configure store package, bonus coins, and discount badges</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="pkgForm" method="POST" action="{{ route('admin.coin-packages.store') }}">
                @csrf
                <div id="pkgMethodSpoof"></div>
                <div class="modal-modern-body">
                    <div class="row g-3">
                        <!-- Left inputs -->
                        <div class="col-12 col-md-7">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold" style="font-size: 13px;">Base Coins / Gems <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold text-warning"><i class="fa-solid fa-gem me-1"></i></span>
                                        <input type="number" name="coins" id="pkgCoins" class="form-control fw-bold" placeholder="e.g. 32000" value="32000" min="1" required oninput="updateModalPreview()">
                                    </div>
                                    <small class="text-muted" style="font-size: 11px;">Primary coin quantity purchased</small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold" style="font-size: 13px;">Bonus Coins (+Bonus)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-success-subtle fw-bold text-success"><i class="fa-solid fa-gift me-1"></i> +</span>
                                        <input type="number" name="bonus_coins" id="pkgBonusCoins" class="form-control fw-bold text-success" placeholder="e.g. 8000" value="8000" min="0" oninput="updateModalPreview()">
                                    </div>
                                    <small class="text-muted" style="font-size: 11px;">Extra free coins given with this package</small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold" style="font-size: 13px;">Price in BDT (৳) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold text-primary">৳ BDT</span>
                                        <input type="number" step="any" name="price" id="pkgPrice" class="form-control fw-bold" placeholder="e.g. 550" value="550" min="1" required oninput="updateModalPreview()">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold" style="font-size: 13px;">Promotion Badge / Tag (Optional)</label>
                                    <input type="text" name="badge" id="pkgBadge" class="form-control rounded-3" placeholder="e.g. 🔥 50% OFF, Best Value, +30% Free" oninput="updateModalPreview()">
                                    <!-- Quick Badge Suggestions -->
                                    <div class="d-flex flex-wrap gap-1 mt-2">
                                        <span class="quick-chip-btn" onclick="selectQuickBadge('🔥 50% OFF')">🔥 50% OFF</span>
                                        <span class="quick-chip-btn" onclick="selectQuickBadge('Best Value')">Best Value</span>
                                        <span class="quick-chip-btn" onclick="selectQuickBadge('+30% Free')">+30% Free</span>
                                        <span class="quick-chip-btn" onclick="selectQuickBadge('VIP Bonus')">VIP Bonus</span>
                                        <span class="quick-chip-btn" onclick="selectQuickBadge('Popular')">Popular</span>
                                        <span class="quick-chip-btn text-danger" onclick="selectQuickBadge('')">Clear</span>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6">
                                    <label class="form-label fw-bold" style="font-size: 13px;">Sort Order</label>
                                    <input type="number" name="sort_order" id="pkgSortOrder" class="form-control rounded-3" value="0" min="0">
                                </div>

                                <div class="col-12">
                                    <div class="form-check form-switch p-0 d-flex align-items-center gap-3 mb-2">
                                        <input class="form-check-input ms-0" type="checkbox" name="is_popular" id="pkgIsPopular" value="1" style="width: 40px; height: 20px; cursor: pointer;" onchange="updateModalPreview()">
                                        <label class="form-check-label fw-bold" for="pkgIsPopular" style="cursor: pointer; font-size: 13px;">
                                            <i class="fa-solid fa-fire text-danger me-1"></i> Highlight as Most Popular / Selected
                                        </label>
                                    </div>

                                    <div class="form-check form-switch p-0 d-flex align-items-center gap-3">
                                        <input class="form-check-input ms-0" type="checkbox" name="is_active" id="pkgIsActive" value="1" checked style="width: 40px; height: 20px; cursor: pointer;">
                                        <label class="form-check-label fw-bold" for="pkgIsActive" style="cursor: pointer; font-size: 13px;">
                                            Active & Visible in Mobile App
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Live Card Preview -->
                        <div class="col-12 col-md-5">
                            <label class="form-label fw-bold mb-2" style="font-size: 13px;">
                                <i class="fa-solid fa-eye text-primary me-1"></i> Live App Card Preview
                            </label>
                            <div class="p-3 rounded-4" style="background: #0f172a; border: 1px solid #334155; min-height: 280px; display: flex; flex-direction: column; justify-content: space-between;">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2" style="min-height: 24px;">
                                        <span id="previewModalBadge" class="pkg-badge badge-pink-glow" style="font-size: 10px;">🔥 50% OFF</span>
                                        <span class="text-muted" style="font-size: 10px;"><i class="fa-solid fa-mobile text-pink"></i> Preview</span>
                                    </div>

                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i class="fa-solid fa-gem" style="color: #fbbf24; font-size: 18px;"></i>
                                        <span id="previewModalCoins" style="font-size: 22px; font-weight: 800; color: #fff; letter-spacing: 0.5px;">32,000</span>
                                    </div>

                                    <div id="previewModalBonus" class="pkg-bonus-text mb-3" style="font-size: 13px;">
                                        <i class="fa-solid fa-plus" style="font-size: 10px;"></i> 8,000 Bonus
                                    </div>

                                    <div id="previewModalPriceBtn" class="pkg-price-btn price-pink text-center">
                                        ৳550
                                    </div>
                                </div>

                                <div class="mt-3 pt-3 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
                                    <small class="text-muted d-block" style="font-size: 11px;">Bottom Action Button in App:</small>
                                    <div id="previewModalActionButton" class="mt-1 p-2 rounded-3 text-center fw-bold" style="background: #ff2d55; color: #fff; font-size: 12px; box-shadow: 0 4px 12px rgba(255,45,85,0.4);">
                                        Recharge 40,000 Gems (৳550)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-modern-footer">
                    <button type="button" class="btn btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-ch-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Save Package
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
/* App Package Cards matching Dark Theme Mobile App */
.app-pkg-card {
    background: #181d2f;
    border: 1.5px solid #283049;
    border-radius: 18px;
    padding: 20px;
    color: #ffffff;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
}

.app-pkg-card:hover {
    transform: translateY(-4px);
    border-color: #3b82f6;
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.35);
}

.app-pkg-card.popular-card {
    border-color: #ff2d55;
    background: linear-gradient(180deg, #221c35 0%, #17182b 100%);
    box-shadow: 0 0 20px rgba(255, 45, 85, 0.25), 0 8px 24px rgba(0, 0, 0, 0.3);
}

.pkg-badge {
    font-size: 11px;
    font-weight: 800;
    padding: 3px 10px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}

.badge-pink-glow {
    background: linear-gradient(135deg, #ff2d55 0%, #d91b42 100%);
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(255, 45, 85, 0.5);
}

.badge-subtle {
    background: #2b334d;
    color: #e2e8f0;
    border: 1px solid #3e4868;
}

.pkg-gem-icon {
    font-size: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pkg-coins-amount {
    font-size: 24px;
    font-weight: 900;
    letter-spacing: 0.5px;
    color: #ffffff;
    font-family: var(--font-heading, 'Outfit', sans-serif);
}

.pkg-bonus-text {
    font-size: 13px;
    font-weight: 700;
    color: #10b981;
    display: flex;
    align-items: center;
    gap: 4px;
}

.pkg-bonus-placeholder {
    font-size: 13px;
    font-weight: 500;
    color: #64748b;
}

.pkg-price-btn {
    padding: 10px 14px;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 800;
    text-align: center;
    transition: all 0.2s ease;
    letter-spacing: 0.5px;
}

.pkg-price-btn.price-pink {
    background: #ff2d55;
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(255, 45, 85, 0.4);
}

.pkg-price-btn.price-dark {
    background: #242c44;
    color: #ffffff;
    border: 1px solid #333f61;
}

.opacity-60 {
    opacity: 0.6;
}
</style>
@endpush

@push('scripts')
<script>
function selectQuickBadge(badgeText) {
    document.getElementById('pkgBadge').value = badgeText;
    updateModalPreview();
}

function updateModalPreview() {
    const coins = parseFloat(document.getElementById('pkgCoins').value) || 0;
    const bonus = parseFloat(document.getElementById('pkgBonusCoins').value) || 0;
    const price = parseFloat(document.getElementById('pkgPrice').value) || 0;
    const badge = document.getElementById('pkgBadge').value.trim();
    const isPopular = document.getElementById('pkgIsPopular').checked;

    const total = coins + bonus;

    // Elements
    const badgeEl = document.getElementById('previewModalBadge');
    const coinsEl = document.getElementById('previewModalCoins');
    const bonusEl = document.getElementById('previewModalBonus');
    const priceBtnEl = document.getElementById('previewModalPriceBtn');
    const actionBtnEl = document.getElementById('previewModalActionButton');

    // Update values
    if (badge) {
        badgeEl.innerText = badge;
        badgeEl.style.display = 'inline-flex';
    } else {
        badgeEl.style.display = 'none';
    }

    coinsEl.innerText = coins.toLocaleString();

    if (bonus > 0) {
        bonusEl.innerHTML = `<i class="fa-solid fa-plus" style="font-size: 10px;"></i> ${bonus.toLocaleString()} Bonus`;
        bonusEl.style.display = 'flex';
    } else {
        bonusEl.style.display = 'none';
    }

    const priceText = `৳${price.toLocaleString()}`;
    priceBtnEl.innerText = priceText;

    if (isPopular) {
        priceBtnEl.className = 'pkg-price-btn price-pink text-center';
        badgeEl.className = 'pkg-badge badge-pink-glow';
    } else {
        priceBtnEl.className = 'pkg-price-btn price-dark text-center';
        badgeEl.className = 'pkg-badge badge-subtle';
    }

    actionBtnEl.innerText = `Recharge ${total.toLocaleString()} Gems (${priceText})`;
}

function openCreatePackageModal() {
    const modalEl = document.getElementById('packageModal');
    const form = document.getElementById('pkgForm');
    document.getElementById('pkgModalTitle').innerText = 'Add Coin Package';
    form.action = "{{ route('admin.coin-packages.store') }}";
    document.getElementById('pkgMethodSpoof').innerHTML = '';
    document.getElementById('pkgCoins').value = '32000';
    document.getElementById('pkgBonusCoins').value = '8000';
    document.getElementById('pkgPrice').value = '550';
    document.getElementById('pkgBadge').value = '🔥 50% OFF';
    document.getElementById('pkgSortOrder').value = '1';
    document.getElementById('pkgIsPopular').checked = true;
    document.getElementById('pkgIsActive').checked = true;

    updateModalPreview();

    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
}

function openEditPackageModal(packageData) {
    const modalEl = document.getElementById('packageModal');
    const form = document.getElementById('pkgForm');
    document.getElementById('pkgModalTitle').innerText = 'Edit Package: ' + (packageData.total_coins || packageData.coins) + ' Gems';
    form.action = `/admin/coin-packages/${packageData.id}`;
    document.getElementById('pkgMethodSpoof').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    
    document.getElementById('pkgCoins').value = packageData.coins || '';
    document.getElementById('pkgBonusCoins').value = packageData.bonus_coins || 0;
    document.getElementById('pkgPrice').value = packageData.price || '';
    document.getElementById('pkgBadge').value = packageData.badge || '';
    document.getElementById('pkgSortOrder').value = packageData.sort_order || 0;
    document.getElementById('pkgIsPopular').checked = Boolean(packageData.is_popular);
    document.getElementById('pkgIsActive').checked = Boolean(packageData.is_active);

    updateModalPreview();

    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
}
</script>
@endpush
@endsection
