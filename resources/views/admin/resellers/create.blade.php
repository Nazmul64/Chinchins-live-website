@extends('layouts.admin')

@section('title', 'Create New Reseller')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <a href="{{ route('admin.resellers.index') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Resellers</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Create New</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-user-plus text-warning"></i>
                <span>Add New Reseller Account</span>
            </h1>
            <p class="page-subtitle">Create a new reseller profile with login credentials, location, bio, discount badges, and initial coin balance.</p>
        </div>
        <a href="{{ route('admin.resellers.index') }}" class="btn btn-outline-secondary rounded-3 px-3">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Resellers
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="card border-0 rounded-4 shadow-sm" style="background: var(--card-bg-light); border: 1px solid var(--border-color) !important;">
                <div class="card-body p-4 p-md-5">
                    <form method="POST" action="{{ route('admin.resellers.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row g-4">
                            <!-- Section 1: Account & Credentials -->
                            <div class="col-12">
                                <h5 class="fw-bold text-dark pb-2 border-bottom">
                                    <i class="fa-solid fa-key text-primary me-2"></i> 1. Reseller Portal Credentials
                                </h5>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold" style="font-size: 13px;">Reseller Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. MURAD COINS RESELLER" value="{{ old('name') }}" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold" style="font-size: 13px;">Email Address (Login Username) <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control rounded-3 font-monospace" placeholder="murad@chinchins.live" value="{{ old('email') }}" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold" style="font-size: 13px;">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control rounded-3" placeholder="Minimum 6 characters" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold" style="font-size: 13px;">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control rounded-3" placeholder="Repeat password" required>
                            </div>

                            <!-- Section 2: Profile & Details -->
                            <div class="col-12 mt-4">
                                <h5 class="fw-bold text-dark pb-2 border-bottom">
                                    <i class="fa-solid fa-id-card text-warning me-2"></i> 2. Public Profile & Badges (Shown in Mobile App)
                                </h5>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold" style="font-size: 13px;">Phone / WhatsApp Number</label>
                                <input type="text" name="phone" class="form-control rounded-3 font-monospace" placeholder="01848340232" value="{{ old('phone') }}">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold" style="font-size: 13px;">Level Badge (e.g. Lv5, Lv10)</label>
                                <input type="text" name="level" class="form-control rounded-3" value="Lv5" placeholder="Lv5">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold" style="font-size: 13px;">Location / City</label>
                                <input type="text" name="location" class="form-control rounded-3" value="Dhaka, Bangladesh" placeholder="Dhaka">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold" style="font-size: 13px;">Discount Tag / Promo Badge</label>
                                <input type="text" name="discount_tag" class="form-control rounded-3" value="Up To 29%↑" placeholder="Up To 29%↑">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold" style="font-size: 13px;">Badge Title</label>
                                <input type="text" name="badge_title" class="form-control rounded-3" value="Diamond Reseller" placeholder="Diamond Reseller">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold" style="font-size: 13px;">Initial Coin Stock Deposit</label>
                                <input type="number" name="initial_coins" class="form-control rounded-3 font-monospace fs-5 text-warning fw-bold" value="50000" min="0">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold" style="font-size: 13px;">Bio / Customer Notice Text (Bengali or English)</label>
                                <textarea name="bio" class="form-control rounded-3" rows="4" placeholder="কয়েন রিচার্জ, হোস্টিং এবং বিভিন্ন ধরণের গিফট ক্রয় করা হয়...">কয়েন রিচার্জ, হোস্টিং এবং বিভিন্ন ধরণের গিফট ক্রয় করা হয়
যোগাযোগ ০১848340232
হোস্টিং সেলারি তুলনামূলক বেশি দেওয়া হয়</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold" style="font-size: 13px;">Reseller Profile Avatar / Logo</label>
                                <input type="file" name="avatar" class="form-control rounded-3" accept="image/*">
                                <small class="text-muted" style="font-size: 11px;">Select JPG, PNG, WEBP. Saved automatically in <code>uploads/reseller/</code></small>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-check form-switch p-0 d-flex align-items-center gap-3">
                                    <input class="form-check-input ms-0" type="checkbox" name="is_active" id="isActiveCheck" value="1" checked style="width: 44px; height: 22px; cursor: pointer;">
                                    <label class="form-check-label fw-bold" for="isActiveCheck" style="cursor: pointer; font-size: 13px;">
                                        Active & Visible to App Users
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-check form-switch p-0 d-flex align-items-center gap-3">
                                    <input class="form-check-input ms-0" type="checkbox" name="is_online" id="isOnlineCheck" value="1" checked style="width: 44px; height: 22px; cursor: pointer;">
                                    <label class="form-check-label fw-bold" for="isOnlineCheck" style="cursor: pointer; font-size: 13px;">
                                        Show as Currently Online
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                <a href="{{ route('admin.resellers.index') }}" class="btn btn-secondary rounded-3 px-4">Cancel</a>
                                <button type="submit" class="btn-ch-primary px-4">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Save & Create Reseller
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
