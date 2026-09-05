@extends('layouts.admin')

@section('title', 'Call Rates & Revenue Sharing Settings')

@section('content')
<div class="container-fluid px-0">
    <!-- Premium Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <a href="{{ route('admin.calls.index') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Call Sessions</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Settings</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-sliders text-primary"></i>
                <span>Call Rates & Revenue Sharing Settings</span>
            </h1>
            <p class="page-subtitle">Configure free trial calling duration for new users, audio/video call rates per minute, and the 50/50 revenue split between hosts and platform.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.calls.index') }}" class="btn-ch-primary">
                <i class="fa-solid fa-phone-volume"></i> View Call Sessions Log
            </a>
        </div>
    </div>

    <form action="{{ route('admin.calls.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-4">
            <!-- Left Column: Core Settings Form -->
            <div class="col-12 col-xl-8">
                <!-- 1. Free Trial & Calling Controls Card -->
                <div class="card mb-4" style="border-radius: 16px; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-dark mb-1">
                                <i class="fa-solid fa-gift text-warning me-2"></i> Free Trial Calling Configuration
                            </h5>
                            <p class="text-muted mb-0" style="font-size: 13px;">Allow new users to experience audio/video calling for free before requiring a coin recharge.</p>
                        </div>
                        <!-- Free Trial Switch -->
                        <div class="form-check form-switch form-switch-lg">
                            <input type="hidden" name="is_free_call_enabled" value="0">
                            <input class="form-check-input" type="checkbox" name="is_free_call_enabled" id="isFreeCallEnabled" value="1" {{ $config['is_free_call_enabled'] ? 'checked' : '' }} style="cursor: pointer; transform: scale(1.3);">
                            <label class="form-check-label fw-bold ms-2 {{ $config['is_free_call_enabled'] ? 'text-success' : 'text-danger' }}" for="isFreeCallEnabled">
                                {{ $config['is_free_call_enabled'] ? 'Free Trial Active' : 'Free Trial Disabled' }}
                            </label>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Free Trial Duration in Seconds -->
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                                    <i class="fa-regular fa-clock text-warning me-1"></i> Free Trial Duration (Seconds)
                                </label>
                                <div class="input-group mb-2">
                                    <input type="number" name="free_call_duration_seconds" id="inputFreeSecs" class="form-control" value="{{ $config['free_call_duration_seconds'] }}" min="1" max="300" required style="font-weight: 700; font-size: 15px;">
                                    <span class="input-group-text bg-light fw-bold">Seconds</span>
                                </div>
                                <!-- Quick Select Pills -->
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 quick-sec-btn" data-sec="5" style="font-size: 11px;">5s</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 quick-sec-btn" data-sec="10" style="font-size: 11px;">10s</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 quick-sec-btn" data-sec="30" style="font-size: 11px;">30s</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 quick-sec-btn" data-sec="60" style="font-size: 11px;">60s (1 min)</button>
                                </div>
                                <small class="text-muted mt-1 d-block">After this duration expires, if the user has 0 coins, the app will prompt them to deposit.</small>
                            </div>

                            <!-- Free Calls Limit per User -->
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                                    <i class="fa-solid fa-user-check text-info me-1"></i> Free Trial Calls Limit per New User
                                </label>
                                <div class="input-group">
                                    <input type="number" name="free_calls_per_user" class="form-control" value="{{ $config['free_calls_per_user'] }}" min="0" max="10" required style="font-weight: 700;">
                                    <span class="input-group-text bg-light">Calls</span>
                                </div>
                                <small class="text-muted">How many times a newly registered user gets free trial calling.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Call Rates & Revenue Sharing Card -->
                <div class="card mb-4" style="border-radius: 16px; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-dark mb-1">
                                <i class="fa-solid fa-coins text-warning me-2"></i> Per-Minute Billing (100 Coins/min) & 50/50 Revenue Split
                            </h5>
                            <p class="text-muted mb-0" style="font-size: 13px;">Set coins charged per minute and how coins are divided 50/50 between the female host and the platform.</p>
                        </div>
                        <!-- Calling Global Switch -->
                        <div class="form-check form-switch form-switch-lg">
                            <input type="hidden" name="is_call_enabled" value="0">
                            <input class="form-check-input" type="checkbox" name="is_call_enabled" id="isCallEnabled" value="1" {{ $config['is_call_enabled'] ? 'checked' : '' }} style="cursor: pointer; transform: scale(1.3);">
                            <label class="form-check-label fw-bold ms-2 {{ $config['is_call_enabled'] ? 'text-success' : 'text-danger' }}" for="isCallEnabled">
                                {{ $config['is_call_enabled'] ? 'Calls Enabled' : 'Calls Disabled' }}
                            </label>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Video Call Rate -->
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                                    <i class="fa-solid fa-video text-primary me-1"></i> Video Call Rate (Coins / Minute)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fa-solid fa-video text-primary"></i></span>
                                    <input type="number" name="video_call_rate_per_minute" id="inputVideoRate" class="form-control" value="{{ $config['video_call_rate_per_minute'] }}" min="1" required style="font-weight: 700; font-size: 15px;">
                                    <span class="input-group-text bg-light">Coins/min</span>
                                </div>
                                <small class="text-muted">Rate: 100 coins/min (Auto calculated as ~1.67 coins/second).</small>
                            </div>

                            <!-- Audio Call Rate -->
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                                    <i class="fa-solid fa-phone text-success me-1"></i> Audio Call Rate (Coins / Minute)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fa-solid fa-phone text-success"></i></span>
                                    <input type="number" name="audio_call_rate_per_minute" id="inputAudioRate" class="form-control" value="{{ $config['audio_call_rate_per_minute'] }}" min="1" required style="font-weight: 700; font-size: 15px;">
                                    <span class="input-group-text bg-light">Coins/min</span>
                                </div>
                                <small class="text-muted">Rate: 100 coins/min (Auto calculated as ~1.67 coins/second).</small>
                            </div>

                            <!-- Host Earning Share % -->
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                                    <i class="fa-solid fa-heart text-danger me-1"></i> Host (Female Receiver) Earning Share (%)
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.5" name="host_earning_percent" id="inputHostPercent" class="form-control" value="{{ $config['host_earning_percent'] }}" min="0" max="100" required style="font-weight: 700; font-size: 15px; color: #10b981;">
                                    <span class="input-group-text bg-light fw-bold">%</span>
                                </div>
                                <small class="text-muted" id="hostCoinsHelper">Host receives 50 coins for every 100 coins spent by caller.</small>
                            </div>

                            <!-- Admin Revenue Share % -->
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                                    <i class="fa-solid fa-building-columns text-purple me-1" style="color: #8b5cf6;"></i> Admin Platform Revenue Share (%)
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.5" id="inputAdminPercent" class="form-control" value="{{ $config['admin_commission_percent'] }}" readonly style="font-weight: 700; font-size: 15px; color: #8b5cf6; background: #f8fafc;">
                                    <span class="input-group-text bg-light fw-bold">%</span>
                                </div>
                                <small class="text-muted" id="adminCoinsHelper">Platform keeps 50 coins for every 100 coins spent by caller.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. In-Call Teaser Banner, Quick Messages & Free Chances Card -->
                <div class="card mb-4" style="border-radius: 16px; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <h5 class="fw-bold text-dark mb-1">
                            <i class="fa-solid fa-comment-dots text-pink me-2" style="color: #ec4899;"></i> In-Call Screen Banners & Quick Icebreaker Chat (ইন-কল ব্যানার ও কুইক মেসেজ)
                        </h5>
                        <p class="text-muted mb-0" style="font-size: 13px;">Configure teaser messages for recharge sheets, top notification banner on calling screen, and quick greeting chips.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Recharge Sheet Teaser Text -->
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                                    <i class="fa-solid fa-heart text-danger me-1"></i> Recharge Modal Teaser Text (রিচার্জ মডেল টেক্সট)
                                </label>
                                <input type="text" name="call_recharge_teaser_text" class="form-control" value="{{ $config['call_recharge_teaser_text'] }}" placeholder="Let's play baby! Recharge and call me..." style="font-size: 14px;">
                                <small class="text-muted">Shown alongside the host's avatar on the gems deposit sheet during calls.</small>
                            </div>

                            <!-- Incoming Call Top Badge Text -->
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                                    <i class="fa-solid fa-bell text-warning me-1"></i> Incoming Calling Top Badge Text
                                </label>
                                <input type="text" name="call_top_badge_text" class="form-control" value="{{ $config['call_top_badge_text'] }}" placeholder="VIDEO NOW! Sexy Girl request video chat!" style="font-size: 14px;">
                                <small class="text-muted">Shown on the incoming call popup overlay.</small>
                            </div>

                            <!-- Free Message Chances -->
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                                    <i class="fa-solid fa-comments text-info me-1"></i> Free Message Chances per Call
                                </label>
                                <div class="input-group">
                                    <input type="number" name="free_message_chances" class="form-control" value="{{ $config['free_message_chances'] ?? 2 }}" min="0" max="20" style="font-weight: 700;">
                                    <span class="input-group-text bg-light">Messages</span>
                                </div>
                                <small class="text-muted">e.g. "You have 2 free message chances"</small>
                            </div>

                            <!-- Quick Icebreaker Message Chips -->
                            <div class="col-12 col-md-8">
                                <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                                    <i class="fa-solid fa-list-check text-success me-1"></i> In-Call Quick Message Chips (1 per line)
                                </label>
                                <textarea name="call_quick_messages" class="form-control" rows="3" placeholder="Be my girlfriend&#10;Hi , what's up babe?&#10;Can we talk privately?" style="font-size: 13px;">{{ is_array($config['call_quick_messages']) ? implode("\n", $config['call_quick_messages']) : $config['call_quick_messages'] }}</textarea>
                                <small class="text-muted">Enter each quick chip option on a new line (e.g. "Be my girlfriend", "Hi , what's up babe?").</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Custom Ringtone & Dial Tone Audio Card -->
                <div class="card mb-4" style="border-radius: 16px; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <h5 class="fw-bold text-dark mb-1">
                            <i class="fa-solid fa-music text-primary me-2"></i> Call Ringtone & Dial Tone Audio Setup (রিংটোন কনফিগারেশন)
                        </h5>
                        <p class="text-muted mb-0" style="font-size: 13px;">Upload custom ringtone audio files (MP3/WAV/OGG) or provide audio URLs for incoming ringtones and outgoing dial tones.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <!-- Incoming Call Ringtone (Receiver Device) -->
                            <div class="col-12 col-md-6">
                                <div class="p-3" style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                                    <label class="form-label fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 13px;">
                                        <i class="fa-solid fa-bell text-warning"></i> Incoming Call Ringtone (রিসিভার রিংটোন)
                                    </label>
                                    <p class="text-muted" style="font-size: 12px; margin-bottom: 8px;">Plays continuously on the receiver's phone when an incoming call arrives.</p>
                                    
                                    <!-- File Upload -->
                                    <div class="mb-2">
                                        <label class="form-label text-muted" style="font-size: 11px;">Upload New Audio File (MP3, WAV):</label>
                                        <input type="file" name="incoming_ringtone_file" accept="audio/*" class="form-control form-control-sm">
                                    </div>

                                    <!-- URL input -->
                                    <div class="mb-2">
                                        <label class="form-label text-muted" style="font-size: 11px;">Or Custom Audio URL:</label>
                                        <input type="url" name="incoming_ringtone_url" value="{{ $config['incoming_ringtone_url'] }}" class="form-control form-control-sm" placeholder="https://...">
                                    </div>

                                    <!-- Audio Preview -->
                                    @if(!empty($config['incoming_ringtone_url']))
                                    <div class="mt-2">
                                        <label class="form-label fw-bold text-dark" style="font-size: 11px;">Preview Ringtone:</label>
                                        <audio controls class="w-100" style="height: 34px;">
                                            <source src="{{ $config['incoming_ringtone_url'] }}">
                                            Your browser does not support audio element.
                                        </audio>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Outgoing Call Dial Tone (Caller Device) -->
                            <div class="col-12 col-md-6">
                                <div class="p-3" style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                                    <label class="form-label fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 13px;">
                                        <i class="fa-solid fa-phone-volume text-primary"></i> Outgoing Call Dial Tone (কলার ডায়ালটোন)
                                    </label>
                                    <p class="text-muted" style="font-size: 12px; margin-bottom: 8px;">Plays continuously on the caller's phone while waiting for the host to answer.</p>
                                    
                                    <!-- File Upload -->
                                    <div class="mb-2">
                                        <label class="form-label text-muted" style="font-size: 11px;">Upload New Audio File (MP3, WAV):</label>
                                        <input type="file" name="outgoing_ringtone_file" accept="audio/*" class="form-control form-control-sm">
                                    </div>

                                    <!-- URL input -->
                                    <div class="mb-2">
                                        <label class="form-label text-muted" style="font-size: 11px;">Or Custom Audio URL:</label>
                                        <input type="url" name="outgoing_ringtone_url" value="{{ $config['outgoing_ringtone_url'] }}" class="form-control form-control-sm" placeholder="https://...">
                                    </div>

                                    <!-- Audio Preview -->
                                    @if(!empty($config['outgoing_ringtone_url']))
                                    <div class="mt-2">
                                        <label class="form-label fw-bold text-dark" style="font-size: 11px;">Preview Dial Tone:</label>
                                        <audio controls class="w-100" style="height: 34px;">
                                            <source src="{{ $config['outgoing_ringtone_url'] }}">
                                            Your browser does not support audio element.
                                        </audio>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Changes Button -->
                <div class="d-flex justify-content-end mb-4">
                    <button type="submit" class="btn btn-primary fw-bold" style="border-radius: 12px; padding: 12px 28px; font-size: 15px; box-shadow: 0 4px 14px rgba(59, 130, 246, 0.4);">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Save Call & Revenue Settings
                    </button>
                </div>
            </div>

            <!-- Right Column: Interactive Live Simulation Widget -->
            <div class="col-12 col-xl-4">
                <!-- Live Revenue Split Simulation -->
                <div class="card sticky-top" style="top: 90px; border-radius: 16px; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 10px 30px rgba(0,0,0,0.05); background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                        <div class="d-flex align-items-center gap-2">
                            <div class="stat-icon-box stat-icon-purple" style="width: 38px; height: 38px; font-size: 16px;">
                                <i class="fa-solid fa-scale-balanced"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Live Split Preview</h5>
                                <small class="text-muted">Real-time revenue simulation</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <!-- Video Call Split Card -->
                        <div class="p-3 mb-3" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-primary" style="font-size: 13px;">
                                    <i class="fa-solid fa-video me-1"></i> Video Call (1 min):
                                </span>
                                <strong id="previewVideoRate" class="text-dark">100 Coins</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted" style="font-size: 12px;">Female Host Gets (<span id="previewHostPercent">50%</span>):</span>
                                <strong id="previewHostVideoCoins" class="text-success">+50 Coins</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted" style="font-size: 12px;">Admin Platform (<span id="previewAdminPercent">50%</span>):</span>
                                <strong id="previewAdminVideoCoins" class="text-purple" style="color: #8b5cf6;">+50 Coins</strong>
                            </div>
                        </div>

                        <!-- Audio Call Split Card -->
                        <div class="p-3 mb-3" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-success" style="font-size: 13px;">
                                    <i class="fa-solid fa-phone me-1"></i> Audio Call (1 min):
                                </span>
                                <strong id="previewAudioRate" class="text-dark">100 Coins</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted" style="font-size: 12px;">Female Host Gets:</span>
                                <strong id="previewHostAudioCoins" class="text-success">+50 Coins</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted" style="font-size: 12px;">Admin Platform:</span>
                                <strong id="previewAdminAudioCoins" class="text-purple" style="color: #8b5cf6;">+50 Coins</strong>
                            </div>
                        </div>

                        <!-- Free Trial Status Card -->
                        <div class="p-3" style="background: rgba(245, 158, 11, 0.08); border-radius: 12px; border: 1px solid rgba(245, 158, 11, 0.2);">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fa-solid fa-gift text-warning"></i>
                                <span class="fw-bold text-dark" style="font-size: 13px;">Free Preview & Calling Workflow:</span>
                            </div>
                            <ul class="text-muted ps-3 mb-0" style="font-size: 12px; line-height: 1.6;">
                                <li>Users get <strong id="previewFreeSecs" class="text-dark">{{ $config['free_call_duration_seconds'] }} seconds</strong> of free preview.</li>
                                <li>Rate is <strong>{{ $config['video_call_rate_per_minute'] }} coins/min</strong> (~1.67 coins/sec).</li>
                                <li>Free Hosts can call any user with <strong>0 balance</strong>.</li>
                                <li>When timer hits 16s and wallet has 0 coins, the in-call video blurs/pauses and opens <strong>Gems Recharge Sheet</strong>.</li>
                                <li>50% of call coins are credited directly to host wallet, and 50% to Admin platform.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputFreeSecs = document.getElementById('inputFreeSecs');
    const inputVideoRate = document.getElementById('inputVideoRate');
    const inputAudioRate = document.getElementById('inputAudioRate');
    const inputHostPercent = document.getElementById('inputHostPercent');
    const inputAdminPercent = document.getElementById('inputAdminPercent');

    const previewVideoRate = document.getElementById('previewVideoRate');
    const previewAudioRate = document.getElementById('previewAudioRate');
    const previewHostPercent = document.getElementById('previewHostPercent');
    const previewAdminPercent = document.getElementById('previewAdminPercent');
    const previewHostVideoCoins = document.getElementById('previewHostVideoCoins');
    const previewAdminVideoCoins = document.getElementById('previewAdminVideoCoins');
    const previewHostAudioCoins = document.getElementById('previewHostAudioCoins');
    const previewAdminAudioCoins = document.getElementById('previewAdminAudioCoins');
    const previewFreeSecs = document.getElementById('previewFreeSecs');
    const hostCoinsHelper = document.getElementById('hostCoinsHelper');
    const adminCoinsHelper = document.getElementById('adminCoinsHelper');

    // Quick select buttons
    document.querySelectorAll('.quick-sec-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (inputFreeSecs) {
                inputFreeSecs.value = this.getAttribute('data-sec');
                updateSimulation();
            }
        });
    });

    function updateSimulation() {
        const vRate = parseInt(inputVideoRate?.value) || 100;
        const aRate = parseInt(inputAudioRate?.value) || 60;
        const hostPct = parseFloat(inputHostPercent?.value) || 50;
        const adminPct = Math.max(0, 100 - hostPct);
        const freeSecs = parseInt(inputFreeSecs?.value) || 10;

        if (inputAdminPercent) inputAdminPercent.value = adminPct.toFixed(1);

        const vHost = Math.round(vRate * (hostPct / 100));
        const vAdmin = vRate - vHost;
        const aHost = Math.round(aRate * (hostPct / 100));
        const aAdmin = aRate - aHost;

        if (previewVideoRate) previewVideoRate.innerText = `${vRate} Coins`;
        if (previewAudioRate) previewAudioRate.innerText = `${aRate} Coins`;
        if (previewHostPercent) previewHostPercent.innerText = `${hostPct}%`;
        if (previewAdminPercent) previewAdminPercent.innerText = `${adminPct}%`;
        if (previewHostVideoCoins) previewHostVideoCoins.innerText = `+${vHost} Coins`;
        if (previewAdminVideoCoins) previewAdminVideoCoins.innerText = `+${vAdmin} Coins`;
        if (previewHostAudioCoins) previewHostAudioCoins.innerText = `+${aHost} Coins`;
        if (previewAdminAudioCoins) previewAdminAudioCoins.innerText = `+${aAdmin} Coins`;
        if (previewFreeSecs) previewFreeSecs.innerText = `${freeSecs} seconds`;

        if (hostCoinsHelper) hostCoinsHelper.innerText = `Host receives ${vHost} coins for every ${vRate} coins spent on video calls.`;
        if (adminCoinsHelper) adminCoinsHelper.innerText = `Platform keeps ${vAdmin} coins for every ${vRate} coins spent on video calls.`;
    }

    [inputFreeSecs, inputVideoRate, inputAudioRate, inputHostPercent].forEach(el => {
        el?.addEventListener('input', updateSimulation);
    });

    updateSimulation();
});
</script>
@endpush
