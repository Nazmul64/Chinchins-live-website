@extends('layouts.admin')

@section('title', 'Mobile App Debugging, Diagnostics & Error Logs')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <a href="{{ route('admin.settings.index') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Settings</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-danger fw-bold" style="font-size: 13px;">App Debug & Diagnostics</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-bug text-danger"></i>
                <span>App Debugging, Live HUD & Real-Time Error Logs</span>
            </h1>
            <p class="page-subtitle">Remotely toggle debugging mode in Flutter apps, monitor real-time WebRTC metrics (FPS, Bitrate, Latency), inspect error logs, and manage security shields.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- 1-Click Debugging Toggle Button -->
            <form action="{{ route('admin.settings.debug.toggle') }}" method="POST">
                @csrf
                @php
                    $isDebugActive = (bool) ($merged['debug_mode_enabled'] ?? false);
                @endphp
                <button type="submit" class="btn {{ $isDebugActive ? 'btn-danger' : 'btn-outline-danger' }} px-4 py-2 rounded-pill fw-bold shadow-sm d-flex align-items-center gap-2">
                    <i class="fa-solid {{ $isDebugActive ? 'fa-toggle-on' : 'fa-toggle-off' }} fs-5"></i>
                    <span>{{ $isDebugActive ? 'DEBUG MODE: ACTIVE (CLICK TO TURN OFF)' : 'DEBUG MODE: OFF (CLICK TO TURN ON)' }}</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Status Overview Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center gap-3" style="background: var(--card-bg, #ffffff);">
                <div class="stat-icon-box" style="width: 48px; height: 48px; background: {{ $isDebugActive ? 'rgba(239,68,68,0.15)' : 'rgba(100,116,139,0.15)' }}; color: {{ $isDebugActive ? '#ef4444' : '#64748b' }}; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fa-solid fa-bug"></i>
                </div>
                <div>
                    <div class="text-muted small">App Debugging HUD</div>
                    <div class="fw-bold fs-5 {{ $isDebugActive ? 'text-danger' : 'text-muted' }}">
                        {{ $isDebugActive ? 'LIVE ACTIVE' : 'DISABLED' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center gap-3" style="background: var(--card-bg, #ffffff);">
                <div class="stat-icon-box" style="width: 48px; height: 48px; background: rgba(59,130,246,0.15); color: #3b82f6; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fa-solid fa-video"></i>
                </div>
                <div>
                    <div class="text-muted small">Active Calls Right Now</div>
                    <div class="fw-bold fs-5 text-primary">{{ $activeCallsCount }} Calls Live</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center gap-3" style="background: var(--card-bg, #ffffff);">
                <div class="stat-icon-box" style="width: 48px; height: 48px; background: rgba(16,185,129,0.15); color: #10b981; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <div class="text-muted small">Online Connected Users</div>
                    <div class="fw-bold fs-5 text-success">{{ $onlineUsersCount }} Users Online</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center gap-3" style="background: var(--card-bg, #ffffff);">
                <div class="stat-icon-box" style="width: 48px; height: 48px; background: rgba(139,92,246,0.15); color: #8b5cf6; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
                <div>
                    <div class="text-muted small">System Log Size</div>
                    <div class="fw-bold fs-5 text-purple">{{ $logFileSize }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Config and Log Inspector Grid -->
    <div class="row g-4">
        <!-- Left Column: Remote Feature & Security Controls -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background: var(--card-bg, #ffffff);">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0">
                        <i class="fa-solid fa-sliders text-primary me-2"></i> Remote Feature Switches
                    </h5>
                    <span class="badge bg-light text-dark border">Instant Sync</span>
                </div>
                <p class="text-muted small mb-4">These switches take effect in Flutter apps immediately without requiring APK/iOS rebuilds.</p>

                <form action="{{ route('admin.settings.debug.update') }}" method="POST">
                    @csrf

                    <!-- Debug Mode Switch -->
                    <div class="p-3 rounded-3 mb-3 border d-flex justify-content-between align-items-center" style="background: {{ ($merged['debug_mode_enabled'] ?? '0') == '1' ? 'rgba(239,68,68,0.06)' : '#fafafa' }};">
                        <div>
                            <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="fa-solid fa-bug text-danger"></i>
                                <span>In-App Debug HUD Overlay</span>
                            </div>
                            <div class="text-muted small">Displays real-time FPS, WebRTC Bitrate, and API latency HUD in Flutter apps.</div>
                        </div>
                        <div class="form-check form-switch ms-3">
                            <input class="form-check-input" type="checkbox" role="switch" name="debug_mode_enabled" value="1" id="switchDebugMode" {{ ($merged['debug_mode_enabled'] ?? '0') == '1' ? 'checked' : '' }} style="width: 2.8em; height: 1.5em;">
                        </div>
                    </div>

                    <!-- Screenshot Protection Switch -->
                    <div class="p-3 rounded-3 mb-3 border d-flex justify-content-between align-items-center" style="background: #fafafa;">
                        <div>
                            <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="fa-solid fa-camera-slash text-warning"></i>
                                <span>Screenshot Protection (FLAG_SECURE)</span>
                            </div>
                            <div class="text-muted small">Blocks screenshots and screen grabs on video call and sensitive screens.</div>
                        </div>
                        <div class="form-check form-switch ms-3">
                            <input class="form-check-input" type="checkbox" role="switch" name="screenshot_protection_enabled" value="1" id="switchScreenshot" {{ ($merged['screenshot_protection_enabled'] ?? '1') == '1' ? 'checked' : '' }} style="width: 2.8em; height: 1.5em;">
                        </div>
                    </div>

                    <!-- Screen Recording Protection Switch -->
                    <div class="p-3 rounded-3 mb-3 border d-flex justify-content-between align-items-center" style="background: #fafafa;">
                        <div>
                            <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="fa-solid fa-video-slash text-danger"></i>
                                <span>Screen Recording Shield</span>
                            </div>
                            <div class="text-muted small">Prevents screen recorders from capturing live video call streams.</div>
                        </div>
                        <div class="form-check form-switch ms-3">
                            <input class="form-check-input" type="checkbox" role="switch" name="screen_recording_protection_enabled" value="1" id="switchRecording" {{ ($merged['screen_recording_protection_enabled'] ?? '1') == '1' ? 'checked' : '' }} style="width: 2.8em; height: 1.5em;">
                        </div>
                    </div>

                    <!-- TikTok Camera Filters Switch -->
                    <div class="p-3 rounded-3 mb-3 border d-flex justify-content-between align-items-center" style="background: #fafafa;">
                        <div>
                            <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="fa-solid fa-wand-magic-sparkles text-pink" style="color: #ec4899;"></i>
                                <span>TikTok-Style Camera Filters</span>
                            </div>
                            <div class="text-muted small">Enables 8 real-time beauty shaders & color filters during video calls.</div>
                        </div>
                        <div class="form-check form-switch ms-3">
                            <input class="form-check-input" type="checkbox" role="switch" name="camera_filters_enabled" value="1" id="switchFilters" {{ ($merged['camera_filters_enabled'] ?? '1') == '1' ? 'checked' : '' }} style="width: 2.8em; height: 1.5em;">
                        </div>
                    </div>

                    <!-- Call Minimization Switch -->
                    <div class="p-3 rounded-3 mb-4 border d-flex justify-content-between align-items-center" style="background: #fafafa;">
                        <div>
                            <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="fa-solid fa-window-minimize text-info"></i>
                                <span>Back Button Call Minimize (PiP)</span>
                            </div>
                            <div class="text-muted small">Minimizes call to floating overlay instead of disconnecting.</div>
                        </div>
                        <div class="form-check form-switch ms-3">
                            <input class="form-check-input" type="checkbox" role="switch" name="call_minimize_enabled" value="1" id="switchMinimize" {{ ($merged['call_minimize_enabled'] ?? '1') == '1' ? 'checked' : '' }} style="width: 2.8em; height: 1.5em;">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-bold">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Save Remote Settings
                    </button>
                </form>
            </div>

            <!-- In-App HUD Preview Mockup Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 text-white" style="background: #0f172a;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
                        <i class="fa-solid fa-mobile-screen-button text-cyan"></i>
                        <span>Mobile App HUD Preview</span>
                    </h6>
                    <span class="badge bg-danger rounded-pill">Visual Guide</span>
                </div>
                <p class="text-muted small mb-3">When Debug Mode is ON, users/testers will see this diagnostic HUD at the top-left of their app:</p>

                <!-- Mockup HUD Box -->
                <div class="p-3 rounded-3 border border-success" style="background: rgba(0,0,0,0.85); font-family: monospace; font-size: 11px;">
                    <div class="d-flex align-items-center gap-2 mb-2 text-success fw-bold">
                        <span style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; display: inline-block;"></span>
                        <span>DEBUG MODE (ADMIN ON)</span>
                    </div>
                    <div class="text-white">WebRTC: 30 FPS | 1,250 kbps (HD)</div>
                    <div class="text-white">Latency: 42 ms | Packet Loss: 0.0%</div>
                    <div class="text-info">API Response: 118 ms (Ultra-fast)</div>
                    <div class="text-warning">PiP Minimization: Enabled</div>
                </div>
            </div>
        </div>

        <!-- Right Column: Real-Time Live Error Logs & Diagnostics Viewer -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4" style="background: var(--card-bg, #ffffff);">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <h5 class="fw-bold mb-0">
                            <i class="fa-solid fa-terminal text-dark me-2"></i> Live Error Logs & App Exceptions
                        </h5>
                        <div class="text-muted small">Inspect real-time exceptions, WebRTC handshakes, and API request diagnostics.</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="window.location.reload();">
                            <i class="fa-solid fa-rotate me-1"></i> Refresh
                        </button>
                        <form action="{{ route('admin.settings.debug.clear-logs') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear system error logs?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                <i class="fa-solid fa-trash-can me-1"></i> Clear Logs
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Terminal Log Container -->
                <div class="log-terminal-container p-3 rounded-4" style="background: #090d16; color: #e2e8f0; font-family: 'Consolas', 'Courier New', monospace; font-size: 12px; height: 560px; overflow-y: auto; border: 1px solid #1e293b; line-height: 1.6;">
                    @if(empty($logs))
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center text-muted">
                            <i class="fa-solid fa-circle-check fs-1 text-success mb-3"></i>
                            <div class="fw-bold fs-6 text-white">System is completely healthy!</div>
                            <div>No unhandled errors or crash logs recorded in system log file.</div>
                        </div>
                    @else
                        @foreach($logs as $index => $logLine)
                            @if(trim($logLine) !== '')
                                @php
                                    $isError = str_contains($logLine, '.ERROR:') || str_contains($logLine, 'local.ERROR') || str_contains($logLine, 'Exception') || str_contains($logLine, 'Stack trace');
                                    $isWarning = str_contains($logLine, '.WARNING:') || str_contains($logLine, 'local.WARNING');
                                    $isInfo = str_contains($logLine, '.INFO:') || str_contains($logLine, 'local.INFO');
                                @endphp
                                <div class="log-line mb-1" style="color: {{ $isError ? '#f87171' : ($isWarning ? '#fbbf24' : ($isInfo ? '#60a5fa' : '#cbd5e1')) }}; word-break: break-all;">
                                    <span class="text-muted me-2" style="font-size: 10px;">[{{ $index + 1 }}]</span>
                                    {!! htmlspecialchars($logLine) !!}
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 text-muted small">
                    <div>Showing last {{ count($logs) }} log entries (Newest first)</div>
                    <div>Log Path: <code>storage/logs/laravel.log</code> ({{ $logFileSize }})</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.08); opacity: 0.85; }
    100% { transform: scale(1); opacity: 1; }
}
</style>
@endsection
