@extends('layouts.admin')

@section('title', 'App Branding & General Settings')

@section('content')
<div class="container-fluid px-0">
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">App Branding & Settings</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-sliders text-primary"></i>
                <span>App Branding & Global Settings</span>
            </h1>
            <p class="page-subtitle">Configure mobile app name, login/registration logo, app icon, free messaging limits, and coin rates.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4" style="background: var(--card-bg, #ffffff);">
                <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-paintbrush text-pink me-2"></i> App Identity & Branding</h5>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">App Name <span class="text-danger">*</span></label>
                            <input type="text" name="app_name" class="form-control" value="{{ old('app_name', $merged['app_name'] ?? 'Chinchins Live') }}" required>
                            <small class="text-muted" style="font-size: 11px;">Displayed on Login, Register and Header</small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">App Tagline</label>
                            <input type="text" name="app_tagline" class="form-control" value="{{ old('app_tagline', $merged['app_tagline'] ?? '') }}">
                        </div>

                        <!-- App Logo Upload -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">App Logo (Login & Registration Screen)</label>
                            <input type="file" name="app_logo_file" class="form-control" accept="image/*" onchange="previewImg(this, 'appLogoPreview')">
                            <small class="text-muted" style="font-size: 11px;">PNG or SVG with transparent background</small>
                        </div>

                        <div class="col-12 col-md-6 text-center">
                            <label class="form-label fw-semibold d-block" style="font-size: 13px;">Current App Logo</label>
                            <div class="p-3 border rounded-3 d-inline-block" style="background: rgba(0,0,0,0.04); min-width: 120px;">
                                <img id="appLogoPreview" src="{{ asset($merged['app_logo'] ?? 'assets/images/branding/logo.png') }}" class="img-fluid" style="max-height: 55px;" onerror="this.src='{{ asset('assets/images/branding/logo.png') }}'">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-comments text-primary me-2"></i> Messaging & Chat Rules</h5>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Free Messages Limit (Per User)</label>
                            <input type="number" name="free_messages_limit" class="form-control" value="{{ old('free_messages_limit', $merged['free_messages_limit'] ?? 5) }}" min="0" max="100">
                            <small class="text-muted" style="font-size: 11px;">After 5 messages, user is prompted to recharge coins</small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 13px;">Coin Cost Per Message (After Free Limit)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent"><i class="fa-solid fa-gem text-primary"></i></span>
                                <input type="number" name="message_coin_cost" class="form-control" value="{{ old('message_coin_cost', $merged['message_coin_cost'] ?? 5) }}" min="0">
                            </div>
                            <small class="text-muted" style="font-size: 11px;">Deducted per text/photo/voice message</small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn-ch-primary px-4 py-2">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center" style="background: var(--card-bg, #ffffff);">
                <div class="stat-icon-box mx-auto mb-3" style="width: 55px; height: 55px; background: rgba(59,130,246,0.15); color: #3b82f6;">
                    <i class="fa-solid fa-mobile-screen"></i>
                </div>
                <h6 class="fw-bold">API Endpoint for Flutter</h6>
                <p class="text-muted small">Your Flutter mobile app calls <code>GET /api/app/config</code> on startup to load the dynamic App Name, Logo, and free messaging limits automatically.</p>
                <div class="p-3 bg-light rounded-3 text-start small font-monospace text-break">
                    GET /api/app/config<br>
                    {<br>
                    &nbsp;&nbsp;"app_name": "{{ $merged['app_name'] ?? 'Chinchins Live' }}",<br>
                    &nbsp;&nbsp;"app_logo_url": "...",<br>
                    &nbsp;&nbsp;"free_messages_limit": {{ $merged['free_messages_limit'] ?? 5 }}<br>
                    }
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function previewImg(input, targetId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(targetId).src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
