@extends('layouts.admin')

@section('title', $user->display_name . ' - User Details')

@section('content')
<div class="user-detail-container">
    <div class="mb-4">
        <a href="{{ route('admin.users.index') }}" class="btn-secondary-custom mb-3" style="text-decoration: none;">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Users List
        </a>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="custom-alert alert-success mb-4">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="row g-4">
        <!-- User Profile Card -->
        <div class="col-12 col-lg-4">
            <div class="card p-4 text-center" style="border: 1px solid var(--border-color); border-radius: var(--radius-md); background: var(--bg-card);">
                <div class="position-relative d-inline-block mx-auto mb-3">
                    <img src="{{ $user->avatar_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($user->display_name) . '&background=3b82f6&color=fff' }}" 
                         alt="{{ $user->display_name }}" 
                         class="rounded-circle" 
                         style="width: 100px; height: 100px; object-fit: cover; border: 3px solid var(--primary);">
                    <span class="status-indicator {{ $user->is_active ? 'online' : 'offline' }}" style="width: 16px; height: 16px; bottom: 4px; right: 4px;"></span>
                </div>
                <h4 class="mb-1" style="font-weight: 700; color: var(--text-primary);">{{ $user->display_name }}</h4>
                <p class="text-muted mb-2" style="font-size: 13px;">Account ID: <code class="badge-account-id">{{ $user->account_id }}</code></p>
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <span class="badge bg-primary">{{ $user->level ?: 'Lv1' }}</span>
                    <span class="badge bg-secondary">{{ ucfirst($user->gender ?: 'User') }}</span>
                    <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">{{ $user->is_active ? 'Online' : 'Offline' }}</span>
                </div>

                <!-- Coin Balance Box -->
                <div class="p-3 mb-3 text-start" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: var(--radius-sm);">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted" style="font-size: 13px;">Current Coin Balance</span>
                        <i class="fa-solid fa-coins text-warning"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-baseline">
                        <h2 class="mb-0" style="color: #d97706; font-weight: 800;">{{ number_format($user->coins) }}</h2>
                        <small class="text-muted">Coins</small>
                    </div>
                </div>

                <!-- Action Button -->
                <button type="button" class="btn-primary-custom w-100" onclick="openAdjustCoinModal('{{ $user->id }}', '{{ addslashes($user->display_name) }}', '{{ $user->coins }}')">
                    <i class="fa-solid fa-plus-minus me-2"></i> Add / Deduct Coins
                </button>

                <hr style="border-color: var(--border-color); margin: 20px 0;">

                <div class="text-start">
                    <h6 class="text-muted mb-3" style="font-size: 12px; text-transform: uppercase;">User Information</h6>
                    <ul class="list-unstyled" style="font-size: 14px; line-height: 2;">
                        <li><strong>Phone:</strong> {{ $user->phone ?: 'Not provided' }}</li>
                        <li><strong>Email:</strong> {{ $user->email }}</li>
                        <li><strong>Country:</strong> {{ $user->country ?: 'N/A' }} ({{ $user->city ?: 'N/A' }})</li>
                        <li><strong>Call Rate:</strong> {{ $user->video_call_rate ?: 100 }} coins / min</li>
                        <li><strong>Joined:</strong> {{ $user->created_at ? $user->created_at->format('M d, Y H:i') : '-' }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Right Side: Transactions & Deposits History -->
        <div class="col-12 col-lg-8">
            <!-- Recent Transactions Ledger -->
            <div class="card mb-4" style="border: 1px solid var(--border-color); border-radius: var(--radius-md); background: var(--bg-card);">
                <div class="card-header p-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--border-color); background: transparent;">
                    <h5 class="mb-0" style="font-size: 16px; font-weight: 600;"><i class="fa-solid fa-list-check me-2 text-primary"></i> Coin Transactions History</h5>
                </div>
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Balance After</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->coinTransactions as $tx)
                                <tr>
                                    <td><small class="text-muted">{{ $tx->created_at->format('M d, Y H:i') }}</small></td>
                                    <td>
                                        <span class="badge {{ $tx->amount >= 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ str_replace('_', ' ', ucfirst($tx->type)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong style="color: {{ $tx->amount >= 0 ? '#10b981' : '#ef4444' }};">
                                            {{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount) }}
                                        </strong>
                                    </td>
                                    <td>{{ number_format($tx->balance_after) }}</td>
                                    <td><small class="text-muted">{{ $tx->description }}</small></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No coin transactions yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Deposit Requests -->
            <div class="card" style="border: 1px solid var(--border-color); border-radius: var(--radius-md); background: var(--bg-card);">
                <div class="card-header p-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--border-color); background: transparent;">
                    <h5 class="mb-0" style="font-size: 16px; font-weight: 600;"><i class="fa-solid fa-money-bill-transfer me-2 text-success"></i> Deposit Requests</h5>
                </div>
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th>Coins</th>
                                <th>TrxID</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->depositRequests as $dep)
                                <tr>
                                    <td><small class="text-muted">{{ $dep->created_at->format('M d, Y') }}</small></td>
                                    <td><strong>{{ $dep->payment_method_name }}</strong></td>
                                    <td>৳ {{ number_format($dep->amount, 2) }}</td>
                                    <td><strong class="text-warning"><i class="fa-solid fa-coins me-1"></i>{{ number_format($dep->coins) }}</strong></td>
                                    <td><code class="badge-account-id">{{ $dep->transaction_id }}</code></td>
                                    <td>
                                        <span class="status-badge {{ $dep->status === 'approved' ? 'badge-active' : ($dep->status === 'rejected' ? 'badge-inactive' : 'badge-warning') }}">
                                            {{ ucfirst($dep->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No deposit requests submitted yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Add/Adjust Coins (Included) -->
<div class="custom-modal-backdrop" id="adjustCoinModal" style="display: none;">
    <div class="custom-modal-dialog">
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <i class="fa-solid fa-coins text-warning"></i>
                    <span>Adjust User Coins</span>
                </h5>
                <button type="button" class="btn-close-modal" onclick="closeAdjustCoinModal()">&times;</button>
            </div>
            <form id="adjustCoinForm" method="POST" action="{{ route('admin.users.adjust-coins', $user->id) }}">
                @csrf
                <div class="custom-modal-body">
                    <div class="user-summary-card mb-3 p-3" style="background: var(--bg-main); border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">Target User</small>
                                <strong style="font-size: 15px; color: var(--text-primary);">{{ $user->display_name }}</strong>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">Current Balance</small>
                                <span class="badge bg-warning text-dark px-2 py-1" style="font-weight: 700;">
                                    <i class="fa-solid fa-coins me-1"></i> {{ number_format($user->coins) }} Coins
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom">Action Type</label>
                        <div class="action-type-selector">
                            <label class="action-type-pill active" data-action="add">
                                <input type="radio" name="action" value="add" checked style="display: none;">
                                <i class="fa-solid fa-plus text-success"></i> Add Coins
                            </label>
                            <label class="action-type-pill" data-action="deduct">
                                <input type="radio" name="action" value="deduct" style="display: none;">
                                <i class="fa-solid fa-minus text-danger"></i> Deduct Coins
                            </label>
                            <label class="action-type-pill" data-action="set">
                                <input type="radio" name="action" value="set" style="display: none;">
                                <i class="fa-solid fa-equals text-primary"></i> Set Exact
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom">Amount of Coins <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="coinAmountInput" class="form-control-custom" placeholder="e.g. 500" min="1" required>
                        <div class="quick-amounts mt-2 d-flex gap-2 flex-wrap">
                            <button type="button" class="btn-chip" onclick="setCoinValue(100)">+100</button>
                            <button type="button" class="btn-chip" onclick="setCoinValue(500)">+500</button>
                            <button type="button" class="btn-chip" onclick="setCoinValue(1000)">+1,000</button>
                            <button type="button" class="btn-chip" onclick="setCoinValue(5000)">+5,000</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom">Reason / Transaction Note</label>
                        <input type="text" name="reason" class="form-control-custom" placeholder="e.g. Manual credit / Special reward">
                    </div>
                </div>
                <div class="custom-modal-footer">
                    <button type="button" class="btn-secondary-custom" onclick="closeAdjustCoinModal()">Cancel</button>
                    <button type="submit" class="btn-primary-custom"><i class="fa-solid fa-check me-1"></i> Apply Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openAdjustCoinModal(userId, userName, currentCoins) {
    document.getElementById('adjustCoinModal').style.display = 'flex';
}
function closeAdjustCoinModal() {
    document.getElementById('adjustCoinModal').style.display = 'none';
}
function setCoinValue(val) {
    const input = document.getElementById('coinAmountInput');
    input.value = (Number(input.value) || 0) + val;
}
document.querySelectorAll('.action-type-pill').forEach(pill => {
    pill.addEventListener('click', function() {
        document.querySelectorAll('.action-type-pill').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        const radio = this.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
    });
});
</script>
@endpush
@endsection
