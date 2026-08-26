@extends('layouts.admin')

@section('title', 'Payment Methods Management')

@section('content')
<div class="payment-methods-container">
    <!-- Page Header & Action -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="page-title mb-1" style="font-size: 24px; font-weight: 700; color: var(--text-primary);">Payment Methods</h1>
            <p class="text-muted mb-0" style="font-size: 14px;">Configure manual deposit payment gateways (bKash, Nagad, Rocket, Bank) shown in the Mobile App.</p>
        </div>
        <button type="button" class="btn-primary-custom" onclick="openCreatePaymentMethodModal()">
            <i class="fa-solid fa-plus me-2"></i> Add Payment Method
        </button>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="custom-alert alert-success mb-4">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="custom-alert alert-danger mb-4">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Payment Methods Grid / Table -->
    <div class="row g-4 mb-4">
        @forelse($methods as $method)
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card p-3 h-100 position-relative" style="border: 1px solid var(--border-color); border-radius: var(--radius-md); background: var(--bg-card); display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="pm-icon-wrapper" style="width: 44px; height: 44px; border-radius: 10px; background: var(--bg-main); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                    @if($method->icon_url)
                                        <img src="{{ $method->icon_url }}" alt="{{ $method->name }}" style="width: 100%; height: 100%; object-fit: contain; padding: 4px;">
                                    @else
                                        <i class="fa-solid fa-wallet text-primary" style="font-size: 20px;"></i>
                                    @endif
                                </div>
                                <div>
                                    <h5 class="mb-0" style="font-size: 15px; font-weight: 700; color: var(--text-primary);">{{ $method->name }}</h5>
                                    <span class="badge bg-secondary" style="font-size: 11px;">{{ $method->account_type }}</span>
                                </div>
                            </div>
                            <form action="{{ route('admin.payment-methods.toggle-status', $method->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="status-badge {{ $method->is_active ? 'badge-active' : 'badge-inactive' }}" title="Toggle Active">
                                    <span class="status-dot"></span>
                                    {{ $method->is_active ? 'Active' : 'Disabled' }}
                                </button>
                            </form>
                        </div>

                        <!-- Account Number -->
                        <div class="p-2 mb-3 rounded" style="background: var(--bg-main); border: 1px solid var(--border-color);">
                            <small class="text-muted d-block" style="font-size: 11px; text-transform: uppercase;">Account / Phone Number</small>
                            <span class="fw-bold text-primary font-monospace" style="font-size: 15px;">{{ $method->account_number }}</span>
                        </div>

                        <!-- Info details -->
                        <div class="mb-3" style="font-size: 13px; line-height: 1.8;">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Exchange Rate:</span>
                                <strong>1 BDT = {{ $method->rate_per_bdt }} Coins</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Deposit Limits:</span>
                                <span>৳{{ number_format($method->min_deposit) }} - ৳{{ number_format($method->max_deposit) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Total Deposits:</span>
                                <span>{{ $method->deposit_requests_count }} requests</span>
                            </div>
                        </div>

                        @if($method->instructions)
                            <div class="p-2 rounded mb-3" style="background: rgba(59, 130, 246, 0.05); border: 1px dashed rgba(59, 130, 246, 0.3); font-size: 12px; color: var(--text-secondary); max-height: 80px; overflow-y: auto;">
                                <strong>Instructions:</strong><br>
                                {!! nl2br(e($method->instructions)) !!}
                            </div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2" style="border-top: 1px solid var(--border-color);">
                        <button type="button" class="btn-action btn-view" onclick="openEditPaymentMethodModal({{ json_encode($method) }})">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                        </button>
                        <form action="{{ route('admin.payment-methods.destroy', $method->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this payment method?');" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action" style="color: var(--danger);">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="card p-5" style="border: 1px dashed var(--border-color); border-radius: var(--radius-md); background: var(--bg-card);">
                    <i class="fa-solid fa-credit-card fa-3x text-muted mb-3"></i>
                    <h4>No Payment Methods Found</h4>
                    <p class="text-muted">Add your bKash, Nagad, Rocket, or Bank account numbers so users can make deposits.</p>
                    <button type="button" class="btn-primary-custom mx-auto" onclick="openCreatePaymentMethodModal()">
                        <i class="fa-solid fa-plus me-2"></i> Add First Method
                    </button>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Modal for Create / Edit Payment Method -->
<div class="custom-modal-backdrop" id="paymentMethodModal" style="display: none;">
    <div class="custom-modal-dialog">
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <h5 class="modal-title" id="pmModalTitle">Add Payment Method</h5>
                <button type="button" class="btn-close-modal" onclick="closePaymentMethodModal()">&times;</button>
            </div>
            <form id="pmForm" method="POST" action="{{ route('admin.payment-methods.store') }}" enctype="multipart/form-data">
                @csrf
                <div id="pmMethodSpoof"></div>
                <div class="custom-modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label-custom">Method Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="pmName" class="form-control-custom" placeholder="e.g. bKash Personal" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label-custom">Method Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="pmCode" class="form-control-custom" placeholder="e.g. bkash / nagad / rocket" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label-custom">Account Type <span class="text-danger">*</span></label>
                            <select name="account_type" id="pmAccountType" class="form-select-custom" required>
                                <option value="Personal">Personal</option>
                                <option value="Agent">Agent</option>
                                <option value="Merchant">Merchant</option>
                                <option value="Bank Account">Bank Account</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label-custom">Account / Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="account_number" id="pmAccountNumber" class="form-control-custom" placeholder="e.g. 01712345678" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label-custom">Min Deposit (BDT) <span class="text-danger">*</span></label>
                            <input type="number" name="min_deposit" id="pmMinDeposit" class="form-control-custom" value="50" min="1" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label-custom">Max Deposit (BDT) <span class="text-danger">*</span></label>
                            <input type="number" name="max_deposit" id="pmMaxDeposit" class="form-control-custom" value="25000" min="1" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">Coin Rate per 1 BDT <span class="text-danger">*</span></label>
                            <input type="number" name="rate_per_bdt" id="pmRatePerBdt" class="form-control-custom" value="10" min="1" required>
                            <small class="text-muted">Example: 10 means 100 BDT = 1,000 Coins.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">Instructions for User (App Screen)</label>
                            <textarea name="instructions" id="pmInstructions" class="form-control-custom" rows="3" placeholder="1. Send money to this number&#10;2. Copy transaction ID..."></textarea>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label-custom">Logo / Icon (Optional)</label>
                            <input type="file" name="icon" class="form-control-custom" accept="image/*">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label-custom">QR Code Image (Optional)</label>
                            <input type="file" name="qr_code" class="form-control-custom" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="d-flex align-items-center gap-2" style="cursor: pointer;">
                                <input type="checkbox" name="is_active" id="pmIsActive" value="1" checked style="width: 18px; height: 18px;">
                                <span style="font-weight: 600; font-size: 14px;">Active & Visible in Mobile App</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="custom-modal-footer">
                    <button type="button" class="btn-secondary-custom" onclick="closePaymentMethodModal()">Cancel</button>
                    <button type="submit" class="btn-primary-custom"><i class="fa-solid fa-floppy-disk me-1"></i> Save Method</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openCreatePaymentMethodModal() {
    const modal = document.getElementById('paymentMethodModal');
    const form = document.getElementById('pmForm');
    document.getElementById('pmModalTitle').innerText = 'Add Payment Method';
    form.action = "{{ route('admin.payment-methods.store') }}";
    document.getElementById('pmMethodSpoof').innerHTML = '';
    document.getElementById('pmName').value = '';
    document.getElementById('pmCode').value = '';
    document.getElementById('pmAccountNumber').value = '';
    document.getElementById('pmMinDeposit').value = '50';
    document.getElementById('pmMaxDeposit').value = '25000';
    document.getElementById('pmRatePerBdt').value = '10';
    document.getElementById('pmInstructions').value = '';
    document.getElementById('pmIsActive').checked = true;
    modal.style.display = 'flex';
}

function openEditPaymentMethodModal(method) {
    const modal = document.getElementById('paymentMethodModal');
    const form = document.getElementById('pmForm');
    document.getElementById('pmModalTitle').innerText = 'Edit Payment Method: ' + method.name;
    form.action = `/admin/payment-methods/${method.id}`;
    document.getElementById('pmMethodSpoof').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('pmName').value = method.name || '';
    document.getElementById('pmCode').value = method.code || '';
    document.getElementById('pmAccountType').value = method.account_type || 'Personal';
    document.getElementById('pmAccountNumber').value = method.account_number || '';
    document.getElementById('pmMinDeposit').value = method.min_deposit || 50;
    document.getElementById('pmMaxDeposit').value = method.max_deposit || 25000;
    document.getElementById('pmRatePerBdt').value = method.rate_per_bdt || 10;
    document.getElementById('pmInstructions').value = method.instructions || '';
    document.getElementById('pmIsActive').checked = method.is_active;
    modal.style.display = 'flex';
}

function closePaymentMethodModal() {
    document.getElementById('paymentMethodModal').style.display = 'none';
}

window.addEventListener('click', function(e) {
    const modal = document.getElementById('paymentMethodModal');
    if (e.target === modal) {
        closePaymentMethodModal();
    }
});
</script>
@endpush
@endsection
