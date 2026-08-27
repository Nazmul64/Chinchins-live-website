@extends('layouts.admin')

@section('title', 'Withdrawal & Commission Settings')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <a href="{{ route('admin.withdrawals.index') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Withdrawals</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Settings</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-sliders text-primary"></i>
                <span>Withdrawal & Commission Configuration</span>
            </h1>
            <p class="page-subtitle">Configure minimum/maximum cash out limits, coin exchange rate, admin commission percentage, and supported payout methods.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.withdrawals.index') }}" class="btn-ch-primary">
                <i class="fa-solid fa-hand-holding-dollar"></i> View Withdrawal Requests
            </a>
        </div>
    </div>

    <form action="{{ route('admin.withdrawals.settings.update') }}" method="POST">
        @csrf
        <div class="row g-4">
            <!-- Left Column: Core Settings Form -->
            <div class="col-12 col-xl-8">
                <!-- 1. General Rules Card -->
                <div class="card mb-4" style="border-radius: 16px; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-dark mb-1">
                                <i class="fa-solid fa-toggle-on text-primary me-2"></i> Global Withdrawal Controls
                            </h5>
                            <p class="text-muted mb-0" style="font-size: 13px;">Control whether users can submit cash outs and configure commission rules.</p>
                        </div>
                        <!-- Active Switch -->
                        <div class="form-check form-switch form-switch-lg">
                            <input type="hidden" name="is_withdraw_enabled" value="0">
                            <input class="form-check-input" type="checkbox" name="is_withdraw_enabled" id="isWithdrawEnabled" value="1" {{ $config['is_withdraw_enabled'] ? 'checked' : '' }} style="cursor: pointer; transform: scale(1.3);">
                            <label class="form-check-label fw-bold ms-2 {{ $config['is_withdraw_enabled'] ? 'text-success' : 'text-danger' }}" for="isWithdrawEnabled">
                                {{ $config['is_withdraw_enabled'] ? 'Withdrawals Active' : 'Withdrawals Paused' }}
                            </label>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Min Coins -->
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                                    <i class="fa-solid fa-circle-down text-warning me-1"></i> Minimum Withdrawal Limit (Coins)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fa-solid fa-coins text-warning"></i></span>
                                    <input type="number" name="min_withdraw_coins" id="inputMinCoins" class="form-control" value="{{ $config['min_withdraw_coins'] }}" min="1" required style="border-radius: 0 8px 8px 0; font-weight: 600;">
                                </div>
                                <small class="text-muted" id="minBdtHelper">Minimum coins a user must withdraw in one request.</small>
                            </div>

                            <!-- Max Coins -->
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                                    <i class="fa-solid fa-circle-up text-danger me-1"></i> Maximum Withdrawal Limit (Coins)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fa-solid fa-coins text-danger"></i></span>
                                    <input type="number" name="max_withdraw_coins" id="inputMaxCoins" class="form-control" value="{{ $config['max_withdraw_coins'] }}" min="1" required style="border-radius: 0 8px 8px 0; font-weight: 600;">
                                </div>
                                <small class="text-muted" id="maxBdtHelper">Maximum coins allowed per single withdrawal request.</small>
                            </div>

                            <!-- Commission Percent -->
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                                    <i class="fa-solid fa-percent text-purple me-1" style="color: #8b5cf6;"></i> Platform Commission / Fee (%)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light" style="font-weight: bold; color: #8b5cf6;">%</span>
                                    <input type="number" step="0.1" name="commission_percent" id="inputCommission" class="form-control" value="{{ $config['commission_percent'] }}" min="0" max="100" required style="border-radius: 0 8px 8px 0; font-weight: 600;">
                                </div>
                                <small class="text-muted">Deducted from gross BDT before sending payout to user.</small>
                            </div>

                            <!-- Conversion Rate: Coins to BDT -->
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                                    <i class="fa-solid fa-arrow-right-arrow-left text-success me-1"></i> Coin Exchange Rate (Coins = BDT)
                                </label>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="input-group" style="flex: 1;">
                                        <input type="number" name="rate_coins" id="inputRateCoins" class="form-control" value="{{ $config['rate_coins'] }}" min="1" placeholder="Coins" required style="font-weight: 600;">
                                        <span class="input-group-text bg-light">Coins =</span>
                                    </div>
                                    <div class="input-group" style="flex: 1;">
                                        <span class="input-group-text bg-light">৳</span>
                                        <input type="number" step="0.01" name="rate_bdt" id="inputRateBdt" class="form-control" value="{{ $config['rate_bdt'] }}" min="0.01" placeholder="BDT" required style="font-weight: 600;">
                                        <span class="input-group-text bg-light">BDT</span>
                                    </div>
                                </div>
                                <small class="text-muted" id="rateHelper">E.g., 100 Coins = ৳10.00 BDT (1 BDT = {{ $config['rate_per_bdt'] }} Coins).</small>
                            </div>

                            <!-- Notice / User Instructions -->
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                                    <i class="fa-solid fa-bullhorn text-info me-1"></i> Withdrawal Notice / User Guidelines
                                </label>
                                <textarea name="notice" class="form-control" rows="3" placeholder="Enter instructions displayed to users on the app withdrawal screen..." style="border-radius: 8px;">{{ $config['notice'] }}</textarea>
                                <small class="text-muted">This text will be returned by the API to the mobile app for users to read.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Payment Gateways Table -->
                <div class="card mb-4" style="border-radius: 16px; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <h5 class="fw-bold text-dark mb-1">
                            <i class="fa-solid fa-credit-card text-success me-2"></i> Payout Gateways (Available in Dropdown)
                        </h5>
                        <p class="text-muted mb-0" style="font-size: 13px;">Select which payment methods users can choose when withdrawing money.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table align-middle ch-datatable mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">Enable</th>
                                        <th>Method Name</th>
                                        <th>Code / Type</th>
                                        <th>Min Limit (BDT)</th>
                                        <th>Max Limit (BDT)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($paymentMethods as $pm)
                                        <tr>
                                            <td>
                                                <input type="checkbox" 
                                                       name="methods[{{ $pm->id }}][supports_withdraw]" 
                                                       value="1" 
                                                       class="form-check-input" 
                                                       style="transform: scale(1.2); cursor: pointer;"
                                                       {{ $pm->supports_withdraw ? 'checked' : '' }}>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if($pm->icon_url)
                                                        <img src="{{ $pm->icon_url }}" alt="icon" style="width: 28px; height: 28px; object-fit: contain; border-radius: 4px;">
                                                    @else
                                                        <i class="fa-solid fa-building-columns text-secondary" style="font-size: 20px;"></i>
                                                    @endif
                                                    <div>
                                                        <strong class="text-dark">{{ $pm->name }}</strong>
                                                        <div class="text-muted" style="font-size: 11px;">A/C: {{ $pm->account_number }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">{{ strtoupper($pm->code) }}</span>
                                                <span class="badge bg-secondary-subtle text-secondary">{{ $pm->account_type }}</span>
                                            </td>
                                            <td>
                                                <input type="number" step="1" name="methods[{{ $pm->id }}][min_withdraw]" value="{{ $pm->min_withdraw ?: 50 }}" class="form-control form-control-sm" style="width: 100px; border-radius: 6px;">
                                            </td>
                                            <td>
                                                <input type="number" step="1" name="methods[{{ $pm->id }}][max_withdraw]" value="{{ $pm->max_withdraw ?: 50000 }}" class="form-control form-control-sm" style="width: 120px; border-radius: 6px;">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">No payment methods found. Please configure payment gateways first.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Save Changes Button -->
                <div class="d-flex justify-content-end mb-4">
                    <button type="submit" class="btn btn-primary fw-bold" style="border-radius: 12px; padding: 12px 28px; font-size: 15px; box-shadow: 0 4px 14px rgba(59, 130, 246, 0.4);">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Save Withdrawal Settings
                    </button>
                </div>
            </div>

            <!-- Right Column: Interactive Live Simulation Widget -->
            <div class="col-12 col-xl-4">
                <!-- Live Rate Calculator Card -->
                <div class="card sticky-top" style="top: 90px; border-radius: 16px; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 10px 30px rgba(0,0,0,0.05); background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                        <div class="d-flex align-items-center gap-2">
                            <div class="stat-icon-box stat-icon-blue" style="width: 38px; height: 38px; font-size: 16px;">
                                <i class="fa-solid fa-calculator"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Live Payout Preview</h5>
                                <small class="text-muted">Test calculations instantly</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark" style="font-size: 13px;">Test Coins Amount</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fa-solid fa-coins text-warning"></i></span>
                                <input type="number" id="testCoinsInput" class="form-control" value="5000" min="1" style="font-weight: 700; font-size: 16px; border-radius: 0 8px 8px 0;">
                            </div>
                        </div>

                        <!-- Calculation Breakdown Table -->
                        <div class="p-3 mb-3" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0;">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted" style="font-size: 13px;">Coins to Convert:</span>
                                <strong id="previewCoins" class="text-dark">5,000 Coins</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted" style="font-size: 13px;">Gross Value (BDT):</span>
                                <strong id="previewGross" class="text-primary">৳ 500.00</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted" style="font-size: 13px;">Commission (<span id="previewCommPercent">5.0%</span>):</span>
                                <strong id="previewCommAmount" class="text-danger">- ৳ 25.00</strong>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark" style="font-size: 14px;">User Receives (Net):</span>
                                <strong id="previewNet" class="text-success fs-4">৳ 475.00</strong>
                            </div>
                        </div>

                        <!-- Status Alert Box -->
                        <div id="previewStatusBox" class="p-3" style="background: rgba(16, 185, 129, 0.1); border-radius: 10px; border: 1px solid rgba(16, 185, 129, 0.2);">
                            <div class="d-flex align-items-center gap-2 text-success fw-bold" style="font-size: 13px;">
                                <i class="fa-solid fa-circle-check"></i>
                                <span id="previewStatusText">Amount is within allowed limits</span>
                            </div>
                        </div>

                        <!-- Summary Tips -->
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="fw-bold text-dark mb-2" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">API Flow Summary</h6>
                            <ul class="text-muted ps-3 mb-0" style="font-size: 12px; line-height: 1.6;">
                                <li>Mobile app queries <code>GET /api/withdraw/info</code> to fetch min/max limits & methods.</li>
                                <li>User submits cash out request with phone & method.</li>
                                <li>Status is set to <strong>Pending</strong>.</li>
                                <li>Admin reviews request in Onedash and clicks <strong>Approve</strong>.</li>
                                <li>Coins are immediately deducted from wallet and payout sent.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputMinCoins = document.getElementById('inputMinCoins');
    const inputMaxCoins = document.getElementById('inputMaxCoins');
    const inputCommission = document.getElementById('inputCommission');
    const inputRateCoins = document.getElementById('inputRateCoins');
    const inputRateBdt = document.getElementById('inputRateBdt');
    const testCoinsInput = document.getElementById('testCoinsInput');

    const previewCoins = document.getElementById('previewCoins');
    const previewGross = document.getElementById('previewGross');
    const previewCommPercent = document.getElementById('previewCommPercent');
    const previewCommAmount = document.getElementById('previewCommAmount');
    const previewNet = document.getElementById('previewNet');
    const previewStatusBox = document.getElementById('previewStatusBox');
    const previewStatusText = document.getElementById('previewStatusText');
    const rateHelper = document.getElementById('rateHelper');

    function updateSimulation() {
        const minCoins = parseFloat(inputMinCoins?.value) || 1000;
        const maxCoins = parseFloat(inputMaxCoins?.value) || 100000;
        const commPercent = parseFloat(inputCommission?.value) || 0;
        const rCoins = parseFloat(inputRateCoins?.value) || 100;
        const rBdt = parseFloat(inputRateBdt?.value) || 10;
        const testCoins = parseFloat(testCoinsInput?.value) || 0;

        const ratePerBdt = rBdt > 0 ? (rCoins / rBdt) : 10;

        if (rateHelper) {
            rateHelper.innerText = `E.g., ${rCoins} Coins = ৳${rBdt.toFixed(2)} BDT (1 BDT = ${ratePerBdt.toFixed(2)} Coins)`;
        }

        const grossBdt = ratePerBdt > 0 ? (testCoins / ratePerBdt) : 0;
        const commBdt = grossBdt * (commPercent / 100);
        const netBdt = Math.max(0, grossBdt - commBdt);

        if (previewCoins) previewCoins.innerText = `${Number(testCoins).toLocaleString()} Coins`;
        if (previewGross) previewGross.innerText = `৳ ${grossBdt.toFixed(2)}`;
        if (previewCommPercent) previewCommPercent.innerText = `${commPercent.toFixed(1)}%`;
        if (previewCommAmount) previewCommAmount.innerText = `- ৳ ${commBdt.toFixed(2)}`;
        if (previewNet) previewNet.innerText = `৳ ${netBdt.toFixed(2)}`;

        // Validation check for test amount
        if (testCoins < minCoins) {
            previewStatusBox.style.background = 'rgba(239, 68, 68, 0.1)';
            previewStatusBox.style.borderColor = 'rgba(239, 68, 68, 0.2)';
            previewStatusText.className = 'text-danger';
            previewStatusText.innerHTML = `<i class="fa-solid fa-circle-exclamation me-1"></i> Below minimum limit (${Number(minCoins).toLocaleString()} coins)`;
        } else if (testCoins > maxCoins) {
            previewStatusBox.style.background = 'rgba(239, 68, 68, 0.1)';
            previewStatusBox.style.borderColor = 'rgba(239, 68, 68, 0.2)';
            previewStatusText.className = 'text-danger';
            previewStatusText.innerHTML = `<i class="fa-solid fa-circle-exclamation me-1"></i> Exceeds maximum limit (${Number(maxCoins).toLocaleString()} coins)`;
        } else {
            previewStatusBox.style.background = 'rgba(16, 185, 129, 0.1)';
            previewStatusBox.style.borderColor = 'rgba(16, 185, 129, 0.2)';
            previewStatusText.className = 'text-success';
            previewStatusText.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> Amount is valid (৳${netBdt.toFixed(2)} Net Payout)`;
        }
    }

    [inputMinCoins, inputMaxCoins, inputCommission, inputRateCoins, inputRateBdt, testCoinsInput].forEach(el => {
        el?.addEventListener('input', updateSimulation);
    });

    updateSimulation();
});
</script>
@endpush
