@extends('layouts.admin')

@section('title', 'Gifts & In-App Rewards Management')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Gifts & Rewards</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-gift text-pink" style="color: #f43f5e;"></i>
                <span>Gifts & In-App Rewards</span>
            </h1>
            <p class="page-subtitle">Manage animated gifts, diamond/coin prices (e.g. 17.70K, 9.99K, 5.55K), icons, badges, categories, and review users' gifts received ledger.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold" style="font-size: 13px;" onclick="openGiveGiftModal()">
                <i class="fa-solid fa-paper-plane me-1"></i> Direct Give / Reward User
            </button>
            <a href="{{ route('admin.gifts.logs') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold" style="font-size: 13px;">
                <i class="fa-solid fa-list-check me-1"></i> Received Logs
            </a>
            <button type="button" class="btn-ch-primary px-3 py-2" onclick="openCreateGiftModal()">
                <i class="fa-solid fa-plus-circle me-1"></i> Add New Gift
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #f43f5e;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Total System Gifts</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ $totalGiftsCount }}</h3>
                        <small class="text-muted">{{ $activeGiftsCount }} Active in App</small>
                    </div>
                    <div class="stat-icon-box" style="background: rgba(244, 63, 94, 0.15); color: #f43f5e;">
                        <i class="fa-solid fa-gift"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #10b981;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Total Sent Quantity</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ number_format($totalSentCount) }}</h3>
                        <small class="text-success fw-semibold"><i class="fa-solid fa-arrow-up"></i> Across all hosts</small>
                    </div>
                    <div class="stat-icon-box" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #f59e0b;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Total Gift Coin Volume</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ \App\Models\Gift::formatCoins($totalSentCoins) }}</h3>
                        <small class="text-muted">{{ number_format($totalSentCoins) }} coins circulating</small>
                    </div>
                    <div class="stat-icon-box" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="premium-stat-card" style="border-left: 4px solid #8b5cf6;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Highest Tier Gift</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ \App\Models\Gift::formatCoins(\App\Models\Gift::max('coins') ?: 0) }}</h3>
                        <small class="text-muted">Top Luxury Item</small>
                    </div>
                    <div class="stat-icon-box" style="background: rgba(139, 92, 246, 0.15); color: #8b5cf6;">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: var(--card-bg, #ffffff);">
        <div class="card-body p-3">
            <form action="{{ route('admin.gifts.index') }}" method="GET" class="row g-2 align-items-center">
                <!-- Category Tabs -->
                <div class="col-12 col-md-auto d-flex gap-1 flex-wrap">
                    <a href="{{ route('admin.gifts.index') }}" class="btn btn-sm rounded-pill px-3 {{ !request('category') || request('category') == 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        All ({{ $totalGiftsCount }})
                    </a>
                    @foreach($categories as $cat)
                        <a href="{{ route('admin.gifts.index', array_merge(request()->query(), ['category' => $cat])) }}" class="btn btn-sm rounded-pill px-3 {{ request('category') == $cat ? 'btn-primary' : 'btn-outline-secondary' }}" style="text-transform: capitalize;">
                            {{ $cat }}
                        </a>
                    @endforeach
                </div>

                <!-- Search Input -->
                <div class="col-12 col-sm-6 col-md ms-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-transparent border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search gift name, coins (e.g. 17700), badge..." value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="col-6 col-sm-3 col-md-auto">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="col-6 col-sm-3 col-md-auto d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">Filter</button>
                    @if(request()->hasAny(['search', 'category', 'status']))
                        <a href="{{ route('admin.gifts.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2" title="Reset Filters"><i class="fa-solid fa-xmark"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Live Mobile Preview Grid (Matching Mobile Screen 1 & 2 Design) -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0" style="font-size: 16px;">
            <i class="fa-solid fa-mobile-screen text-pink me-2" style="color: #f43f5e;"></i> Gifts Catalog (Mobile App Live Items)
        </h5>
        <span class="text-muted" style="font-size: 13px;">Showing {{ $gifts->count() }} of {{ $gifts->total() }} gifts</span>
    </div>

    <div class="row g-3 mb-4">
        @forelse($gifts as $gift)
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <div class="card h-100 border-0 shadow-sm rounded-4 position-relative text-center p-2 transition-all gift-admin-card {{ !$gift->is_active ? 'opacity-50' : '' }}" style="background: var(--card-bg, #ffffff); border: 1px solid rgba(244, 63, 94, 0.12) !important;">
                    <!-- Badge Top Corner -->
                    <div class="position-absolute top-0 start-0 m-2 z-1">
                        @if($gift->badge)
                            <span class="badge rounded-pill" style="background: linear-gradient(135deg, #f43f5e, #ec4899); font-size: 9px; padding: 2px 6px;">{{ $gift->badge }}</span>
                        @endif
                    </div>

                    <div class="position-absolute top-0 end-0 m-2 z-1">
                        <form action="{{ route('admin.gifts.toggle-status', $gift->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm p-0 border-0" title="Toggle active status">
                                <i class="fa-solid {{ $gift->is_active ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }}" style="font-size: 20px;"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Gift Icon Preview with dynamic glow -->
                    <div class="my-2 d-flex justify-content-center align-items-center" style="min-height: 80px;">
                        <img src="{{ $gift->image_url }}" alt="{{ $gift->name }}" class="img-fluid rounded-3" style="max-height: 72px; width: 72px; object-fit: contain; filter: drop-shadow(0 4px 10px rgba(244, 63, 94, 0.25));" onerror="this.src='{{ asset('assets/images/gifts/gift-box-default.png') }}'">
                    </div>

                    <!-- Gift Name -->
                    <div class="fw-bold text-truncate mb-1" style="font-size: 12px;" title="{{ $gift->name }}">{{ $gift->name }}</div>

                    <!-- Diamond / Coin Price Pill (Dynamic Formatting like 💎 17.70K) -->
                    <div class="mb-2">
                        <span class="badge rounded-pill text-white px-2 py-1" style="background: linear-gradient(135deg, #a855f7, #6366f1); font-size: 11px; font-family: 'Fira Code', monospace; letter-spacing: 0.3px;">
                            💎 {{ $gift->formatted_coins }}
                        </span>
                        <div class="text-muted mt-1" style="font-size: 10px;">({{ number_format($gift->coins) }} coins)</div>
                    </div>

                    <!-- Actions Footer -->
                    <div class="mt-auto pt-2 border-top d-flex justify-content-center gap-1">
                        <button type="button" class="btn btn-xs btn-outline-primary rounded-circle" style="width: 28px; height: 28px; padding: 0;" onclick="openEditGiftModal({{ json_encode($gift) }})" title="Edit Gift">
                            <i class="fa-solid fa-pen" style="font-size: 11px;"></i>
                        </button>
                        <form action="{{ route('admin.gifts.destroy', $gift->id) }}" method="POST" onsubmit="return confirm('Delete {{ addslashes($gift->name) }}?');" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-outline-danger rounded-circle" style="width: 28px; height: 28px; padding: 0;" title="Delete Gift">
                                <i class="fa-solid fa-trash" style="font-size: 11px;"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="stat-icon-box mx-auto mb-3" style="width: 64px; height: 64px; font-size: 28px; background: rgba(244, 63, 94, 0.1); color: #f43f5e;">
                    <i class="fa-solid fa-gift"></i>
                </div>
                <h5 class="fw-bold">No Gifts Found</h5>
                <p class="text-muted">Click "Add New Gift" to add your first in-app reward item.</p>
                <button type="button" class="btn-ch-primary px-4 py-2" onclick="openCreateGiftModal()">
                    <i class="fa-solid fa-plus-circle me-1"></i> Add New Gift
                </button>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mb-5">
        {{ $gifts->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
</div>

<!-- ========================================== -->
<!-- 🎁 Modal: Create New Gift -->
<!-- ========================================== -->
<div class="modal fade" id="createGiftModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4" style="background: var(--card-bg, #ffffff);">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-box" style="width: 44px; height: 44px; font-size: 20px; background: rgba(244, 63, 94, 0.15); color: #f43f5e;">
                        <i class="fa-solid fa-gift"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bolder mb-0">Add New In-App Gift</h5>
                        <p class="text-muted small mb-0">Upload icon image, set diamond coin price and animation metadata</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.gifts.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Gift Name -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Gift Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Romantic Couple, Fire Dragon" required>
                        </div>

                        <!-- Diamond / Coin Price -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Coin / Diamond Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent"><i class="fa-solid fa-gem text-primary"></i></span>
                                <input type="number" name="coins" id="createGiftCoinsInput" class="form-control" placeholder="e.g. 500, 5550, 17700" required min="1" oninput="updateCoinsPreview(this.value, 'createCoinsPreviewBadge')">
                            </div>
                            <small class="text-muted" style="font-size: 11px;">
                                Preview Display: <span class="badge bg-purple rounded-pill" id="createCoinsPreviewBadge" style="background: #8b5cf6;">💎 0</span> (Auto formatted like 17.70K, 5.55K, 500)
                            </small>
                        </div>

                        <!-- Category -->
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="popular" selected>Popular</option>
                                <option value="luxury">Luxury</option>
                                <option value="romantic">Romantic</option>
                                <option value="effects">Effects / 3D</option>
                                <option value="vip">VIP Exclusive</option>
                            </select>
                        </div>

                        <!-- Badge Tag -->
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Badge / Ribbon</label>
                            <input type="text" name="badge" class="form-control" placeholder="e.g. HOT, 3D, VIP, NEW">
                        </div>

                        <!-- Sort Order -->
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0" min="0">
                        </div>

                        <!-- Image File Upload (Stored in public/uploads/gifts) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Gift Icon File (Upload to public/uploads/gifts)</label>
                            <input type="file" name="image_file" class="form-control" accept="image/*" onchange="previewImageFile(this, 'createImagePreview')">
                            <small class="text-muted" style="font-size: 11px;">PNG, SVG, JPG, WebP recommended with transparent background (max 5MB)</small>
                        </div>

                        <!-- Image Preview Box -->
                        <div class="col-12 col-md-6 text-center">
                            <label class="form-label fw-semibold d-block" style="font-size: 13px;">Live Icon Preview</label>
                            <div class="p-2 border rounded-3 d-inline-block" style="background: rgba(0,0,0,0.05); min-width: 90px; min-height: 90px;">
                                <img id="createImagePreview" src="{{ asset('assets/images/gifts/gift-box-default.png') }}" class="img-fluid" style="height: 75px; width: 75px; object-fit: contain;">
                            </div>
                        </div>

                        <!-- Animation Type & URL (SVGA / Lottie / MP4) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Animation Format</label>
                            <select name="animation_type" class="form-select">
                                <option value="image" selected>Standard Static / PNG</option>
                                <option value="svga">SVGA Animation</option>
                                <option value="lottie">Lottie JSON</option>
                                <option value="webp">Animated WebP</option>
                                <option value="mp4">MP4 Video Alpha</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Animation File URL (Optional)</label>
                            <input type="text" name="animation_url" class="form-control" placeholder="https://.../dragon_fly.svga">
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Description / Note</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief description of the gift effect..."></textarea>
                        </div>

                        <!-- Toggles -->
                        <div class="col-12 d-flex gap-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="createIsActive" checked value="1">
                                <label class="form-check-label fw-semibold" for="createIsActive" style="font-size: 13px;">Active in Mobile App</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_broadcast" id="createIsBroadcast" value="1">
                                <label class="form-check-label fw-semibold" for="createIsBroadcast" style="font-size: 13px;">Global Full-Screen Broadcast</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-ch-primary px-4">Save & Publish Gift</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- ✏️ Modal: Edit Existing Gift -->
<!-- ========================================== -->
<div class="modal fade" id="editGiftModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4" style="background: var(--card-bg, #ffffff);">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-box" style="width: 44px; height: 44px; font-size: 20px; background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bolder mb-0">Edit In-App Gift</h5>
                        <p class="text-muted small mb-0">Update coin price, image, category and details</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="editGiftForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Gift Name -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Gift Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editGiftName" class="form-control" required>
                        </div>

                        <!-- Diamond / Coin Price -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Coin / Diamond Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent"><i class="fa-solid fa-gem text-primary"></i></span>
                                <input type="number" name="coins" id="editGiftCoins" class="form-control" required min="1" oninput="updateCoinsPreview(this.value, 'editCoinsPreviewBadge')">
                            </div>
                            <small class="text-muted" style="font-size: 11px;">
                                Display Badge: <span class="badge bg-purple rounded-pill" id="editCoinsPreviewBadge" style="background: #8b5cf6;">💎 0</span>
                            </small>
                        </div>

                        <!-- Category -->
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Category <span class="text-danger">*</span></label>
                            <select name="category" id="editGiftCategory" class="form-select" required>
                                <option value="popular">Popular</option>
                                <option value="luxury">Luxury</option>
                                <option value="romantic">Romantic</option>
                                <option value="effects">Effects / 3D</option>
                                <option value="vip">VIP Exclusive</option>
                            </select>
                        </div>

                        <!-- Badge -->
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Badge</label>
                            <input type="text" name="badge" id="editGiftBadge" class="form-control">
                        </div>

                        <!-- Sort Order -->
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Sort Order</label>
                            <input type="number" name="sort_order" id="editGiftSortOrder" class="form-control" min="0">
                        </div>

                        <!-- Replace Image File -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Replace Image (Upload to public/uploads/gifts)</label>
                            <input type="file" name="image_file" class="form-control" accept="image/*" onchange="previewImageFile(this, 'editImagePreview')">
                            <small class="text-muted" style="font-size: 11px;">Leave empty to keep current image</small>
                        </div>

                        <!-- Current Image Preview -->
                        <div class="col-12 col-md-6 text-center">
                            <label class="form-label fw-semibold d-block" style="font-size: 13px;">Current Icon</label>
                            <div class="p-2 border rounded-3 d-inline-block" style="background: rgba(0,0,0,0.05); min-width: 90px; min-height: 90px;">
                                <img id="editImagePreview" src="" class="img-fluid" style="height: 75px; width: 75px; object-fit: contain;">
                            </div>
                        </div>

                        <!-- Animation Type & URL -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Animation Format</label>
                            <select name="animation_type" id="editGiftAnimationType" class="form-select">
                                <option value="image">Standard Static / PNG</option>
                                <option value="svga">SVGA Animation</option>
                                <option value="lottie">Lottie JSON</option>
                                <option value="webp">Animated WebP</option>
                                <option value="mp4">MP4 Video Alpha</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Animation File URL</label>
                            <input type="text" name="animation_url" id="editGiftAnimationUrl" class="form-control">
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Description</label>
                            <textarea name="description" id="editGiftDescription" class="form-control" rows="2"></textarea>
                        </div>

                        <!-- Toggles -->
                        <div class="col-12 d-flex gap-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="editIsActive" value="1">
                                <label class="form-check-label fw-semibold" for="editIsActive" style="font-size: 13px;">Active in Mobile App</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_broadcast" id="editIsBroadcast" value="1">
                                <label class="form-check-label fw-semibold" for="editIsBroadcast" style="font-size: 13px;">Global Full-Screen Broadcast</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-ch-primary px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 🎁 Modal: Direct Award Gift to User (Admin Tool) -->
<!-- ========================================== -->
<div class="modal fade" id="giveGiftModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4" style="background: var(--card-bg, #ffffff);">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-box" style="width: 44px; height: 44px; font-size: 20px; background: rgba(16, 185, 129, 0.15); color: #10b981;">
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bolder mb-0">Award / Give Gift to Host</h5>
                        <p class="text-muted small mb-0">Directly add received gifts count to any host's profile</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.gifts.give') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 13px;">Select Receiver Host / User <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select" required>
                            <option value="">-- Choose Host User --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->display_name }} (#{{ $u->account_id ?? $u->id }}) - {{ $u->gender ?? 'user' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 13px;">Select Gift <span class="text-danger">*</span></label>
                        <select name="gift_id" class="form-select" required>
                            <option value="">-- Choose Gift Item --</option>
                            @foreach($gifts as $g)
                                <option value="{{ $g->id }}">{{ $g->name }} (💎 {{ $g->formatted_coins }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 13px;">Quantity / Count Slot (e.g. x1, x2, x4, x32) <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control" value="1" min="1" max="1000" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold">Send Reward</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openCreateGiftModal() {
        const modal = new bootstrap.Modal(document.getElementById('createGiftModal'));
        modal.show();
    }

    function openGiveGiftModal() {
        const modal = new bootstrap.Modal(document.getElementById('giveGiftModal'));
        modal.show();
    }

    function openEditGiftModal(gift) {
        document.getElementById('editGiftForm').action = `/admin/gifts/${gift.id}`;
        document.getElementById('editGiftName').value = gift.name || '';
        document.getElementById('editGiftCoins').value = gift.coins || 0;
        document.getElementById('editGiftCategory').value = gift.category || 'popular';
        document.getElementById('editGiftBadge').value = gift.badge || '';
        document.getElementById('editGiftSortOrder').value = gift.sort_order || 0;
        document.getElementById('editGiftAnimationType').value = gift.animation_type || 'image';
        document.getElementById('editGiftAnimationUrl').value = gift.animation_url || '';
        document.getElementById('editGiftDescription').value = gift.description || '';
        document.getElementById('editIsActive').checked = !!gift.is_active;
        document.getElementById('editIsBroadcast').checked = !!gift.is_broadcast;
        
        document.getElementById('editImagePreview').src = gift.image_url || '';
        updateCoinsPreview(gift.coins, 'editCoinsPreviewBadge');

        const modal = new bootstrap.Modal(document.getElementById('editGiftModal'));
        modal.show();
    }

    function updateCoinsPreview(val, targetId) {
        const num = parseFloat(val) || 0;
        let formatted = num.toString();
        if (num >= 1000000) {
            formatted = (num / 1000000).toFixed(2) + 'M';
        } else if (num >= 1000) {
            const k = (num / 1000).toFixed(2);
            formatted = (k.endsWith('.00') ? parseInt(num/1000) : k) + 'K';
        }
        const badge = document.getElementById(targetId);
        if (badge) {
            badge.innerText = '💎 ' + formatted;
        }
    }

    function previewImageFile(input, previewImgId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(previewImgId).src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
