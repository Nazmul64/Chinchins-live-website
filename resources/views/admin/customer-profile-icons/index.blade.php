@extends('layouts.admin')

@section('title', 'Customer Profile Icons Setting')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header Section -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1" style="color: #1e293b; font-family: 'Outfit', sans-serif;">
                <i class="fa-solid fa-icons me-2 text-pink" style="color: #ec4899;"></i>Customer Profile Icons Setting
            </h3>
            <p class="text-muted mb-0 small">
                Manage all 10 icons & pictures on the Mobile App <strong>"Me" (কাস্টমার প্রোফাইল)</strong> page. Upload custom images to <code>public/uploads/customer_profile_icon/</code> or use defaults.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ url('/api/customer-profile-icons') }}" target="_blank" class="btn btn-outline-primary btn-sm px-3 shadow-sm rounded-pill">
                <i class="fa-solid fa-code me-1"></i>View RESTful API JSON
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Metric Counter Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Total Profile Icons</div>
                        <h3 class="fw-bold mb-0 mt-1" style="color: #6366f1;">{{ $totalIcons }}</h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(99, 102, 241, 0.12); color: #6366f1;">
                        <i class="fa-solid fa-shapes fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Custom Uploaded</div>
                        <h3 class="fw-bold mb-0 mt-1" style="color: #ec4899;">{{ $customUploadedCount }}</h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(236, 72, 153, 0.12); color: #ec4899;">
                        <i class="fa-solid fa-cloud-arrow-up fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Default Icons Active</div>
                        <h3 class="fw-bold mb-0 mt-1" style="color: #06b6d4;">{{ $defaultIconsCount }}</h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(6, 182, 212, 0.12); color: #06b6d4;">
                        <i class="fa-solid fa-image fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Mobile Offline Ready</div>
                        <h3 class="fw-bold mb-0 mt-1" style="color: #10b981;">100%</h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.12); color: #10b981;">
                        <i class="fa-solid fa-bolt fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Layout (Grid + Mobile Live Simulator) -->
    <div class="row g-4">
        <!-- Left: 10 Icon Management Cards -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold" style="color: #1e293b;">
                        <i class="fa-solid fa-layer-group me-2 text-primary"></i>10 Profile Items & Picture Uploaders
                    </h5>
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">Uploads: <code>public/uploads/customer_profile_icon/</code></span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        @foreach($icons as $icon)
                        <div class="col-md-6">
                            <div class="card border rounded-4 p-3 h-100 position-relative transition-all shadow-hover" style="background: #f8fafc; border-color: #e2e8f0;">
                                <!-- Top Bar with Key & Badge -->
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-dark text-white rounded-pill px-2 py-1" style="font-size: 11px;">#{{ $icon->sort_order }}</span>
                                        <span class="fw-bold text-truncate" style="max-width: 180px; color: #0f172a;">{{ $icon->title }}</span>
                                    </div>
                                    @if($icon->is_custom)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2" style="font-size: 10px;">Custom Picture</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2" style="font-size: 10px;">Default Icon</span>
                                    @endif
                                </div>

                                <!-- Icon Display & Upload Form -->
                                <form action="{{ route('admin.customer-profile-icons.update', $icon->id) }}" method="POST" enctype="multipart/form-data" class="mt-2">
                                    @csrf
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <!-- Image Preview Box -->
                                        <div class="position-relative rounded-4 d-flex align-items-center justify-content-center overflow-hidden border shadow-sm flex-shrink-0" style="width: 72px; height: 72px; background: #0f172a;">
                                            <img src="{{ $icon->icon_url }}?v={{ time() }}" alt="{{ $icon->title }}" id="preview_{{ $icon->id }}" class="img-fluid p-2" style="max-height: 64px; object-fit: contain;">
                                        </div>

                                        <!-- Upload File Input -->
                                        <div class="flex-grow-1">
                                            <label class="form-label text-muted small mb-1 fw-semibold">Replace Picture / Icon:</label>
                                            <input type="file" name="icon_image" class="form-control form-control-sm rounded-3 shadow-none icon-file-input" data-preview="preview_{{ $icon->id }}" accept="image/png, image/jpeg, image/webp, image/svg+xml">
                                            <div class="form-text" style="font-size: 11px;">PNG, SVG, WebP, JPG (Max 5MB)</div>
                                        </div>
                                    </div>

                                    <!-- Editable Fields -->
                                    <div class="row g-2 mb-2">
                                        <div class="col-7">
                                            <label class="form-label text-muted small mb-1">Title Label</label>
                                            <input type="text" name="title" value="{{ $icon->title }}" class="form-control form-control-sm rounded-3" required>
                                        </div>
                                        <div class="col-5">
                                            <label class="form-label text-muted small mb-1">Badge Tag</label>
                                            <input type="text" name="badge_text" value="{{ $icon->badge_text }}" placeholder="e.g. big discount" class="form-control form-control-sm rounded-3">
                                        </div>
                                    </div>

                                    @if($icon->subtitle)
                                    <div class="mb-2">
                                        <label class="form-label text-muted small mb-1">Subtitle</label>
                                        <input type="text" name="subtitle" value="{{ $icon->subtitle }}" class="form-control form-control-sm rounded-3">
                                    </div>
                                    @endif

                                    <!-- Action Buttons -->
                                    <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active_{{ $icon->id }}" {{ $icon->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label small text-muted" for="active_{{ $icon->id }}">Active</label>
                                        </div>

                                        <div class="d-flex gap-1">
                                            @if($icon->is_custom)
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-2 reset-btn" data-id="{{ $icon->id }}" data-url="{{ route('admin.customer-profile-icons.reset', $icon->id) }}" title="Reset to default icon">
                                                    <i class="fa-solid fa-rotate-left"></i> Reset
                                                </button>
                                            @endif
                                            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">
                                                <i class="fa-solid fa-check me-1"></i>Save
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Live Mobile "Me" Screen Simulator Mockup -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white sticky-top" style="top: 20px;">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-mobile-screen-button me-2 text-primary"></i>Mobile "Me" Profile Live Preview
                    </h6>
                    <span class="badge bg-success rounded-pill px-2">Live Sync</span>
                </div>

                <!-- Phone Mockup Container -->
                <div class="phone-mockup rounded-4 overflow-hidden border shadow" style="background: #0f1026; color: #ffffff; font-family: 'Inter', sans-serif;">
                    <!-- Phone Status Bar -->
                    <div class="d-flex align-items-center justify-content-between px-3 pt-2 text-muted" style="font-size: 11px;">
                        <span>8:38 PM</span>
                        <div class="d-flex gap-1">
                            <i class="fa-solid fa-signal"></i>
                            <i class="fa-solid fa-wifi"></i>
                            <i class="fa-solid fa-battery-full"></i>
                        </div>
                    </div>

                    <!-- App Bar -->
                    <div class="d-flex align-items-center justify-content-between px-3 py-2">
                        <h5 class="fw-bold mb-0">Me</h5>
                        <i class="fa-solid fa-pen text-muted fs-6"></i>
                    </div>

                    <!-- User Profile Header -->
                    <div class="px-3 py-2 d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white shadow-lg" style="width: 58px; height: 58px; background: linear-gradient(135deg, #0284c7, #38bdf8); border: 2px solid #38bdf8; font-size: 20px;">
                            NA
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Nazmul Hossain</h6>
                            <div class="d-flex gap-1 align-items-center flex-wrap" style="font-size: 10px;">
                                <span class="badge bg-info text-dark rounded-pill px-2">♂ 22</span>
                                <span class="badge bg-info-subtle text-info rounded-pill px-2">Bangladesh</span>
                                <span class="badge bg-primary text-white rounded-pill px-2">Lv.1</span>
                            </div>
                            <div class="text-muted mt-1" style="font-size: 10px;">ID 36351449 <i class="fa-regular fa-copy"></i></div>
                        </div>
                    </div>

                    <!-- Like Counters -->
                    <div class="d-flex justify-content-around text-center py-2 px-3 my-1 border-top border-bottom border-dark">
                        <div>
                            <div class="fw-bold fs-6">5</div>
                            <div class="text-muted" style="font-size: 10px;">I Like</div>
                        </div>
                        <div>
                            <div class="fw-bold fs-6">0</div>
                            <div class="text-muted" style="font-size: 10px;">Like Me</div>
                        </div>
                    </div>

                    <!-- 1 & 2: Wallet Cards (My Gems & Beans Center) -->
                    @php
                        $gemIcon = $icons->firstWhere('key', 'my_gems');
                        $beanIcon = $icons->firstWhere('key', 'beans_center');
                    @endphp
                    <div class="row g-2 px-3 py-2">
                        <div class="col-6">
                            <div class="p-2 rounded-3 d-flex align-items-center justify-content-between" style="background: rgba(30, 27, 75, 0.8); border: 1px solid rgba(139, 92, 246, 0.3);">
                                <div>
                                    <div class="text-muted" style="font-size: 10px;">{{ $gemIcon->title ?? 'My Gems' }} &rsaquo;</div>
                                    <div class="fw-bold fs-6 text-white">410</div>
                                </div>
                                <img src="{{ $gemIcon?->icon_url ?? asset('uploads/customer_profile_icon/default_my_gems.png') }}" style="width: 32px; height: 32px; object-fit: contain;">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded-3 d-flex align-items-center justify-content-between" style="background: rgba(30, 27, 75, 0.8); border: 1px solid rgba(139, 92, 246, 0.3);">
                                <div>
                                    <div class="text-muted" style="font-size: 10px;">{{ $beanIcon->title ?? 'Beans Center' }} &rsaquo;</div>
                                    <div class="fw-bold fs-6 text-white">0</div>
                                </div>
                                <img src="{{ $beanIcon?->icon_url ?? asset('uploads/customer_profile_icon/default_beans_center.png') }}" style="width: 32px; height: 32px; object-fit: contain;">
                            </div>
                        </div>
                    </div>

                    <!-- 3: Spend Less, Get More Gems VIP Banner -->
                    @php $spendCard = $icons->firstWhere('key', 'spend_less_card'); @endphp
                    <div class="px-3 py-1">
                        <div class="p-2 rounded-3 d-flex align-items-center justify-content-between" style="background: rgba(30, 27, 75, 0.95); border: 1px solid rgba(234, 179, 8, 0.4);">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $spendCard?->icon_url ?? asset('uploads/customer_profile_icon/default_spend_less_card.png') }}" style="width: 36px; height: 26px; object-fit: contain;">
                                <div>
                                    <div class="fw-bold text-white" style="font-size: 11px;">{{ $spendCard->title ?? 'Spend Less, Get More Gems!' }}</div>
                                    <div class="text-muted" style="font-size: 9px;">{{ $spendCard->subtitle ?? 'Update to New User Weekly Card' }}</div>
                                </div>
                            </div>
                            <span class="badge bg-warning text-dark rounded-pill fw-bold" style="font-size: 9px;">{{ $spendCard->badge_text ?? 'big discount' }}</span>
                        </div>
                    </div>

                    <!-- 4 - 10: Quick Action Grid Icons (SVIP, Bag, Gems Center, Payment, Level, Sign-In, Reward) -->
                    @php
                        $gridKeys = ['svip', 'my_bag', 'gems_center', 'payment_details', 'my_level', 'sign_in', 'reward'];
                    @endphp
                    <div class="p-3">
                        <div class="row g-2 text-center">
                            @foreach($gridKeys as $gKey)
                                @php $gIcon = $icons->firstWhere('key', $gKey); @endphp
                                @if($gIcon)
                                <div class="col-3 mb-2">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center p-2 mb-1 shadow-sm" style="width: 44px; height: 44px; background: rgba(30, 27, 75, 0.9); border: 1px solid rgba(255, 255, 255, 0.1);">
                                            <img src="{{ $gIcon->icon_url }}" alt="{{ $gIcon->title }}" style="width: 28px; height: 28px; object-fit: contain;">
                                        </div>
                                        <span class="text-truncate text-white" style="font-size: 10px; max-width: 60px;">{{ $gIcon->title }}</span>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Bottom Nav Mockup -->
                    <div class="d-flex align-items-center justify-content-around py-2 border-top border-dark text-muted" style="font-size: 10px;">
                        <div><i class="fa-solid fa-house"></i><br>Home</div>
                        <div><i class="fa-solid fa-compass"></i><br>Discover</div>
                        <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; margin-top: -10px;"><i class="fa-solid fa-plus"></i></div>
                        <div><i class="fa-solid fa-message"></i><br>Inbox</div>
                        <div class="text-danger fw-bold"><i class="fa-solid fa-user"></i><br>Profile</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="resetForm" method="POST" style="display: none;">
    @csrf
</form>

@push('styles')
<style>
.shadow-hover:hover {
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
    border-color: #6366f1 !important;
    transform: translateY(-2px);
}
.transition-all {
    transition: all 0.2s ease-in-out;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Instant Image Preview on File Select
    document.querySelectorAll('.icon-file-input').forEach(input => {
        input.addEventListener('change', function() {
            const previewId = this.getAttribute('data-preview');
            const previewImg = document.getElementById(previewId);
            if (this.files && this.files[0] && previewImg) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    // Reset to Default Icon handler
    document.querySelectorAll('.reset-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('data-url');
            if (confirm('Are you sure you want to reset this custom picture to default built-in icon?')) {
                const form = document.getElementById('resetForm');
                form.action = url;
                form.submit();
            }
        });
    });
});
</script>
@endpush
@endsection
