@extends('layouts.reseller')

@section('title', 'Reseller Dashboard')

@section('content')
<div class="row g-4">
    <!-- Left Column: Balance Banner & Direct Transfer Card -->
    <div class="col-12 col-xl-7">
        <!-- Balance & Action Hero Card -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 position-relative overflow-hidden text-white" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #4338ca 100%);">
            <div class="card-body p-4 p-md-5">
                <div class="row align-items-center">
                    <div class="col-12 col-md-7 mb-3 mb-md-0">
                        <span class="badge rounded-pill px-3 py-1 mb-2" style="background: rgba(245,158,11,0.25); color: #fbbf24; border: 1px solid rgba(245,158,11,0.4);">
                            <i class="fa-solid fa-gem me-1"></i> Authorized Coin Reseller
                        </span>
                        <h2 class="display-6 fw-bold mb-1" style="color: #fbbf24;">
                            {{ number_format($reseller->coins_balance) }} <small class="fs-5 text-white">Gems</small>
                        </h2>
                        <p class="text-white-50 mb-0" style="font-size: 13px;">Available coin stock for user recharges & direct transfers</p>
                    </div>
                    <div class="col-12 col-md-5 text-md-end d-flex flex-md-column gap-2 justify-content-start">
                        <button type="button" class="btn btn-warning fw-bold rounded-3 px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#refillModal">
                            <i class="fa-solid fa-circle-plus me-1"></i> Refill Stock
                        </button>
                        <button type="button" class="btn btn-outline-light rounded-3 px-3 py-2" data-bs-toggle="modal" data-bs-target="#withdrawModal">
                            <i class="fa-solid fa-hand-holding-dollar me-1"></i> Request Cash-Out
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Direct User Coin Transfer Card (Fast, Live ID validation) -->
        <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: #fff; border: 1px solid #e2e8f0 !important;">
            <div class="card-header bg-transparent border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 44px; height: 44px; background: rgba(59,130,246,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #2563eb; font-size: 20px;">
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Instant Coin Recharge to User</h5>
                        <small class="text-muted">Enter User ID / Account ID to validate and transfer gems immediately</small>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <form id="transferForm" method="POST" action="{{ route('reseller.transfer') }}">
                    @csrf
                    <!-- Step 1: User ID Validation -->
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Target User ID / 8-digit Account ID <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-id-badge text-muted"></i></span>
                            <input type="text" name="target_account_id" id="targetAccountId" class="form-control font-monospace border-start-0 ps-0" placeholder="e.g. 266813634" required>
                            <button type="button" class="btn btn-primary px-3 fw-bold" id="validateUserBtn" onclick="lookupUser()">
                                <i class="fa-solid fa-magnifying-glass me-1"></i> Verify User
                            </button>
                        </div>
                    </div>

                    <!-- User Verified Card Preview -->
                    <div id="userPreviewCard" class="p-3 rounded-3 mb-3" style="display: none; background: rgba(16,185,129,0.06); border: 1px solid rgba(16,185,129,0.2);">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <img id="previewAvatar" src="" class="rounded-circle border" style="width: 46px; height: 46px; object-fit: cover;">
                                <div>
                                    <strong id="previewName" class="d-block text-dark fs-6"></strong>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-light text-primary border" id="previewAccountId"></span>
                                        <span class="badge bg-light text-muted border" id="previewLevel"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block" style="font-size: 11px;">Current Coins</small>
                                <strong class="text-warning fs-6" id="previewCoins"></strong>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Coin Amount to Transfer <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-coins text-warning"></i></span>
                                <input type="number" name="coins" id="transferCoins" class="form-control font-monospace fs-5 fw-bold" placeholder="7560" min="10" max="{{ $reseller->coins_balance }}" required>
                            </div>
                            <small class="text-muted" style="font-size: 11px;">Max available: {{ number_format($reseller->coins_balance) }} gems</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Amount Paid by User (BDT)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">৳</span>
                                <input type="number" step="0.01" name="amount_bdt" class="form-control" placeholder="150.00">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Payment Method Received</label>
                            <select name="payment_method" class="form-select">
                                <option value="bKash">bKash</option>
                                <option value="Nagad">Nagad</option>
                                <option value="Rocket">Rocket</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cash / Hand">Cash / Hand</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Payment TrxID / Reference</label>
                            <input type="text" name="transaction_id" class="form-control font-monospace" placeholder="e.g. 9J8A7K21X">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold" style="font-size: 13px;">Note / Memo (Optional)</label>
                            <input type="text" name="notes" class="form-control" placeholder="e.g. Recharged via chat discount offer">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold rounded-3" id="submitTransferBtn">
                            <i class="fa-solid fa-bolt me-1"></i> Transfer & Recharge Gems
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Quick Stats, Live Chat link & Recent Transfers -->
    <div class="col-12 col-xl-5">
        <!-- Live Chat Card Promo -->
        <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff;">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="fw-bold mb-1">Customer Live Chat</h5>
                    <p class="mb-0 text-white-50" style="font-size: 13px;">View customer inquiries, payment screenshots, and voice notes.</p>
                </div>
                <a href="{{ route('reseller.chat') }}" class="btn btn-light rounded-pill px-3 py-2 fw-bold shadow-sm" style="color: #b45309;">
                    <i class="fa-solid fa-comments me-1"></i> Open Chat
                </a>
            </div>
        </div>

        <!-- Mini Stats Grid -->
        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="card border-0 rounded-4 shadow-sm p-3" style="background: #fff; border: 1px solid #e2e8f0 !important;">
                    <small class="text-muted d-block">Total Sold</small>
                    <h4 class="fw-bold text-success mb-0">{{ number_format($stats['total_sold']) }}</h4>
                    <small class="text-muted" style="font-size: 11px;">Gems delivered</small>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 rounded-4 shadow-sm p-3" style="background: #fff; border: 1px solid #e2e8f0 !important;">
                    <small class="text-muted d-block">Total Orders</small>
                    <h4 class="fw-bold text-primary mb-0">{{ number_format($stats['total_transfers']) }}</h4>
                    <small class="text-muted" style="font-size: 11px;">Completed transfers</small>
                </div>
            </div>
        </div>

        <!-- Recent Transfers List -->
        <div class="card border-0 rounded-4 shadow-sm" style="background: #fff; border: 1px solid #e2e8f0 !important;">
            <div class="card-header bg-transparent border-0 p-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">Recent Transfers</h6>
                <a href="{{ route('reseller.transfers') }}" class="text-primary text-decoration-none fw-semibold" style="font-size: 12px;">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush" style="font-size: 13px;">
                    @forelse($recentTransfers as $tr)
                        <div class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $tr->user->avatar_url ?? '' }}" class="rounded-circle border" style="width: 36px; height: 36px; object-fit: cover;">
                                <div>
                                    <strong class="d-block text-dark">{{ $tr->user->name ?? 'User #' . $tr->user_id }}</strong>
                                    <span class="text-muted" style="font-size: 11px;">UID: {{ $tr->target_account_id }} · {{ $tr->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-warning d-block">+{{ number_format($tr->coins) }} gems</span>
                                @if($tr->amount_bdt)
                                    <small class="text-success fw-semibold">৳{{ number_format($tr->amount_bdt, 2) }}</small>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fa-solid fa-receipt fa-2x mb-2" style="opacity: 0.3;"></i>
                            <p class="mb-0">No transfers yet. Recharge users above!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Refill Stock Deposit Request -->
<div class="modal fade" id="refillModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-circle-plus text-warning me-2"></i> Request Stock Refill</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('reseller.deposit.submit') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="p-3 rounded-3 mb-3" style="background: rgba(245,158,11,0.08); border: 1px dashed rgba(245,158,11,0.3); font-size: 12px;">
                        <strong>Exchange Rate:</strong> 1 BDT = {{ $settings['reseller_coin_rate_per_bdt'] }} Gems (Wholesale Agent Rate)<br>
                        <strong>Limits:</strong> Min ৳{{ number_format($settings['min_deposit']) }} - Max ৳{{ number_format($settings['max_deposit']) }}
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Deposit Amount (BDT) <span class="text-danger">*</span></label>
                        <input type="number" name="amount_bdt" id="depBdtInput" class="form-control rounded-3 fs-5 font-monospace" placeholder="1000" min="{{ $settings['min_deposit'] }}" max="{{ $settings['max_deposit'] }}" required oninput="calculateRefillCoins(this.value)">
                        <small class="text-muted">You will receive approx: <strong class="text-warning" id="depCoinsEst">0</strong> Gems</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select rounded-3" required>
                            @foreach($paymentMethods as $pm)
                                <option value="{{ $pm->name }}">{{ $pm->name }} ({{ $pm->account_number }} - {{ $pm->account_type }})</option>
                            @endforeach
                            <option value="Bank Transfer">Bank Transfer</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Sender Phone / Account Number <span class="text-danger">*</span></label>
                        <input type="text" name="sender_number" class="form-control rounded-3 font-monospace" placeholder="01700000000" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Transaction ID (TrxID) <span class="text-danger">*</span></label>
                        <input type="text" name="transaction_id" class="form-control rounded-3 font-monospace" placeholder="e.g. 9H8A7B6C" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Payment Screenshot (Optional)</label>
                        <input type="file" name="screenshot" class="form-control rounded-3" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold rounded-3">Submit Refill Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Request Cash-Out / Withdrawal -->
<div class="modal fade" id="withdrawModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-hand-holding-dollar text-primary me-2"></i> Request Coin Cash-Out</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('reseller.withdrawal.submit') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="p-3 rounded-3 mb-3" style="background: rgba(59,130,246,0.08); border: 1px dashed rgba(59,130,246,0.3); font-size: 12px;">
                        <strong>Commission Fee:</strong> {{ $settings['withdraw_commission_rate'] }}% deducted upon payout.<br>
                        <strong>Limits:</strong> Min {{ number_format($settings['min_withdraw']) }} - Max {{ number_format($settings['max_withdraw']) }} Coins.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Coins to Withdraw <span class="text-danger">*</span></label>
                        <input type="number" name="coins_amount" id="withCoinsInput" class="form-control rounded-3 fs-5 font-monospace" placeholder="10000" min="{{ $settings['min_withdraw'] }}" max="{{ min($settings['max_withdraw'], $reseller->coins_balance) }}" required oninput="calculateWithdrawNet(this.value)">
                        <small class="text-muted">Net Payout: <strong class="text-success" id="withNetEst">৳0.00 BDT</strong></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Payout Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select rounded-3" required>
                            <option value="bKash Personal">bKash Personal</option>
                            <option value="Nagad Personal">Nagad Personal</option>
                            <option value="Rocket">Rocket</option>
                            <option value="Bank Account">Bank Account</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Account / Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="account_number" class="form-control rounded-3 font-monospace" placeholder="01700000000" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Account Holder Name</label>
                        <input type="text" name="account_name" class="form-control rounded-3" placeholder="e.g. Murad Hasan">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold rounded-3">Submit Withdrawal</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const ratePerBdt = {{ (float) $settings['reseller_coin_rate_per_bdt'] }};
const commissionRate = {{ (float) $settings['withdraw_commission_rate'] }};

function calculateRefillCoins(bdt) {
    const val = parseFloat(bdt) || 0;
    const coins = Math.round(val * ratePerBdt);
    document.getElementById('depCoinsEst').innerText = coins.toLocaleString();
}

function calculateWithdrawNet(coins) {
    const c = parseInt(coins) || 0;
    const gross = c / (ratePerBdt || 60);
    const fee = (gross * commissionRate) / 100;
    const net = gross - fee;
    document.getElementById('withNetEst').innerText = '৳' + (net > 0 ? net.toFixed(2) : '0.00') + ' BDT';
}

function lookupUser() {
    const accountId = document.getElementById('targetAccountId').value.trim();
    if (!accountId) {
        window.showToast('Please enter an Account ID first', 'warning');
        return;
    }

    const btn = document.getElementById('validateUserBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Checking...';

    fetch(`{{ route('reseller.validate-user') }}?account_id=${encodeURIComponent(accountId)}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-magnifying-glass me-1"></i> Verify User';

        if (res.status && res.data) {
            const user = res.data;
            document.getElementById('previewAvatar').src = user.avatar_url;
            document.getElementById('previewName').innerText = user.name || user.nickname || 'Unknown';
            document.getElementById('previewAccountId').innerText = 'UID: ' + user.account_id;
            document.getElementById('previewLevel').innerText = user.level || 'Lv1';
            document.getElementById('previewCoins').innerText = Number(user.coins).toLocaleString() + ' Gems';
            document.getElementById('userPreviewCard').style.display = 'block';
            window.showToast('User found: ' + user.name, 'success');
        } else {
            document.getElementById('userPreviewCard').style.display = 'none';
            window.showToast(res.message || 'User not found', 'error');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-magnifying-glass me-1"></i> Verify User';
        window.showToast('Error validating user', 'error');
    });
}
</script>
@endpush
@endsection
