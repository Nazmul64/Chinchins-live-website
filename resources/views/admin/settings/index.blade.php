@extends('layouts.admin')

@section('title', 'App Branding, In-App Updates & Push Notifications')

@section('content')
<div class="container-fluid px-0">
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">App Branding & In-App Updates</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-sliders text-primary"></i>
                <span>App Branding, OTA Updates & Push Engine</span>
            </h1>
            <p class="page-subtitle">Manage remote dynamic features, OTA in-app update releases, Firebase push notifications (IMO-style ringing), and branding.</p>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center gap-3" style="background: var(--card-bg, #ffffff);">
                <div class="stat-icon-box" style="width: 48px; height: 48px; background: rgba(59,130,246,0.15); color: #3b82f6; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fa-solid fa-code-branch"></i>
                </div>
                <div>
                    <div class="text-muted small">Current Version</div>
                    <div class="fw-bold fs-5">v{{ $latestVersion->version_name ?? $merged['app_version'] ?? '1.0.0' }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center gap-3" style="background: var(--card-bg, #ffffff);">
                <div class="stat-icon-box" style="width: 48px; height: 48px; background: rgba(16,185,129,0.15); color: #10b981; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fa-solid fa-mobile-screen"></i>
                </div>
                <div>
                    <div class="text-muted small">Registered Devices</div>
                    <div class="fw-bold fs-5">{{ $registeredDevicesCount ?? 0 }} Devices</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center gap-3" style="background: var(--card-bg, #ffffff);">
                <div class="stat-icon-box" style="width: 48px; height: 48px; background: rgba(236,72,153,0.15); color: #ec4899; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <div>
                    <div class="text-muted small">FCM Push Tokens</div>
                    <div class="fw-bold fs-5">{{ $totalPushTokensCount ?? 0 }} Users</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center gap-3" style="background: var(--card-bg, #ffffff);">
                <div class="stat-icon-box" style="width: 48px; height: 48px; background: rgba(245,158,11,0.15); color: #f59e0b; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <div>
                    <div class="text-muted small">OTA Remote Config</div>
                    <div class="fw-bold fs-5 text-success">Active & Live</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Nav Tabs -->
    <ul class="nav nav-pills mb-4 gap-2" id="settingsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 py-2 fw-semibold" id="branding-tab" data-bs-toggle="tab" data-bs-target="#branding" type="button" role="tab">
                <i class="fa-solid fa-palette me-2"></i> Branding & Messaging
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 py-2 fw-semibold" id="version-tab" data-bs-toggle="tab" data-bs-target="#version" type="button" role="tab">
                <i class="fa-solid fa-cloud-arrow-up me-2"></i> In-App Updates & Releases
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 py-2 fw-semibold" id="push-tab" data-bs-toggle="tab" data-bs-target="#push" type="button" role="tab">
                <i class="fa-solid fa-bullhorn me-2"></i> Firebase FCM & Push Alerts
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 py-2 fw-semibold" id="api-tab" data-bs-toggle="tab" data-bs-target="#api" type="button" role="tab">
                <i class="fa-solid fa-code me-2"></i> Mobile API Docs
            </button>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="settingsTabContent">
        <!-- TAB 1: App Identity & Branding -->
        <div class="tab-pane fade show active" id="branding" role="tabpanel">
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
                                    <label class="form-label fw-semibold" style="font-size: 13px;">App Logo (Login & Header)</label>
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

                            <h5 class="fw-bold mb-3"><i class="fa-solid fa-comments text-primary me-2"></i> Messaging & Free Quota Rules</h5>
                            
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

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold" style="font-size: 13px;">Support Email</label>
                                    <input type="email" name="support_email" class="form-control" value="{{ old('support_email', $merged['support_email'] ?? 'support@chinchins.live') }}">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold" style="font-size: 13px;">Support WhatsApp Number</label>
                                    <input type="text" name="support_whatsapp" class="form-control" value="{{ old('support_whatsapp', $merged['support_whatsapp'] ?? '+8801700000000') }}">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn-ch-primary px-4 py-2">Save Branding Settings</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 text-center" style="background: var(--card-bg, #ffffff);">
                        <div class="stat-icon-box mx-auto mb-3" style="width: 55px; height: 55px; background: rgba(59,130,246,0.15); color: #3b82f6; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                            <i class="fa-solid fa-mobile-screen"></i>
                        </div>
                        <h6 class="fw-bold">Dynamic Remote Configuration</h6>
                        <p class="text-muted small">Your Flutter mobile app calls <code>GET /api/app/config</code> on startup to load dynamic app settings without rebuilding APK.</p>
                        <div class="p-3 bg-light rounded-3 text-start small font-monospace text-break">
                            GET /api/app/config<br>
                            {<br>
                            &nbsp;&nbsp;"app_name": "{{ $merged['app_name'] ?? 'Chinchins Live' }}",<br>
                            &nbsp;&nbsp;"latest_version": "{{ $merged['app_version'] ?? '1.0.0' }}",<br>
                            &nbsp;&nbsp;"free_messages_limit": {{ $merged['free_messages_limit'] ?? 5 }}<br>
                            }
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: In-App Updates & APK Release Manager (OTA) -->
        <div class="tab-pane fade" id="version" role="tabpanel">
            <div class="row g-4">
                <div class="col-12 col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 p-4" style="background: var(--card-bg, #ffffff);">
                        <h5 class="fw-bold mb-2"><i class="fa-solid fa-rocket text-primary me-2"></i> Publish New In-App Update (OTA)</h5>
                        <p class="text-muted small mb-4">When you publish a new version, users with old APK versions will automatically see an in-app update popup with changelog & download link without having to manually reinstall.</p>

                        <form action="{{ route('admin.settings.version.publish') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold" style="font-size: 13px;">Version Name <span class="text-danger">*</span></label>
                                    <input type="text" name="version_name" class="form-control" placeholder="e.g. 1.0.2" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold" style="font-size: 13px;">Version Code <span class="text-danger">*</span></label>
                                    <input type="number" name="version_code" class="form-control" placeholder="e.g. 2" required min="1">
                                    <small class="text-muted" style="font-size: 11px;">Must be higher than previous code</small>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold" style="font-size: 13px;">Minimum Supported Version</label>
                                    <input type="text" name="min_supported_version" class="form-control" placeholder="e.g. 1.0.0" value="1.0.0">
                                    <small class="text-muted" style="font-size: 11px;">Versions below this will be force-updated</small>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold" style="font-size: 13px;">Update Title</label>
                                    <input type="text" name="title" class="form-control" value="Exciting New Features & Live Updates! 🎉" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold" style="font-size: 13px;">Changelog / Release Notes</label>
                                    <textarea name="changelog" class="form-control" rows="3" placeholder="• Instant incoming call ringing (IMO/WhatsApp style)&#10;• Profile visitor notifications&#10;• Faster video calling and new gifts"></textarea>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold" style="font-size: 13px;">Direct APK Download URL</label>
                                    <input type="url" name="download_url" class="form-control" placeholder="https://chinchins.live/downloads/latest.apk">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold" style="font-size: 13px;">Upload New APK File</label>
                                    <input type="file" name="apk_file" class="form-control" accept=".apk">
                                    <small class="text-muted" style="font-size: 11px;">Uploads directly to server /downloads/</small>
                                </div>
                            </div>

                            <hr class="my-3">
                            <h6 class="fw-bold mb-3"><i class="fa-solid fa-sliders text-success me-2"></i> Dynamic Feature Flags (Remote Control)</h6>
                            <div class="row g-2 mb-4">
                                <div class="col-12 col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="enable_video_calling" id="f1" checked>
                                        <label class="form-check-label fw-semibold" for="f1" style="font-size: 13px;">Enable Video Calling</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="enable_instant_call_wake" id="f2" checked>
                                        <label class="form-check-label fw-semibold" for="f2" style="font-size: 13px;">Instant IMO-style Call Wake</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="enable_random_matching" id="f3" checked>
                                        <label class="form-check-label fw-semibold" for="f3" style="font-size: 13px;">Enable Random Match Tab</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="enable_profile_view_alert" id="f4" checked>
                                        <label class="form-check-label fw-semibold" for="f4" style="font-size: 13px;">Profile Visitor Alerts</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="force_update" id="f5">
                                        <label class="form-check-label fw-semibold text-danger" for="f5" style="font-size: 13px;">Force Update (Mandatory)</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="broadcast_push" id="f6" checked>
                                        <label class="form-check-label fw-semibold text-primary" for="f6" style="font-size: 13px;">Broadcast FCM Push to all Devices</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn-ch-primary px-4 py-2">
                                    <i class="fa-solid fa-cloud-arrow-up me-2"></i> Publish Release Version
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4" style="background: var(--card-bg, #ffffff);">
                        <h6 class="fw-bold mb-3"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> Released App Versions History</h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Version</th>
                                        <th>Code</th>
                                        <th>Type</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($allVersions as $v)
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-primary">v{{ $v->version_name }}</span>
                                            @if($v->is_active)
                                                <span class="badge bg-success rounded-pill ms-1" style="font-size: 10px;">Active</span>
                                            @endif
                                        </td>
                                        <td><code>{{ $v->version_code }}</code></td>
                                        <td>
                                            @if($v->force_update)
                                                <span class="badge bg-danger rounded-pill">Force</span>
                                            @else
                                                <span class="badge bg-secondary rounded-pill">Flexible</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $v->created_at->format('M d, Y') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No custom releases published yet. Default v1.0.0 active.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: Firebase FCM & Push Notification Dispatcher -->
        <div class="tab-pane fade" id="push" role="tabpanel">
            <div class="row g-4">
                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4" style="background: var(--card-bg, #ffffff);">
                        <h5 class="fw-bold mb-2"><i class="fa-solid fa-key text-warning me-2"></i> Firebase Cloud Messaging (FCM) Config</h5>
                        <p class="text-muted small mb-4">Paste your Firebase Project Server Key to enable High-Priority Incoming Call Push (wakes up mobile phone and rings like IMO / WhatsApp).</p>

                        <form action="{{ route('admin.settings.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="app_name" value="{{ $merged['app_name'] ?? 'Chinchins Live' }}">
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size: 13px;">FCM Server Key (Firebase Cloud Messaging)</label>
                                <textarea name="fcm_server_key" class="form-control font-monospace" rows="3" placeholder="AAAA... Firebase Legacy Server Key or Cloud Messaging Token">{{ old('fcm_server_key', $merged['fcm_server_key'] ?? '') }}</textarea>
                                <small class="text-muted" style="font-size: 11px;">Found in Firebase Console > Project Settings > Cloud Messaging</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold" style="font-size: 13px;">FCM Sender ID / Project ID</label>
                                <input type="text" name="fcm_sender_id" class="form-control" value="{{ old('fcm_sender_id', $merged['fcm_sender_id'] ?? '') }}" placeholder="e.g. 10928374652">
                            </div>

                            <button type="submit" class="btn-ch-primary px-4 py-2">
                                <i class="fa-solid fa-floppy-disk me-2"></i> Save Firebase Keys
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4" style="background: var(--card-bg, #ffffff);">
                        <h5 class="fw-bold mb-2"><i class="fa-solid fa-bullhorn text-pink me-2"></i> Send Instant Broadcast Notification</h5>
                        <p class="text-muted small mb-4">Send a live push notification to all installed mobile apps right now (like TikTok / IMO announcements).</p>

                        <form action="{{ route('admin.settings.push.broadcast') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Notification Title <span class="text-danger">*</span></label>
                                <input type="text" name="push_title" class="form-control" placeholder="e.g. Hot hosts are live! Join now 🔥" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Notification Message <span class="text-danger">*</span></label>
                                <textarea name="push_body" class="form-control" rows="3" placeholder="e.g. Start 1-on-1 private video calls with verified hosts now..." required></textarea>
                            </div>

                            <div class="d-flex align-items-center justify-content-between">
                                <span class="badge bg-primary-subtle text-primary p-2">
                                    <i class="fa-solid fa-tower-broadcast me-1"></i> Targets {{ $registeredDevicesCount ?? 0 }} Devices
                                </span>
                                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                                    <i class="fa-solid fa-paper-plane me-2"></i> Send Live Broadcast
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 4: Mobile API Docs -->
        <div class="tab-pane fade" id="api" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 p-4" style="background: var(--card-bg, #ffffff);">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-code text-primary me-2"></i> Mobile Client Endpoints (Flutter Integration)</h5>
                
                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <div class="border rounded-3 p-3 bg-light">
                            <h6 class="fw-bold text-primary"><span class="badge bg-success me-2">POST</span> /api/app/check-update</h6>
                            <p class="small text-muted mb-2">Call on app startup to detect OTA updates & force-update flags:</p>
                            <pre class="bg-dark text-white p-3 rounded-3 small mb-0 font-monospace"><code>// Request:
{
  "app_version": "1.0.0",
  "version_code": 1,
  "platform": "android"
}

// Response:
{
  "status": true,
  "data": {
    "has_update": true,
    "force_update": false,
    "latest_version": "1.0.2",
    "download_url": "https://chinchins.live/downloads/latest.apk",
    "changelog": "Instant IMO incoming call wake...",
    "remote_flags": {
      "enable_video_calling": true,
      "enable_instant_call_wake": true
    }
  }
}</code></pre>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="border rounded-3 p-3 bg-light">
                            <h6 class="fw-bold text-primary"><span class="badge bg-success me-2">POST</span> /api/app/device/register</h6>
                            <p class="small text-muted mb-2">Call when FCM token is generated to enable instant incoming call ringing:</p>
                            <pre class="bg-dark text-white p-3 rounded-3 small mb-0 font-monospace"><code>// Request:
{
  "fcm_token": "fcm_device_token_here...",
  "device_type": "android",
  "device_brand": "Samsung",
  "device_model": "Galaxy S24",
  "os_version": "Android 14",
  "app_version": "1.0.0"
}

// Response:
{
  "status": true,
  "message": "Device registered successfully for high-priority push notifications and incoming calls."
}</code></pre>
                        </div>
                    </div>
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
