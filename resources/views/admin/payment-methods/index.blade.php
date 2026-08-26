@extends('layouts.admin')

@section('title', 'Payment Gateways & Deposit Methods')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Payment Gateways</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-credit-card text-success"></i>
                <span>Payment Gateways & Methods</span>
            </h1>
            <p class="page-subtitle">Configure manual deposit payment options (bKash, Nagad, Rocket, Bank) visible inside the mobile app.</p>
        </div>
        <button type="button" class="btn-ch-primary" onclick="openCreatePaymentMethodModal()">
            <i class="fa-solid fa-plus-circle"></i> Add Payment Gateway
        </button>
    </div>

    <!-- Payment Gateways Grid -->
    <div class="row g-4 mb-4">
        @forelse($methods as $method)
            @php
                $brandCode = strtolower(trim($method->code));
                if (str_contains($brandCode, 'bkash')) $brandClass = 'bkash';
                elseif (str_contains($brandCode, 'nagad')) $brandClass = 'nagad';
                elseif (str_contains($brandCode, 'rocket')) $brandClass = 'rocket';
                elseif (str_contains($brandCode, 'upay')) $brandClass = 'upay';
                else $brandClass = 'general';
            @endphp
            <div class="col-12 col-md-6 col-xl-3">
                <div class="pm-card-wrapper {{ $brandClass }}">
                    <div>
                        <!-- Card Top: Brand Icon + Title + Status Toggle -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="pm-logo-frame">
                                    @if($method->icon_url)
                                        <img src="{{ $method->icon_url }}" alt="{{ $method->name }}" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($method->name) }}&background=3b82f6&color=fff';">
                                    @else
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($method->name) }}&background=3b82f6&color=fff" alt="{{ $method->name }}">
                                    @endif
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1" style="font-size: 16px; color: var(--text-primary);">
                                        {{ $method->name }}
                                    </h5>
                                    <span class="gateway-badge {{ $brandClass }}" style="font-size: 10px; padding: 2px 8px;">
                                        {{ $method->account_type }}
                                    </span>
                                </div>
                            </div>
                            <!-- Status pill button -->
                            <form action="{{ route('admin.payment-methods.toggle-status', $method->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="status-pill-modern {{ $method->is_active ? 'active' : 'inactive' }}" style="border: none; cursor: pointer;" title="Click to toggle status">
                                    <span class="status-pulsing-dot"></span>
                                    {{ $method->is_active ? 'Active' : 'Disabled' }}
                                </button>
                            </form>
                        </div>

                        <!-- Account Number Box with Copy -->
                        <div class="pm-number-box">
                            <div>
                                <small class="text-muted d-block fw-bold" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px;">Account / Phone Number</small>
                                <span class="pm-number-text">{{ $method->account_number }}</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-light border rounded-3 p-1 px-2" onclick="copyToClipboard('{{ $method->account_number }}', 'Account number copied!')" title="Copy Number">
                                <i class="fa-regular fa-copy text-muted"></i>
                            </button>
                        </div>

                        <!-- Exchange Rate & Limits -->
                        <div class="d-flex flex-column gap-2 mb-3" style="font-size: 13px;">
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom" style="border-color: var(--border-color) !important;">
                                <span class="text-muted">Exchange Rate:</span>
                                <div class="coin-badge-3d" style="padding: 3px 10px;">
                                    <i class="fa-solid fa-coins coin-icon-glow" style="font-size: 11px;"></i>
                                    <span class="coin-amount-text" style="font-size: 12px;">
                                        {{ number_format($method->rate_coins ?? ($method->rate_per_bdt * 10)) }} Coins
                                        @if(($method->bonus_coins ?? 0) > 0)
                                            <span class="text-success fw-bold">(+{{ number_format($method->bonus_coins) }} Bonus)</span>
                                        @endif
                                        = ৳{{ number_format($method->rate_bdt ?? 10) }}
                                    </span>
                                </div>
                            </div>
                            @if($method->offer_tag)
                                <div class="d-flex justify-content-between align-items-center py-1 border-bottom" style="border-color: var(--border-color) !important;">
                                    <span class="text-muted">Offer / Discount:</span>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold" style="font-size: 11px; padding: 3px 8px;">
                                        {{ $method->offer_tag }}
                                    </span>
                                </div>
                            @endif
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom" style="border-color: var(--border-color) !important;">
                                <span class="text-muted">Deposit Limits:</span>
                                <strong>৳{{ number_format($method->min_deposit) }} - ৳{{ number_format($method->max_deposit) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-1">
                                <span class="text-muted">Total Processed:</span>
                                <span class="badge bg-light text-dark border">{{ $method->deposit_requests_count }} deposits</span>
                            </div>
                        </div>

                        <!-- Instructions Preview -->
                        @if($method->instructions)
                            <div class="p-2 rounded-3 mb-3" style="background: var(--bg-main); border: 1px dashed var(--border-color); font-size: 12px; color: var(--text-secondary); max-height: 75px; overflow-y: auto;">
                                <strong class="d-block text-dark mb-1"><i class="fa-solid fa-circle-info text-primary me-1"></i> User Instructions:</strong>
                                {!! nl2br(e($method->instructions)) !!}
                            </div>
                        @endif
                    </div>

                    <!-- Card Actions -->
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="border-color: var(--card-border-light) !important;">
                        <small class="text-muted"><i class="fa-solid fa-shield-check text-success me-1"></i> Enabled for Users</small>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn-ch-icon" title="Edit Gateway" onclick='openEditPaymentMethodModal(@json($method))'>
                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                            </button>
                            <form action="{{ route('admin.payment-methods.destroy', $method->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this payment method?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-ch-icon" title="Delete Gateway" style="color: var(--danger);">
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
                    <i class="fa-solid fa-credit-card fa-4x text-muted mb-3" style="opacity: 0.3;"></i>
                    <h4 class="fw-bold">No Payment Gateways Configured</h4>
                    <p class="text-muted mb-4">Add your bKash, Nagad, Rocket or Bank numbers so mobile app users can deposit money to get coins.</p>
                    <button type="button" class="btn-ch-primary mx-auto" onclick="openCreatePaymentMethodModal()">
                        <i class="fa-solid fa-plus-circle"></i> Add First Gateway
                    </button>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Modal for Create / Edit Payment Method -->
<div class="modal fade" id="paymentMethodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-modern-dialog" style="max-width: 650px;">
        <div class="modal-content modal-modern-content">
            <div class="modal-modern-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-box stat-icon-green" style="width: 44px; height: 44px; font-size: 18px;">
                        <i class="fa-solid fa-credit-card"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="pmModalTitle">Add Payment Gateway</h5>
                        <small class="text-muted">Configure deposit account details for the mobile app</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="pmForm" method="POST" action="{{ route('admin.payment-methods.store') }}" enctype="multipart/form-data">
                @csrf
                <div id="pmMethodSpoof"></div>
                <div class="modal-modern-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Method Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="pmName" class="form-control rounded-3" placeholder="e.g. bKash Personal" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Account Type <span class="text-danger">*</span></label>
                            <select name="account_type" id="pmAccountType" class="form-select rounded-3" required>
                                <option value="Personal">Personal Account</option>
                                <option value="Agent">Agent Account</option>
                                <option value="Merchant">Merchant Account</option>
                                <option value="Bank Account">Bank Transfer Account</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold" style="font-size: 13px;">Account / Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="account_number" id="pmAccountNumber" class="form-control font-monospace rounded-3" placeholder="e.g. 01700000000" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Min Deposit (BDT) <span class="text-danger">*</span></label>
                            <input type="number" name="min_deposit" id="pmMinDeposit" class="form-control rounded-3" value="50" min="1" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Max Deposit (BDT) <span class="text-danger">*</span></label>
                            <input type="number" name="max_deposit" id="pmMaxDeposit" class="form-control rounded-3" value="25000" min="1" required>
                        </div>

                        <!-- Quad Setup: Coin Amount, Bonus Coins, BDT Amount & Offer Tag -->
                        <div class="col-12">
                            <label class="form-label fw-bold d-flex justify-content-between align-items-center" style="font-size: 13px;">
                                <span><i class="fa-solid fa-scale-balanced text-primary me-1"></i> Coin Exchange Rate & Bonus Offer Setup <span class="text-danger">*</span></span>
                                <span class="badge bg-light text-muted border fw-normal" style="font-size: 11px;">কয়েন, বোনাস ও অফার ট্যাগ</span>
                            </label>
                            <div class="p-3 rounded-3" style="background: var(--bg-main, #f8fafc); border: 1.5px solid var(--border-color, #e2e8f0);">
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 col-sm-4">
                                        <label class="form-label text-muted fw-bold mb-1" style="font-size: 11px; text-transform: uppercase;">Coin Quantity (কয়েন)</label>
                                        <div class="input-group">
                                            <input type="number" name="rate_coins" id="pmRateCoins" class="form-control fw-bold" placeholder="500" value="500" min="1" required oninput="calculateExchangeRateLive()">
                                            <span class="input-group-text bg-warning-subtle text-warning fw-bold"><i class="fa-solid fa-coins me-1"></i> Coins</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <label class="form-label text-muted fw-bold mb-1" style="font-size: 11px; text-transform: uppercase;">Bonus Coins (বোনাস কয়েন)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-success-subtle text-success fw-bold"><i class="fa-solid fa-gift me-1"></i> +</span>
                                            <input type="number" name="bonus_coins" id="pmBonusCoins" class="form-control fw-bold text-success" placeholder="0" value="0" min="0" oninput="calculateExchangeRateLive()">
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <label class="form-label text-muted fw-bold mb-1" style="font-size: 11px; text-transform: uppercase;">BDT Amount (টাকা)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light fw-bold text-success">৳ BDT</span>
                                            <input type="number" step="any" name="rate_bdt" id="pmRateBdt" class="form-control fw-bold" placeholder="50" value="50" min="0.1" required oninput="calculateExchangeRateLive()">
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="rate_per_bdt" id="pmRatePerBdt" value="10">

                                <div class="mt-3">
                                    <label class="form-label text-muted fw-bold mb-1" style="font-size: 11px; text-transform: uppercase;">Offer / Discount Badge (অফার ট্যাগ - যেমন: 🔥 50% OFF, 30% OFF)</label>
                                    <input type="text" name="offer_tag" id="pmOfferTag" class="form-control rounded-3" placeholder="e.g. 🔥 50% OFF, 30% OFF, Best Value" oninput="calculateExchangeRateLive()">
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        <span class="quick-chip-btn" onclick="selectPmOfferTag('🔥 50% OFF')">🔥 50% OFF</span>
                                        <span class="quick-chip-btn" onclick="selectPmOfferTag('30% OFF')">30% OFF</span>
                                        <span class="quick-chip-btn" onclick="selectPmOfferTag('Best Value')">Best Value</span>
                                        <span class="quick-chip-btn" onclick="selectPmOfferTag('VIP Bonus')">VIP Bonus</span>
                                        <span class="quick-chip-btn text-danger" onclick="selectPmOfferTag('')">Clear</span>
                                    </div>
                                </div>
                                
                                <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-2" style="font-size: 12px;">
                                    <div class="d-flex align-items-center gap-1 text-muted">
                                        <i class="fa-solid fa-circle-check text-success"></i>
                                        <span>Effective Multiplier:</span>
                                        <strong id="pmRateMultiplierDisplay" class="text-primary">1 BDT = 10 Coins</strong>
                                    </div>
                                    <div class="text-success fw-bold" id="pmRateSummaryPreview">
                                        500 Coins = ৳50 BDT
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted mt-1 d-block" style="font-size: 11px;">
                                ডিপোজিট অ্যাপ্রুভ হলে ব্যবহারকারীর মেইন অ্যাকাউন্টে মূল কয়েন এবং বোনাস কয়েন একসাথে যোগ হয়ে যাবে।
                            </small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold" style="font-size: 13px;">User Instructions (Shown on Mobile Deposit Screen)</label>
                            <textarea name="instructions" id="pmInstructions" class="form-control rounded-3" rows="3" placeholder="1. Go to your bKash/Nagad app&#10;2. Select 'Send Money'&#10;3. Enter the number and copy the TrxID..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold" style="font-size: 13px;">Gateway Icon / Logo (Picture)</label>
                            <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: var(--bg-main); border: 1px dashed var(--border-color);">
                                <div class="position-relative" style="width: 64px; height: 64px; border-radius: 12px; background: #fff; border: 2px solid var(--border-color); display: flex; align-items: center; justify-content: center; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); flex-shrink: 0;">
                                    <img id="pmModalIconPreview" src="" alt="Icon Preview" style="width: 100%; height: 100%; object-fit: contain; display: none;">
                                    <i id="pmModalIconPlaceholder" class="fa-solid fa-image text-muted fa-2x" style="opacity: 0.4;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="icon" id="pmIconInput" class="form-control rounded-3" accept="image/*" onchange="previewGatewayIcon(event)">
                                    <small class="text-muted mt-1 d-block" style="font-size: 11px;">Select PNG, JPG, or SVG to preview and save in <code>uploads/payment_gateways/</code></small>
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-3" id="pmClearIconBtn" onclick="clearGatewayIconPreview()" style="display: none;" title="Remove chosen image">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch p-0 d-flex align-items-center gap-3">
                                <input class="form-check-input ms-0" type="checkbox" name="is_active" id="pmIsActive" value="1" checked style="width: 44px; height: 22px; cursor: pointer;">
                                <label class="form-check-label fw-bold" for="pmIsActive" style="cursor: pointer; font-size: 14px;">
                                    Active & Enabled in Mobile App
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-modern-footer">
                    <button type="button" class="btn btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-ch-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Save Gateway
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="ch-toast-container" id="toastContainer"></div>

@push('scripts')
<script>
function handlePmImageError(img, name) {
    img.onerror = null;
    const lower = (name || '').toLowerCase();
    if (lower.includes('bkash')) {
        img.src = 'https://freelogopng.com/images/all_img/1656234745bkash-app-logo.png';
    } else if (lower.includes('nagad')) {
        img.src = 'https://freelogopng.com/images/all_img/1679248787Nagad-Logo.png';
    } else if (lower.includes('rocket')) {
        img.src = 'https://seeklogo.com/images/D/dutch-bangla-rocket-logo-B4D1CC458D-seeklogo.com.png';
    } else if (lower.includes('upay')) {
        img.src = 'https://play-lh.googleusercontent.com/O61_aF_n_wP508rC8v2y26Y92aM3u9z-m-B-f8x-y44';
    } else {
        img.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=3b82f6&color=fff`;
    }
}

function selectPmOfferTag(tag) {
    document.getElementById('pmOfferTag').value = tag;
    calculateExchangeRateLive();
}

function calculateExchangeRateLive() {
    const coinsInput = document.getElementById('pmRateCoins');
    const bonusInput = document.getElementById('pmBonusCoins');
    const bdtInput = document.getElementById('pmRateBdt');
    const ratePerBdtHidden = document.getElementById('pmRatePerBdt');
    const multiplierDisplay = document.getElementById('pmRateMultiplierDisplay');
    const summaryPreview = document.getElementById('pmRateSummaryPreview');

    const coins = parseFloat(coinsInput ? coinsInput.value : 0) || 0;
    const bonus = parseFloat(bonusInput ? bonusInput.value : 0) || 0;
    const bdt = parseFloat(bdtInput ? bdtInput.value : 0) || 0;
    const totalCoins = coins + bonus;

    const ratePerBdt = bdt > 0 ? (coins / bdt) : 0;
    const formattedRate = (Number.isInteger(ratePerBdt) ? ratePerBdt : ratePerBdt.toFixed(2));

    if (ratePerBdtHidden) ratePerBdtHidden.value = formattedRate;
    if (multiplierDisplay) multiplierDisplay.innerText = `1 BDT = ${formattedRate} Coins`;
    
    if (summaryPreview) {
        if (bonus > 0) {
            summaryPreview.innerHTML = `${coins.toLocaleString()} + <span class="text-success fw-bold">${bonus.toLocaleString()} Bonus</span> = <strong>${totalCoins.toLocaleString()} Coins</strong> for ৳${bdt.toLocaleString()} BDT`;
        } else {
            summaryPreview.innerText = `${coins.toLocaleString()} Coins = ৳${bdt.toLocaleString()} BDT`;
        }
    }
}

function previewGatewayIcon(event) {
    const input = event.target;
    const preview = document.getElementById('pmModalIconPreview');
    const placeholder = document.getElementById('pmModalIconPlaceholder');
    const clearBtn = document.getElementById('pmClearIconBtn');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
            clearBtn.style.display = 'inline-block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function clearGatewayIconPreview() {
    const input = document.getElementById('pmIconInput');
    const preview = document.getElementById('pmModalIconPreview');
    const placeholder = document.getElementById('pmModalIconPlaceholder');
    const clearBtn = document.getElementById('pmClearIconBtn');

    input.value = '';
    preview.src = '';
    preview.style.display = 'none';
    placeholder.style.display = 'block';
    clearBtn.style.display = 'none';
}

function openCreatePaymentMethodModal() {
    const modalEl = document.getElementById('paymentMethodModal');
    const form = document.getElementById('pmForm');
    document.getElementById('pmModalTitle').innerText = 'Add Payment Gateway';
    form.action = "{{ route('admin.payment-methods.store') }}";
    document.getElementById('pmMethodSpoof').innerHTML = '';
    document.getElementById('pmName').value = '';
    document.getElementById('pmAccountNumber').value = '';
    document.getElementById('pmMinDeposit').value = '50';
    document.getElementById('pmMaxDeposit').value = '25000';
    document.getElementById('pmRateCoins').value = '500';
    document.getElementById('pmBonusCoins').value = '0';
    document.getElementById('pmRateBdt').value = '50';
    document.getElementById('pmOfferTag').value = '';
    calculateExchangeRateLive();
    document.getElementById('pmInstructions').value = '';
    document.getElementById('pmIsActive').checked = true;
    clearGatewayIconPreview();

    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
}

function openEditPaymentMethodModal(method) {
    const modalEl = document.getElementById('paymentMethodModal');
    const form = document.getElementById('pmForm');
    document.getElementById('pmModalTitle').innerText = 'Edit Gateway: ' + method.name;
    form.action = `/admin/payment-methods/${method.id}`;
    document.getElementById('pmMethodSpoof').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('pmName').value = method.name || '';
    document.getElementById('pmAccountType').value = method.account_type || 'Personal';
    document.getElementById('pmAccountNumber').value = method.account_number || '';
    document.getElementById('pmMinDeposit').value = method.min_deposit || 50;
    document.getElementById('pmMaxDeposit').value = method.max_deposit || 25000;
    
    // Set Coins, Bonus, BDT & Offer Tag
    const rateCoins = method.rate_coins || (method.rate_per_bdt ? (method.rate_per_bdt * 10) : 500);
    const rateBdt = method.rate_bdt || 50;
    document.getElementById('pmRateCoins').value = rateCoins;
    document.getElementById('pmBonusCoins').value = method.bonus_coins || 0;
    document.getElementById('pmRateBdt').value = rateBdt;
    document.getElementById('pmOfferTag').value = method.offer_tag || '';
    calculateExchangeRateLive();

    document.getElementById('pmInstructions').value = method.instructions || '';
    document.getElementById('pmIsActive').checked = Boolean(method.is_active);

    const preview = document.getElementById('pmModalIconPreview');
    const placeholder = document.getElementById('pmModalIconPlaceholder');
    const clearBtn = document.getElementById('pmClearIconBtn');
    document.getElementById('pmIconInput').value = '';

    if (method.icon_url) {
        preview.src = method.icon_url;
        preview.style.display = 'block';
        placeholder.style.display = 'none';
        clearBtn.style.display = 'none';
        preview.onerror = function() {
            this.style.display = 'none';
            placeholder.style.display = 'block';
        };
    } else {
        clearGatewayIconPreview();
    }

    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
}

function copyToClipboard(text, msg) {
    navigator.clipboard.writeText(text).then(() => {
        window.showToast(msg || 'Copied to clipboard!', 'success');
    });
}
</script>
@endpush
@endsection
