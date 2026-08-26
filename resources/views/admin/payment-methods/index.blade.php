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

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-3 p-3 mb-4 rounded-4 shadow-sm border-0" style="background: rgba(16, 185, 129, 0.12); color: #047857;">
            <i class="fa-solid fa-circle-check fa-lg"></i>
            <div class="fw-semibold">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-3 p-3 mb-4 rounded-4 shadow-sm border-0" style="background: rgba(239, 68, 68, 0.12); color: #b91c1c;">
            <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
            <div class="fw-semibold">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

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
                                    <img src="{{ $method->icon_url }}" alt="{{ $method->name }}" onerror="handlePmImageError(this, '{{ addslashes($method->name) }}')">
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
                                <span class="text-muted">Rate Multiplier:</span>
                                <div class="coin-badge-3d" style="padding: 2px 8px;">
                                    <i class="fa-solid fa-coins coin-icon-glow" style="font-size: 11px;"></i>
                                    <span class="coin-amount-text" style="font-size: 12px;">1 BDT = {{ $method->rate_per_bdt }} Coins</span>
                                </div>
                            </div>
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
    <div class="modal-dialog modal-dialog-centered modal-modern-dialog" style="max-width: 600px;">
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
                        <div class="col-12">
                            <label class="form-label fw-bold" style="font-size: 13px;">Exchange Rate (Coins per 1 BDT) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">1 BDT =</span>
                                <input type="number" name="rate_per_bdt" id="pmRatePerBdt" class="form-control fw-bold" value="10" min="1" required>
                                <span class="input-group-text bg-light text-warning fw-bold"><i class="fa-solid fa-coins me-1"></i> Coins</span>
                            </div>
                            <small class="text-muted mt-1 d-block">Example: 10 means 100 BDT = 1,000 Coins credited to user.</small>
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
    document.getElementById('pmRatePerBdt').value = '10';
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
    document.getElementById('pmRatePerBdt').value = method.rate_per_bdt || 10;
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
    } else {
        clearGatewayIconPreview();
    }

    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
}

function copyToClipboard(text, msg) {
    navigator.clipboard.writeText(text).then(() => {
        showToast(msg || 'Copied to clipboard!');
    });
}

function showToast(message) {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = 'ch-toast';
    toast.innerHTML = `<i class="fa-solid fa-circle-check text-success"></i> <span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        setTimeout(() => toast.remove(), 300);
    }, 2500);
}
</script>
@endpush
@endsection
