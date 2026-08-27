@extends('layouts.admin')

@section('title', $user->display_name . ' - User Profile & Coin History')

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb & Back -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" style="border-radius: 10px; font-weight: 600; font-size: 13px;">
                <i class="fa-solid fa-arrow-left"></i> Back to Users Directory
            </a>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn-ch-gold" onclick="openAdjustCoinModal('{{ $user->id }}', '{{ addslashes($user->display_name) }}', '{{ $user->coins }}', '{{ $user->avatar_url }}')">
                <i class="fa-solid fa-coins"></i> Adjust Coins Balance
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Side: Profile & Coin Balance Card -->
        <div class="col-12 col-xl-4">
            <!-- Profile Overview Card -->
            <div class="card border-0 rounded-4 overflow-hidden shadow-sm mb-4" style="background: var(--card-bg-light); border: 1px solid var(--card-border-light) !important;">
                <!-- Cover Banner -->
                <div style="height: 110px; background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%); position: relative;">
                    @if($user->cover_photo_url)
                        <img src="{{ $user->cover_photo_url }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @endif
                </div>

                <!-- Avatar & Core Info -->
                <div class="card-body px-4 pb-4 pt-0 text-center position-relative">
                    <div style="margin-top: -55px;" class="mb-3 position-relative d-inline-block">
                        <img src="{{ $user->avatar_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($user->display_name) . '&background=3b82f6&color=fff' }}" 
                             alt="{{ $user->display_name }}" 
                             class="rounded-circle" 
                             style="width: 100px; height: 100px; object-fit: cover; border: 4px solid var(--card-bg-light); box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                        <span class="online-pulse-dot {{ $user->is_active ? 'online' : 'offline' }}" style="width: 16px; height: 16px; bottom: 6px; right: 6px;"></span>
                    </div>

                    <h4 class="fw-bold mb-1" style="color: var(--text-primary);">
                        {{ $user->display_name }}
                        @if($user->is_verified)
                            <i class="fa-solid fa-circle-check text-primary ms-1" title="Verified" style="font-size: 16px;"></i>
                        @endif
                    </h4>
                    <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
                        <span class="copyable-chip" onclick="copyToClipboard('{{ $user->account_id }}', 'Account ID copied!')">
                            <i class="fa-solid fa-id-card me-1"></i> {{ $user->account_id }}
                            <i class="fa-regular fa-copy"></i>
                        </span>
                        <span class="badge bg-secondary rounded-pill px-2 py-1">{{ $user->level ?: 'Lv1' }}</span>
                        <span class="status-pill-modern {{ $user->is_active ? 'active' : 'inactive' }}" style="font-size: 11px;">
                            <span class="status-pulsing-dot"></span> {{ $user->is_active ? 'Online' : 'Offline' }}
                        </span>
                    </div>

                    <!-- 3D Coin Balance Card -->
                    <div class="p-3 rounded-4 mb-3 text-start" style="background: linear-gradient(135deg, rgba(254, 243, 199, 0.6) 0%, rgba(253, 230, 138, 0.4) 100%); border: 1px solid rgba(245, 158, 11, 0.3);">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted fw-bold" style="font-size: 12px; text-transform: uppercase;">Available Coin Balance</span>
                            <i class="fa-solid fa-coins text-warning fa-lg"></i>
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline">
                            <h2 class="fw-extrabold mb-0" style="color: #92400e; font-size: 30px;">{{ number_format($user->coins) }}</h2>
                            <span class="badge bg-warning text-dark font-monospace fw-bold">COINS</span>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <button type="button" class="btn-ch-gold w-100 justify-content-center py-2 mb-3" onclick="openAdjustCoinModal('{{ $user->id }}', '{{ addslashes($user->display_name) }}', '{{ $user->coins }}', '{{ $user->avatar_url }}')">
                        <i class="fa-solid fa-plus-minus me-1"></i> Add / Deduct Coins
                    </button>

                    <hr style="border-color: var(--border-color); margin: 20px 0;">

                    <!-- Info List -->
                    <div class="text-start">
                        <h6 class="text-muted fw-bold mb-3" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Account Metadata</h6>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2" style="font-size: 13px;">
                            <li class="d-flex justify-content-between py-1 border-bottom" style="border-color: var(--border-color) !important;">
                                <span class="text-muted">Phone Number:</span>
                                <strong class="font-monospace text-primary">{{ $user->phone ?: 'Not provided' }}</strong>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom" style="border-color: var(--border-color) !important;">
                                <span class="text-muted">Email:</span>
                                <strong>{{ $user->email }}</strong>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom" style="border-color: var(--border-color) !important;">
                                <span class="text-muted">Country / City:</span>
                                <strong>{{ $user->country ?: 'Global' }} ({{ $user->city ?: 'N/A' }})</strong>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom" style="border-color: var(--border-color) !important;">
                                <span class="text-muted">Gender / Age:</span>
                                <strong>{{ ucfirst($user->gender ?: 'N/A') }} &bull; {{ $user->age ? $user->age . ' yrs' : 'N/A' }}</strong>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom" style="border-color: var(--border-color) !important;">
                                <span class="text-muted">Video Call Rate:</span>
                                <span class="badge bg-light text-dark border">{{ $user->video_call_rate ?: 100 }} coins / min</span>
                            </li>
                            <li class="d-flex justify-content-between py-1">
                                <span class="text-muted">Member Since:</span>
                                <strong>{{ $user->created_at ? $user->created_at->format('M d, Y') : '-' }}</strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Transactions Ledger & Deposit History -->
        <div class="col-12 col-xl-8">
            <!-- Coin Transactions Ledger Card -->
            <div class="premium-table-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-receipt text-primary fa-lg"></i>
                        <h5 class="mb-0 fw-bold" style="font-size: 16px; color: var(--text-primary);">Coin Transactions Ledger</h5>
                    </div>
                    <span class="badge bg-light text-muted border px-2 py-1">{{ count($user->coinTransactions) }} events</span>
                </div>
                <div class="table-responsive">
                    <table class="premium-datatable">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Type</th>
                                <th>Coins Changed</th>
                                <th>Balance After</th>
                                <th>Description / Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->coinTransactions as $tx)
                                <tr>
                                    <td>
                                        <small class="text-muted fw-semibold">{{ $tx->created_at->format('M d, Y') }}<br>{{ $tx->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $tx->amount >= 0 ? 'bg-success' : 'bg-danger' }} rounded-pill px-2 py-1" style="font-size: 11px;">
                                            {{ str_replace('_', ' ', ucfirst($tx->type)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong style="font-size: 15px; color: {{ $tx->amount >= 0 ? '#10b981' : '#ef4444' }};">
                                            {{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount) }}
                                        </strong>
                                        <small class="text-muted">Coins</small>
                                    </td>
                                    <td>
                                        <span class="font-monospace fw-bold">{{ number_format($tx->balance_after) }}</span>
                                    </td>
                                    <td>
                                        <div style="font-size: 13px; color: var(--text-primary);">{{ $tx->description }}</div>
                                        @if($tx->reference_id)
                                            <small class="text-muted font-monospace">{{ $tx->reference_id }}</small>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No coin transactions on record for this user yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- KYC Identity Verification Card -->
            <div class="premium-table-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-id-card text-info fa-lg"></i>
                        <h5 class="mb-0 fw-bold" style="font-size: 16px; color: var(--text-primary);">KYC Identity Verification</h5>
                    </div>
                    <div>
                        @if($user->is_verified)
                            <span class="badge bg-success" style="font-size: 12px; padding: 6px 12px;">
                                <i class="fa-solid fa-circle-check me-1"></i> Verified Profile
                            </span>
                        @elseif($user->kyc_status === 'pending')
                            <span class="badge bg-warning text-dark" style="font-size: 12px; padding: 6px 12px;">
                                <i class="fa-solid fa-clock me-1"></i> Review Pending
                            </span>
                        @else
                            <span class="badge bg-secondary" style="font-size: 12px; padding: 6px 12px;">
                                Not Verified
                            </span>
                        @endif
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="premium-datatable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Doc Type</th>
                                <th>Legal Name</th>
                                <th>ID Number</th>
                                <th>Photos</th>
                                <th>Status</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->kycVerifications as $kyc)
                                <tr>
                                    <td><small class="text-muted">{{ $kyc->submitted_at ? $kyc->submitted_at->format('M d, Y') : $kyc->created_at->format('M d, Y') }}</small></td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $kyc->document_type_label }}
                                        </span>
                                    </td>
                                    <td><strong>{{ $kyc->full_name }}</strong></td>
                                    <td><span class="font-monospace text-primary fw-bold">{{ $kyc->document_number }}</span></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <a href="{{ $kyc->front_image_url }}" target="_blank" class="badge bg-primary text-decoration-none">Front</a>
                                            @if($kyc->back_image_url)
                                                <a href="{{ $kyc->back_image_url }}" target="_blank" class="badge bg-secondary text-decoration-none">Back</a>
                                            @endif
                                            <a href="{{ $kyc->selfie_image_url }}" target="_blank" class="badge bg-danger text-decoration-none">Selfie</a>
                                        </div>
                                    </td>
                                    <td>
                                        @if($kyc->status === 'approved')
                                            <span class="badge bg-success">Verified</span>
                                        @elseif($kyc->status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right;">
                                        @if($kyc->status === 'pending')
                                            <form action="{{ route('admin.kyc.approve', $kyc->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success py-1 px-2" style="font-size: 11px;">
                                                    <i class="fa-solid fa-check"></i> Approve
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.kyc.index', ['search' => $user->account_id]) }}" class="btn btn-sm btn-outline-info py-1 px-2" style="font-size: 11px;">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No KYC verification documents submitted by this user yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Manual Deposit Requests Card -->
            <div class="premium-table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-money-bill-transfer text-warning fa-lg"></i>
                        <h5 class="mb-0 fw-bold" style="font-size: 16px; color: var(--text-primary);">Deposit Requests History</h5>
                    </div>
                    <span class="badge bg-light text-muted border px-2 py-1">{{ count($user->depositRequests) }} requests</span>
                </div>
                <div class="table-responsive">
                    <table class="premium-datatable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Gateway</th>
                                <th>Amount</th>
                                <th>Coins</th>
                                <th>TrxID</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->depositRequests as $dep)
                                <tr>
                                    <td><small class="text-muted">{{ $dep->created_at->format('M d, Y h:i A') }}</small></td>
                                    <td>
                                        <span class="gateway-badge {{ strtolower(explode(' ', $dep->payment_method_name)[0]) }}">
                                            {{ $dep->payment_method_name }}
                                        </span>
                                    </td>
                                    <td><strong>৳ {{ number_format($dep->amount, 2) }}</strong></td>
                                    <td>
                                        <div class="coin-badge-3d" style="padding: 3px 10px;">
                                            <i class="fa-solid fa-coins coin-icon-glow" style="font-size: 12px;"></i>
                                            <span class="coin-amount-text" style="font-size: 13px;">{{ number_format($dep->coins) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="copyable-chip" onclick="copyToClipboard('{{ $dep->transaction_id }}', 'TrxID copied!')">
                                            <span>{{ $dep->transaction_id }}</span>
                                            <i class="fa-regular fa-copy"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-pill-modern {{ $dep->status }}">
                                            <span class="status-pulsing-dot"></span>
                                            {{ ucfirst($dep->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No deposit requests submitted by this user yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reusable Coin Adjust Modal Included -->
<div class="modal fade" id="adjustCoinModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-modern-dialog">
        <div class="modal-content modal-modern-content">
            <div class="modal-modern-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-box stat-icon-gold" style="width: 44px; height: 44px; font-size: 18px;">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" style="color: var(--text-primary);">Manual Coins Adjustment</h5>
                        <small class="text-muted">Instantly credit or debit coins from user balance</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="adjustCoinForm" method="POST" action="">
                @csrf
                <div class="modal-modern-body">
                    <div class="p-3 rounded-4 mb-3 d-flex justify-content-between align-items-center" style="background: var(--bg-main); border: 1px solid var(--border-color);">
                        <div class="d-flex align-items-center gap-3">
                            <img id="modalUserAvatar" src="" class="rounded-circle" style="width: 44px; height: 44px; object-fit: cover; border: 2px solid var(--ch-gold);">
                            <div>
                                <small class="text-muted d-block" style="font-size: 11px; text-transform: uppercase;">Target Account</small>
                                <strong id="modalUserName" style="font-size: 15px; color: var(--text-primary);"></strong>
                            </div>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block" style="font-size: 11px; text-transform: uppercase;">Current Balance</small>
                            <span class="coin-badge-3d">
                                <i class="fa-solid fa-coins coin-icon-glow"></i>
                                <span id="modalCurrentCoins" class="coin-amount-text">0</span>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Select Operation</label>
                        <div class="coin-action-segmented">
                            <div class="coin-segment-tab active-add" data-action="add">
                                <i class="fa-solid fa-plus-circle"></i> Add Coins
                            </div>
                            <div class="coin-segment-tab" data-action="deduct">
                                <i class="fa-solid fa-minus-circle"></i> Deduct Coins
                            </div>
                            <div class="coin-segment-tab" data-action="set">
                                <i class="fa-solid fa-equals"></i> Set Exact
                            </div>
                        </div>
                        <input type="hidden" name="action" id="coinActionInput" value="add">
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold mb-0" style="font-size: 13px;">Amount of Coins <span class="text-danger">*</span></label>
                            <span id="newBalancePreview" class="text-muted" style="font-size: 12px;"></span>
                        </div>
                        <div class="position-relative">
                            <input type="number" name="amount" id="coinAmountInput" class="form-control form-control-lg rounded-3 fw-bold" style="font-family: var(--font-heading); font-size: 18px; padding-left: 45px;" placeholder="e.g. 1000" min="1" required>
                            <i class="fa-solid fa-coins text-warning position-absolute" style="left: 16px; top: 50%; transform: translateY(-50%); font-size: 18px;"></i>
                        </div>
                        <div class="d-flex gap-2 flex-wrap mt-2">
                            <button type="button" class="quick-chip-btn" onclick="addCoinPreset(100)">+100</button>
                            <button type="button" class="quick-chip-btn" onclick="addCoinPreset(500)">+500</button>
                            <button type="button" class="quick-chip-btn" onclick="addCoinPreset(1000)">+1,000</button>
                            <button type="button" class="quick-chip-btn" onclick="addCoinPreset(5000)">+5,000</button>
                            <button type="button" class="quick-chip-btn" onclick="addCoinPreset(10000)">+10,000</button>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold" style="font-size: 13px;">Transaction Reason / Note</label>
                        <input type="text" name="reason" class="form-control rounded-3" placeholder="e.g. Manual credit / Special reward">
                    </div>
                </div>
                <div class="modal-modern-footer">
                    <button type="button" class="btn btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-ch-primary">
                        <i class="fa-solid fa-circle-check"></i> Apply Coin Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="ch-toast-container" id="toastContainer"></div>

@push('scripts')
<script>
let currentSelectedUserCoins = 0;

function openAdjustCoinModal(userId, userName, currentCoins, avatarUrl) {
    const modalEl = document.getElementById('adjustCoinModal');
    const form = document.getElementById('adjustCoinForm');
    form.action = `/admin/users/${userId}/adjust-coins`;
    
    currentSelectedUserCoins = Number(currentCoins) || 0;
    document.getElementById('modalUserName').innerText = userName;
    document.getElementById('modalCurrentCoins').innerText = currentSelectedUserCoins.toLocaleString();
    document.getElementById('modalUserAvatar').src = avatarUrl || `https://ui-avatars.com/api/?name=${encodeURIComponent(userName)}&background=3b82f6&color=fff`;
    document.getElementById('coinAmountInput').value = '';
    document.getElementById('newBalancePreview').innerText = '';

    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
}

function addCoinPreset(val) {
    const input = document.getElementById('coinAmountInput');
    input.value = (Number(input.value) || 0) + val;
    updateBalancePreview();
}

function updateBalancePreview() {
    const action = document.getElementById('coinActionInput').value;
    const amount = Number(document.getElementById('coinAmountInput').value) || 0;
    const previewEl = document.getElementById('newBalancePreview');
    
    if (amount <= 0) {
        previewEl.innerText = '';
        return;
    }
    
    let newBal = currentSelectedUserCoins;
    if (action === 'add') newBal += amount;
    else if (action === 'deduct') newBal = Math.max(0, currentSelectedUserCoins - amount);
    else if (action === 'set') newBal = amount;
    
    previewEl.innerHTML = `New Balance: <strong class="text-primary">${newBal.toLocaleString()} Coins</strong>`;
}

document.getElementById('coinAmountInput')?.addEventListener('input', updateBalancePreview);

document.querySelectorAll('.coin-segment-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const action = this.getAttribute('data-action');
        document.getElementById('coinActionInput').value = action;
        document.querySelectorAll('.coin-segment-tab').forEach(t => t.className = 'coin-segment-tab');
        this.className = `coin-segment-tab active-${action}`;
        updateBalancePreview();
    });
});

function copyToClipboard(text, msg) {
    navigator.clipboard.writeText(text).then(() => {
        window.showToast(msg || 'Copied to clipboard!', 'success');
    });
}
</script>
@endpush
@endsection
