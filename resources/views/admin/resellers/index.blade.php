@extends('layouts.admin')

@section('title', 'Resellers Management')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Resellers</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-store text-warning"></i>
                <span>Resellers & Coin Agents</span>
            </h1>
            <p class="page-subtitle">Manage authorized coin resellers, adjust balances, monitor sales, and configure discount badges.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.resellers.transfers') }}" class="btn btn-outline-secondary rounded-3 px-3">
                <i class="fa-solid fa-clock-rotate-left"></i> Transfer Ledger
            </a>
            <button type="button" class="btn-ch-primary" onclick="openCreateResellerModal()">
                <i class="fa-solid fa-plus-circle"></i> Add New Reseller
            </button>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-blue">
                <div class="stat-icon-box stat-icon-blue">
                    <i class="fa-solid fa-store"></i>
                </div>
                <div>
                    <div class="stat-title">Total Resellers</div>
                    <div class="stat-value">{{ number_format($stats['total_resellers']) }}</div>
                    <small class="text-muted">{{ $stats['active_resellers'] }} Active · {{ $stats['online_resellers'] }} Online</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-purple">
                <div class="stat-icon-box stat-icon-purple">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div>
                    <div class="stat-title">Reseller Stock Coins</div>
                    <div class="stat-value">{{ number_format($stats['total_coins_held']) }}</div>
                    <small class="text-muted">Currently held in reseller wallets</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-green">
                <div class="stat-icon-box stat-icon-green">
                    <i class="fa-solid fa-circle-arrow-up"></i>
                </div>
                <div>
                    <div class="stat-title">Total Coins Sold</div>
                    <div class="stat-value">{{ number_format($stats['total_coins_sold']) }}</div>
                    <small class="text-muted">Recharged to users</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-yellow">
                <div class="stat-icon-box stat-icon-yellow">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <div class="stat-title">Pending Requests</div>
                    <div class="stat-value">{{ $stats['pending_deposits'] + $stats['pending_withdrawals'] }}</div>
                    <small class="text-muted">{{ $stats['pending_deposits'] }} Deposits · {{ $stats['pending_withdrawals'] }} Withdrawals</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: var(--card-bg-light); border: 1px solid var(--border-color) !important;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.resellers.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by name, email, phone, location..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Disabled Only</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <button type="submit" class="btn btn-primary w-100 rounded-3">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                </div>
                <div class="col-12 col-md-2 text-end">
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.resellers.index') }}" class="btn btn-light border w-100 rounded-3 text-muted">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Resellers Grid -->
    <div class="row g-4 mb-4">
        @forelse($resellers as $reseller)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 rounded-4 shadow-sm h-100 position-relative overflow-hidden" style="background: var(--card-bg-light); border: 1px solid var(--border-color) !important;">
                    <!-- Top Ribbon / Discount Tag -->
                    @if($reseller->discount_tag)
                        <div class="position-absolute top-0 end-0 px-3 py-1 text-white fw-bold shadow-sm" style="background: linear-gradient(135deg, #f59e0b, #ef4444); font-size: 11px; border-bottom-left-radius: 12px;">
                            {{ $reseller->discount_tag }}
                        </div>
                    @endif

                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <!-- Header: Avatar + Name + Level -->
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="position-relative">
                                    <img src="{{ $reseller->avatar_url }}" alt="{{ $reseller->name }}" class="rounded-circle border" style="width: 58px; height: 58px; object-fit: cover; border-width: 2px !important; border-color: #f59e0b !important;">
                                    <span class="position-absolute bottom-0 end-0 p-1 border border-white rounded-circle {{ $reseller->is_online ? 'bg-success' : 'bg-secondary' }}" style="width: 14px; height: 14px;" title="{{ $reseller->is_online ? 'Online' : 'Offline' }}"></span>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1" style="color: var(--text-primary); font-size: 16px;">{{ $reseller->name }}</h5>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge bg-primary text-white rounded-pill px-2" style="font-size: 10px;">{{ $reseller->level }}</span>
                                        <span class="badge bg-light text-dark border rounded-pill px-2" style="font-size: 10px;">
                                            <i class="fa-solid fa-location-dot text-danger me-1"></i> {{ $reseller->location }}
                                        </span>
                                        @if($reseller->gender)
                                            <span class="badge bg-light text-muted border rounded-pill px-2" style="font-size: 10px;">
                                                <i class="fa-solid fa-{{ $reseller->gender == 'female' ? 'venus text-pink' : 'mars text-primary' }}"></i> {{ $reseller->age }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Bio Box -->
                            @if($reseller->bio)
                                <div class="p-2 px-3 rounded-3 mb-3" style="background: rgba(245,158,11,0.06); border: 1px dashed rgba(245,158,11,0.3); font-size: 12px; color: var(--text-secondary); max-height: 80px; overflow-y: auto;">
                                    {!! nl2br(e($reseller->bio)) !!}
                                </div>
                            @endif

                            <!-- Contact & Login Details -->
                            <div class="d-flex flex-column gap-1 mb-3 pb-3 border-bottom" style="font-size: 12px;">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted"><i class="fa-regular fa-envelope me-1"></i> Email:</span>
                                    <strong class="text-dark">{{ $reseller->email }}</strong>
                                </div>
                                @if($reseller->phone)
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted"><i class="fa-solid fa-phone me-1"></i> Phone:</span>
                                        <strong class="text-dark">{{ $reseller->phone }}</strong>
                                    </div>
                                @endif
                            </div>

                            <!-- Wallet Stats Box -->
                            <div class="p-3 rounded-3 mb-3" style="background: var(--bg-main); border: 1px solid var(--border-color);">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted fw-semibold" style="font-size: 12px;">Coin Balance:</span>
                                    <span class="fw-bolder fs-5 text-warning">
                                        <i class="fa-solid fa-coins me-1"></i> {{ number_format($reseller->coins_balance) }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top" style="font-size: 11px; border-color: var(--border-color) !important;">
                                    <span class="text-muted">Total Sold: <strong>{{ number_format($reseller->total_sold_coins) }}</strong></span>
                                    <span class="text-muted">Transfers: <strong>{{ $reseller->transfers_count }}</strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer Controls -->
                        <div>
                            <div class="d-flex justify-content-between align-items-center gap-2 pt-2">
                                <!-- Status Pill Toggles -->
                                <div class="d-flex gap-1">
                                    <form action="{{ route('admin.resellers.toggle-status', $reseller->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $reseller->is_active ? 'btn-success' : 'btn-outline-secondary' }} rounded-pill px-2 py-0" style="font-size: 11px;" title="Toggle Active">
                                            {{ $reseller->is_active ? 'Active' : 'Disabled' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.resellers.toggle-online', $reseller->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $reseller->is_online ? 'btn-outline-success' : 'btn-outline-muted' }} rounded-pill px-2 py-0" style="font-size: 11px;" title="Toggle Online">
                                            {{ $reseller->is_online ? 'Online' : 'Offline' }}
                                        </button>
                                    </form>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-warning rounded-3" title="Adjust Coins" onclick='openAdjustCoinsModal(@json($reseller))'>
                                        <i class="fa-solid fa-coins"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-3" title="Edit Reseller" onclick='openEditResellerModal(@json($reseller))'>
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form action="{{ route('admin.resellers.destroy', $reseller->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this reseller? All transfer history will remain.');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Delete Reseller">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="card p-5 border-0 rounded-4 shadow-sm text-center" style="background: var(--card-bg-light); border: 1px dashed var(--border-color) !important;">
                    <i class="fa-solid fa-store fa-4x text-muted mb-3" style="opacity: 0.3;"></i>
                    <h4 class="fw-bold">No Resellers Found</h4>
                    <p class="text-muted mb-4">Create your first coin reseller so users can purchase coins directly via chat with discounts.</p>
                    <button type="button" class="btn-ch-primary mx-auto" onclick="openCreateResellerModal()">
                        <i class="fa-solid fa-plus-circle"></i> Add First Reseller
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($resellers->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $resellers->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<!-- Modal: Create / Edit Reseller -->
<div class="modal fade" id="resellerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-modern-dialog">
        <div class="modal-content modal-modern-content">
            <div class="modal-modern-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-box stat-icon-yellow" style="width: 44px; height: 44px; font-size: 18px;">
                        <i class="fa-solid fa-store"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="resellerModalTitle">Add New Reseller</h5>
                        <small class="text-muted">Configure reseller credentials and live profile details</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="resellerForm" method="POST" action="{{ route('admin.resellers.store') }}" enctype="multipart/form-data">
                @csrf
                <div id="resellerMethodSpoof"></div>
                <div class="modal-modern-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Reseller Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="resellerName" class="form-control rounded-3" placeholder="e.g. MURAD COINS RESELLER" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Email Address (For Portal Login) <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="resellerEmail" class="form-control rounded-3" placeholder="e.g. murad.reseller@chinchins.live" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Password <span id="pwdReqStar" class="text-danger">*</span></label>
                            <input type="password" name="password" id="resellerPassword" class="form-control rounded-3" placeholder="Enter secure password">
                            <small class="text-muted" id="pwdHelpText" style="font-size: 11px;">Min 6 characters</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Confirm Password <span id="pwdConfReqStar" class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" id="resellerPasswordConfirm" class="form-control rounded-3" placeholder="Repeat password">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold" style="font-size: 13px;">Phone / WhatsApp Number</label>
                            <input type="text" name="phone" id="resellerPhone" class="form-control rounded-3" placeholder="e.g. 01848340232">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold" style="font-size: 13px;">Level Badge</label>
                            <input type="text" name="level" id="resellerLevel" class="form-control rounded-3" value="Lv5" placeholder="e.g. Lv5">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold" style="font-size: 13px;">Location / City</label>
                            <input type="text" name="location" id="resellerLocation" class="form-control rounded-3" value="Dhaka, Bangladesh" placeholder="e.g. Dhaka">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Discount Tag / Badge (Shown in App)</label>
                            <input type="text" name="discount_tag" id="resellerDiscountTag" class="form-control rounded-3" value="Up To 29%↑" placeholder="e.g. Up To 29%↑">
                        </div>
                        <div class="col-12 col-md-6" id="initialCoinsWrap">
                            <label class="form-label fw-bold" style="font-size: 13px;">Initial Coins Deposit</label>
                            <input type="number" name="initial_coins" id="resellerInitialCoins" class="form-control rounded-3" value="10000" min="0">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold" style="font-size: 13px;">Bio / Notice Text (Displayed in Reseller Card)</label>
                            <textarea name="bio" id="resellerBio" class="form-control rounded-3" rows="3" placeholder="কয়েন রিচার্জ, হোস্টিং এবং বিভিন্ন ধরণের গিফট ক্রয় করা হয়..."></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold" style="font-size: 13px;">Reseller Profile Avatar</label>
                            <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: var(--bg-main); border: 1px dashed var(--border-color);">
                                <div class="position-relative" style="width: 60px; height: 60px; border-radius: 50%; background: #fff; border: 2px solid var(--border-color); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                                    <img id="resellerAvatarPreview" src="" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                                    <i id="resellerAvatarPlaceholder" class="fa-solid fa-user text-muted fa-2x" style="opacity: 0.4;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="avatar" id="resellerAvatarInput" class="form-control rounded-3" accept="image/*" onchange="previewResellerAvatar(event)">
                                    <small class="text-muted mt-1 d-block" style="font-size: 11px;">Select JPG, PNG, WEBP. Saved in <code>uploads/reseller/</code></small>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch p-0 d-flex align-items-center gap-3">
                                <input class="form-check-input ms-0" type="checkbox" name="is_active" id="resellerIsActive" value="1" checked style="width: 44px; height: 22px; cursor: pointer;">
                                <label class="form-check-label fw-bold" for="resellerIsActive" style="cursor: pointer; font-size: 13px;">
                                    Active (Visible in Mobile App)
                                </label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch p-0 d-flex align-items-center gap-3">
                                <input class="form-check-input ms-0" type="checkbox" name="is_online" id="resellerIsOnline" value="1" checked style="width: 44px; height: 22px; cursor: pointer;">
                                <label class="form-check-label fw-bold" for="resellerIsOnline" style="cursor: pointer; font-size: 13px;">
                                    Show Online Status
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-modern-footer">
                    <button type="button" class="btn btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-ch-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Save Reseller
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Adjust Reseller Coins Balance -->
<div class="modal fade" id="adjustCoinsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-modern-dialog" style="max-width: 460px;">
        <div class="modal-content modal-modern-content">
            <div class="modal-modern-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-box stat-icon-yellow" style="width: 40px; height: 40px; font-size: 16px;">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Adjust Reseller Coins</h5>
                        <small class="text-muted" id="adjResellerName">Reseller Balance</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="adjCoinsForm" method="POST" action="">
                @csrf
                <div class="modal-modern-body">
                    <div class="p-3 rounded-3 mb-3 text-center" style="background: rgba(245,158,11,0.08); border: 1px dashed rgba(245,158,11,0.3);">
                        <small class="text-muted d-block">Current Stock Coins</small>
                        <span class="fs-4 fw-bolder text-warning" id="adjCurrentCoins">0</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Adjustment Action <span class="text-danger">*</span></label>
                        <select name="action" class="form-select rounded-3" required>
                            <option value="add">➕ Add Coins (Increase Stock)</option>
                            <option value="subtract">➖ Subtract Coins (Deduct Stock)</option>
                            <option value="set">🔄 Set Exact Coin Balance</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Coin Amount <span class="text-danger">*</span></label>
                        <input type="number" name="coins" class="form-control rounded-3 font-monospace fs-5" placeholder="e.g. 50000" min="1" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold" style="font-size: 13px;">Admin Note (Optional)</label>
                        <input type="text" name="notes" class="form-control rounded-3" placeholder="e.g. Refill after bKash payment">
                    </div>
                </div>
                <div class="modal-modern-footer">
                    <button type="button" class="btn btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-ch-primary">
                        <i class="fa-solid fa-check-circle"></i> Apply Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewResellerAvatar(event) {
    const input = event.target;
    const preview = document.getElementById('resellerAvatarPreview');
    const placeholder = document.getElementById('resellerAvatarPlaceholder');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function openCreateResellerModal() {
    const modalEl = document.getElementById('resellerModal');
    const form = document.getElementById('resellerForm');
    document.getElementById('resellerModalTitle').innerText = 'Add New Reseller';
    form.action = "{{ route('admin.resellers.store') }}";
    document.getElementById('resellerMethodSpoof').innerHTML = '';

    document.getElementById('resellerName').value = '';
    document.getElementById('resellerEmail').value = '';
    document.getElementById('resellerPassword').value = '';
    document.getElementById('resellerPassword').required = true;
    document.getElementById('resellerPasswordConfirm').value = '';
    document.getElementById('resellerPasswordConfirm').required = true;
    document.getElementById('pwdReqStar').style.display = 'inline';
    document.getElementById('pwdConfReqStar').style.display = 'inline';
    document.getElementById('pwdHelpText').innerText = 'Min 6 characters';

    document.getElementById('resellerPhone').value = '';
    document.getElementById('resellerLevel').value = 'Lv5';
    document.getElementById('resellerLocation').value = 'Dhaka, Bangladesh';
    document.getElementById('resellerDiscountTag').value = 'Up To 29%↑';
    document.getElementById('resellerBio').value = "কয়েন রিচার্জ, হোস্টিং এবং বিভিন্ন ধরণের গিফট ক্রয় করা হয়\nযোগাযোগ ০১848340232\nহোস্টিং সেলারি তুলনামূলক বেশি দেওয়া হয়";
    document.getElementById('initialCoinsWrap').style.display = 'block';
    document.getElementById('resellerInitialCoins').value = '10000';
    document.getElementById('resellerIsActive').checked = true;
    document.getElementById('resellerIsOnline').checked = true;

    document.getElementById('resellerAvatarPreview').style.display = 'none';
    document.getElementById('resellerAvatarPlaceholder').style.display = 'block';
    document.getElementById('resellerAvatarInput').value = '';

    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
}

function openEditResellerModal(reseller) {
    const modalEl = document.getElementById('resellerModal');
    const form = document.getElementById('resellerForm');
    document.getElementById('resellerModalTitle').innerText = 'Edit Reseller: ' + reseller.name;
    form.action = `/admin/resellers/${reseller.id}`;
    document.getElementById('resellerMethodSpoof').innerHTML = '<input type="hidden" name="_method" value="PUT">';

    document.getElementById('resellerName').value = reseller.name || '';
    document.getElementById('resellerEmail').value = reseller.email || '';
    document.getElementById('resellerPassword').value = '';
    document.getElementById('resellerPassword').required = false;
    document.getElementById('resellerPasswordConfirm').value = '';
    document.getElementById('resellerPasswordConfirm').required = false;
    document.getElementById('pwdReqStar').style.display = 'none';
    document.getElementById('pwdConfReqStar').style.display = 'none';
    document.getElementById('pwdHelpText').innerText = 'Leave blank to keep existing password';

    document.getElementById('resellerPhone').value = reseller.phone || '';
    document.getElementById('resellerLevel').value = reseller.level || 'Lv1';
    document.getElementById('resellerLocation').value = reseller.location || '';
    document.getElementById('resellerDiscountTag').value = reseller.discount_tag || '';
    document.getElementById('resellerBio').value = reseller.bio || '';
    document.getElementById('initialCoinsWrap').style.display = 'none';
    document.getElementById('resellerIsActive').checked = Boolean(reseller.is_active);
    document.getElementById('resellerIsOnline').checked = Boolean(reseller.is_online);

    const preview = document.getElementById('resellerAvatarPreview');
    const placeholder = document.getElementById('resellerAvatarPlaceholder');
    document.getElementById('resellerAvatarInput').value = '';

    if (reseller.avatar_url) {
        preview.src = reseller.avatar_url;
        preview.style.display = 'block';
        placeholder.style.display = 'none';
    } else {
        preview.style.display = 'none';
        placeholder.style.display = 'block';
    }

    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
}

function openAdjustCoinsModal(reseller) {
    const modalEl = document.getElementById('adjustCoinsModal');
    const form = document.getElementById('adjCoinsForm');
    form.action = `/admin/resellers/${reseller.id}/adjust-coins`;
    document.getElementById('adjResellerName').innerText = reseller.name;
    document.getElementById('adjCurrentCoins').innerText = Number(reseller.coins_balance).toLocaleString() + ' Coins';

    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
}
</script>
@endpush
@endsection
