@extends('layouts.admin')

@section('title', 'My Bag Items & Backpack Management')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">My Bag</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-bag-shopping" style="color: #a855f7;"></i>
                <span>My Bag (আমার ব্যাগ) Items</span>
            </h1>
            <p class="page-subtitle">Manage all 6 My Bag categories: Coupons, Avatar Frames, Chat Styles, Profile Cards, Entrance Bubbles, and Big Entrances.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold" style="font-size: 13px;" onclick="openGiveItemModal()">
                <i class="fa-solid fa-gift me-1"></i> Give / Reward User
            </button>
            <a href="{{ route('admin.my-bag.inventory') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold" style="font-size: 13px;">
                <i class="fa-solid fa-boxes-stacked me-1"></i> User Inventories ({{ $totalUserItems }})
            </a>
            <button type="button" class="btn-ch-primary px-3 py-2" onclick="openCreateItemModal()">
                <i class="fa-solid fa-plus-circle me-1"></i> Add Bag Item
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #a855f7;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Total Bag Items</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ $totalItems }}</h3>
                        <small class="text-muted">{{ $activeItems }} Active in App</small>
                    </div>
                    <div class="stat-icon-box" style="background: rgba(168, 85, 247, 0.15); color: #a855f7;">
                        <i class="fa-solid fa-bag-shopping"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #f59e0b;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Sale & Coupons</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ $categoryCounts['coupon'] ?? 0 }}</h3>
                        <small class="text-warning fw-semibold">Discount & Vouchers</small>
                    </div>
                    <div class="stat-icon-box" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                        <i class="fa-solid fa-ticket"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #3b82f6;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Frames & Rides</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ ($categoryCounts['avatar_frame'] ?? 0) + ($categoryCounts['big_entrance'] ?? 0) }}</h3>
                        <small class="text-primary fw-semibold">Visual Outfits & Rides</small>
                    </div>
                    <div class="stat-icon-box" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                        <i class="fa-solid fa-car-side"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #10b981;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Active Equipped</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ $activeEquippedTotal }}</h3>
                        <small class="text-success fw-semibold"><i class="fa-solid fa-circle-check"></i> In-Use by Users</small>
                    </div>
                    <div class="stat-icon-box" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Filter Tabs (Matching Mobile UI 6 Tabs) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="{{ route('admin.my-bag.index', ['category' => 'all']) }}" 
                       class="btn btn-sm rounded-pill px-3 py-2 fw-bold {{ $category === 'all' ? 'btn-primary' : 'btn-light text-muted' }}">
                        <i class="fa-solid fa-border-all me-1"></i> All Items ({{ $totalItems }})
                    </a>
                    @foreach($categories as $catKey => $catLabel)
                        <a href="{{ route('admin.my-bag.index', ['category' => $catKey]) }}" 
                           class="btn btn-sm rounded-pill px-3 py-2 fw-bold {{ $category === $catKey ? 'btn-primary' : 'btn-light text-muted' }}">
                            <i class="{{ $categoryIcons[$catKey] ?? 'fa-solid fa-tag' }} me-1"></i> 
                            {{ $catLabel }} 
                            <span class="badge rounded-pill ms-1 {{ $category === $catKey ? 'bg-white text-primary' : 'bg-secondary-subtle text-muted' }}" style="font-size: 10px;">
                                {{ $categoryCounts[$catKey] ?? 0 }}
                            </span>
                        </a>
                    @endforeach
                </div>
                <!-- Search Box -->
                <form action="{{ route('admin.my-bag.index') }}" method="GET" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="category" value="{{ $category }}">
                    <div class="input-group input-group-sm" style="width: 240px;">
                        <input type="text" name="search" class="form-control rounded-start-pill" placeholder="Search item name..." value="{{ $search }}">
                        <button class="btn btn-primary rounded-end-pill px-3" type="submit">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                    @if(!empty($search))
                        <a href="{{ route('admin.my-bag.index', ['category' => $category]) }}" class="btn btn-sm btn-outline-secondary rounded-pill" title="Clear">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Items Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted fw-bold" style="font-size: 12px; text-transform: uppercase;">
                    <tr>
                        <th class="ps-4">Item & Preview</th>
                        <th>Category</th>
                        <th>Price (Gems / BDT)</th>
                        <th>Duration</th>
                        <th>Badge & Value</th>
                        <th>Giftable</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="position-relative" style="width: 58px; height: 58px; border-radius: 12px; background: #181428; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid rgba(255,255,255,0.1);">
                                        @if($item->icon_full_url)
                                            <img src="{{ $item->icon_full_url }}" alt="{{ $item->name }}" style="max-width: 90%; max-height: 90%; object-fit: contain;">
                                        @else
                                            <i class="{{ $categoryIcons[$item->category] ?? 'fa-solid fa-tag' }} text-white-50 fa-lg"></i>
                                        @endif
                                        @if($item->badge)
                                            <span class="position-absolute top-0 end-0 badge bg-danger" style="font-size: 8px; transform: scale(0.85); transform-origin: top right;">{{ $item->badge }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $item->name }}</div>
                                        <small class="text-muted font-monospace" style="font-size: 11px;">#{{ $item->code }}</small>
                                        @if($item->description)
                                            <div class="text-muted text-truncate" style="max-width: 220px; font-size: 11px;">{{ $item->description }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 11px;">
                                    <i class="{{ $categoryIcons[$item->category] ?? 'fa-solid fa-tag' }} text-primary me-1"></i>
                                    {{ $item->category_name }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-pink" style="color: #ec4899;">
                                    @if($item->price_coins > 0)
                                        <i class="fa-solid fa-gem me-1"></i> {{ number_format($item->price_coins) }} Gems
                                    @else
                                        <span class="text-success">Free</span>
                                    @endif
                                </div>
                                @if($item->price_bdt > 0)
                                    <small class="text-muted">৳{{ number_format($item->price_bdt, 2) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1" style="font-size: 11px;">
                                    <i class="fa-regular fa-clock me-1"></i> {{ $item->duration_text }}
                                </span>
                            </td>
                            <td>
                                @if($item->category === 'coupon' && $item->discount_percent)
                                    <span class="badge bg-warning-subtle text-warning fw-bold px-2 py-1" style="font-size: 11px;">
                                        {{ $item->discount_percent }}% OFF
                                    </span>
                                @elseif($item->badge)
                                    <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1" style="font-size: 11px;">
                                        {{ $item->badge }}
                                    </span>
                                @else
                                    <span class="text-muted" style="font-size: 12px;">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->is_giftable)
                                    <span class="text-success" style="font-size: 12px;"><i class="fa-solid fa-check me-1"></i> Yes</span>
                                @else
                                    <span class="text-muted" style="font-size: 12px;"><i class="fa-solid fa-xmark me-1"></i> No</span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.my-bag.toggle-status', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm rounded-pill px-3 py-1 fw-bold {{ $item->is_active ? 'btn-success-subtle text-success' : 'btn-danger-subtle text-danger' }}" style="font-size: 11px; border: none;">
                                        <i class="fa-solid fa-circle {{ $item->is_active ? 'text-success' : 'text-danger' }} me-1" style="font-size: 8px;"></i>
                                        {{ $item->is_active ? 'Active' : 'Disabled' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 32px; height: 32px; padding: 0;" title="Edit" onclick="openEditItemModal({{ json_encode($item) }})">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success rounded-circle" style="width: 32px; height: 32px; padding: 0;" title="Gift to User" onclick="prefillGiveModal({{ $item->id }}, '{{ addslashes($item->name) }}')">
                                        <i class="fa-solid fa-paper-plane"></i>
                                    </button>
                                    <form action="{{ route('admin.my-bag.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this bag item?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" style="width: 32px; height: 32px; padding: 0;" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-bag-shopping fa-3x text-muted mb-3 opacity-50"></i>
                                <p class="mb-2">No My Bag items found for category '{{ $category }}'.</p>
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="openCreateItemModal()">
                                    <i class="fa-solid fa-plus me-1"></i> Add First Item
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ========================================== -->
<!-- ➕ MODAL: Add New Bag Item -->
<!-- ========================================== -->
<div class="modal fade" id="createItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header bg-gradient text-white p-4" style="background: linear-gradient(135deg, #a855f7, #6366f1);">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-plus-circle me-2"></i> Add New My Bag Item
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.my-bag.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Category Selector (6 Tabs) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Category *</label>
                            <select name="category" class="form-select rounded-3 fw-semibold" required onchange="toggleCategoryFields(this.value)">
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}" {{ $category === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Item Name *</label>
                            <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. 50% Off Recharge Coupon" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold text-muted small text-uppercase">Unique Code / Slug</label>
                            <input type="text" name="code" class="form-control rounded-3" placeholder="e.g. coupon_sale_50">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold text-muted small text-uppercase">Price in Gems (Coins)</label>
                            <input type="number" name="price_coins" class="form-control rounded-3" placeholder="0" min="0" value="0">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold text-muted small text-uppercase">Validity Duration (Days)</label>
                            <input type="number" name="duration_days" class="form-control rounded-3" placeholder="7" min="0" value="7">
                            <small class="text-muted" style="font-size: 11px;">0 = Permanent / Single Use</small>
                        </div>

                        <!-- Coupon Specific Fields -->
                        <div class="col-12 col-md-6 coupon-field">
                            <label class="form-label fw-bold text-muted small text-uppercase">Discount Percentage (%)</label>
                            <input type="number" name="discount_percent" class="form-control rounded-3" placeholder="e.g. 50" min="1" max="100">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Badge Text</label>
                            <input type="text" name="badge" class="form-control rounded-3" placeholder="e.g. SALE 50%, VIP, HOT">
                        </div>

                        <!-- Upload SVG / Image -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Upload SVG / PNG Image Asset</label>
                            <input type="file" name="icon_file" class="form-control rounded-3" accept=".svg,.png,.jpg,.jpeg,.webp,.gif">
                            <small class="text-muted" style="font-size: 11px;">Saved to <code>public/uploads/my_bag/</code></small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Or Image Asset URL / Path</label>
                            <input type="text" name="icon_url" class="form-control rounded-3" placeholder="uploads/my_bag/coupon_sale_yellow.svg">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Format</label>
                            <select name="format" class="form-select rounded-3">
                                <option value="svg" selected>SVG Vector</option>
                                <option value="svga">SVGA Animation</option>
                                <option value="lottie">Lottie JSON</option>
                                <option value="webp">WebP Image</option>
                                <option value="png">PNG Image</option>
                                <option value="mp4">MP4 Video Ride</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control rounded-3" placeholder="0" value="0">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-muted small text-uppercase">Description</label>
                            <textarea name="description" class="form-control rounded-3" rows="2" placeholder="Brief description of perks and effect..."></textarea>
                        </div>

                        <div class="col-12 d-flex align-items-center gap-4 pt-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="createIsActive" checked>
                                <label class="form-check-label fw-semibold" for="createIsActive">Active in Bag / Store</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_giftable" value="1" id="createIsGiftable" checked>
                                <label class="form-check-label fw-semibold" for="createIsGiftable">Can be Gifted to Friends</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Create Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- ✏️ MODAL: Edit Bag Item -->
<!-- ========================================== -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header bg-gradient text-white p-4" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-pen-to-square me-2"></i> Edit Bag Item
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editItemForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Category *</label>
                            <select name="category" id="editCategory" class="form-select rounded-3 fw-semibold" required>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Item Name *</label>
                            <input type="text" name="name" id="editName" class="form-control rounded-3" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold text-muted small text-uppercase">Price in Gems (Coins)</label>
                            <input type="number" name="price_coins" id="editPriceCoins" class="form-control rounded-3" min="0">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold text-muted small text-uppercase">Validity Duration (Days)</label>
                            <input type="number" name="duration_days" id="editDurationDays" class="form-control rounded-3" min="0">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold text-muted small text-uppercase">Discount (%)</label>
                            <input type="number" name="discount_percent" id="editDiscountPercent" class="form-control rounded-3" min="0" max="100">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Badge Text</label>
                            <input type="text" name="badge" id="editBadge" class="form-control rounded-3">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Sort Order</label>
                            <input type="number" name="sort_order" id="editSortOrder" class="form-control rounded-3">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Replace SVG / Image File</label>
                            <input type="file" name="icon_file" class="form-control rounded-3" accept=".svg,.png,.jpg,.jpeg,.webp,.gif">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Or Image Asset URL</label>
                            <input type="text" name="icon_url" id="editIconUrl" class="form-control rounded-3">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-muted small text-uppercase">Description</label>
                            <textarea name="description" id="editDescription" class="form-control rounded-3" rows="2"></textarea>
                        </div>

                        <div class="col-12 d-flex align-items-center gap-4 pt-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editIsActive">
                                <label class="form-check-label fw-semibold" for="editIsActive">Active in Bag / Store</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_giftable" value="1" id="editIsGiftable">
                                <label class="form-check-label fw-semibold" for="editIsGiftable">Can be Gifted</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 🎁 MODAL: Give / Reward Item to User -->
<!-- ========================================== -->
<div class="modal fade" id="giveItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header bg-gradient text-white p-4" style="background: linear-gradient(135deg, #10b981, #059669);">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-gift me-2"></i> Direct Grant Item to User Bag
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.my-bag.give-user') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Select Bag Item *</label>
                        <select name="bag_item_id" id="giveItemId" class="form-select rounded-3 fw-semibold" required>
                            @foreach($items as $i)
                                <option value="{{ $i->id }}">[{{ $i->category_name }}] {{ $i->name }} ({{ $i->duration_text }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Target User (ID / Account ID / Phone / Email) *</label>
                        <input type="text" name="user_identity" class="form-control rounded-3" placeholder="e.g. 84920183 or User ID" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Quantity</label>
                            <input type="number" name="quantity" class="form-control rounded-3" value="1" min="1">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Custom Days (0 = default)</label>
                            <input type="number" name="duration_days" class="form-control rounded-3" value="0" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">Gift to User</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openCreateItemModal() {
        const modal = new bootstrap.Modal(document.getElementById('createItemModal'));
        modal.show();
    }

    function openEditItemModal(item) {
        const form = document.getElementById('editItemForm');
        form.action = `/admin/my-bag/${item.id}`;

        document.getElementById('editCategory').value = item.category;
        document.getElementById('editName').value = item.name;
        document.getElementById('editPriceCoins').value = item.price_coins || 0;
        document.getElementById('editDurationDays').value = item.duration_days || 7;
        document.getElementById('editDiscountPercent').value = item.discount_percent || '';
        document.getElementById('editBadge').value = item.badge || '';
        document.getElementById('editSortOrder').value = item.sort_order || 0;
        document.getElementById('editIconUrl').value = item.icon_url || '';
        document.getElementById('editDescription').value = item.description || '';

        document.getElementById('editIsActive').checked = Boolean(item.is_active);
        document.getElementById('editIsGiftable').checked = Boolean(item.is_giftable);

        const modal = new bootstrap.Modal(document.getElementById('editItemModal'));
        modal.show();
    }

    function openGiveItemModal() {
        const modal = new bootstrap.Modal(document.getElementById('giveItemModal'));
        modal.show();
    }

    function prefillGiveModal(itemId, itemName) {
        const select = document.getElementById('giveItemId');
        if (select) select.value = itemId;
        openGiveItemModal();
    }
</script>
@endpush
