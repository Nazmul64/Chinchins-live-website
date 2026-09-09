@extends('layouts.admin')

@section('title', 'Party Rooms System Settings')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <a href="{{ route('admin.party-rooms.index') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Party Rooms</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Settings</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-sliders text-primary"></i>
                <span>Party Room & Revenue Configuration</span>
            </h1>
            <p class="page-subtitle">Configure default per-minute coin billing, 50/50 Host & Admin revenue split percentages, stage seat limits, and default announcements.</p>
        </div>
        <div>
            <a href="{{ route('admin.party-rooms.index') }}" class="btn btn-light" style="border-radius: 10px; font-weight: 600; font-size: 13px; padding: 10px 16px;">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Rooms
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert" style="border-radius: 12px;">
            <i class="fa-solid fa-circle-check fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <form action="{{ route('admin.party-rooms.settings.update') }}" method="POST">
                        @csrf

                        <!-- Enable / Disable Switch -->
                        <div class="d-flex justify-content-between align-items-center p-3 mb-4 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <div>
                                <h6 class="fw-bold mb-1">Enable Party Rooms Feature</h6>
                                <p class="text-muted mb-0" style="font-size: 12px;">Toggle whether users can host and join live voice and video party rooms.</p>
                            </div>
                            <div class="form-check form-switch fs-4">
                                <input class="form-check-input" type="checkbox" name="is_party_room_enabled" value="1" {{ $settings->is_party_room_enabled ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold" style="font-size: 13px;">Default Voice Party Rate (Coins/Min)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fa-solid fa-microphone text-primary"></i></span>
                                    <input type="number" name="default_voice_rate_per_minute" class="form-control" value="{{ $settings->default_voice_rate_per_minute }}" min="0" required>
                                    <span class="input-group-text">coins</span>
                                </div>
                                <small class="text-muted">Default billing for voice party seat participants (e.g. 100).</small>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold" style="font-size: 13px;">Default Video Party Rate (Coins/Min)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fa-solid fa-video text-danger"></i></span>
                                    <input type="number" name="default_video_rate_per_minute" class="form-control" value="{{ $settings->default_video_rate_per_minute }}" min="0" required>
                                    <span class="input-group-text">coins</span>
                                </div>
                                <small class="text-muted">Default billing for video party seat participants (e.g. 100).</small>
                            </div>
                        </div>

                        <!-- 50/50 Revenue Split -->
                        <div class="p-3 mb-4 rounded-3" style="background: #fffbeb; border: 1px solid #fde68a;">
                            <h6 class="fw-bold text-warning-emphasis mb-3">
                                <i class="fa-solid fa-scale-balanced me-1"></i> Revenue Commission Split (50% Host / 50% Admin)
                            </h6>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold" style="font-size: 13px;">Host Revenue Share (%)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.1" name="host_commission_percentage" class="form-control" value="{{ $settings->host_commission_percentage }}" min="0" max="100" required>
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <small class="text-muted">Percentage of billed coins credited directly to the room host wallet.</small>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold" style="font-size: 13px;">Admin Platform Share (%)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.1" name="admin_commission_percentage" class="form-control" value="{{ $settings->admin_commission_percentage }}" min="0" max="100" required>
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <small class="text-muted">Percentage of billed coins credited to platform admin revenue.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold" style="font-size: 13px;">Default Max Stage Seats</label>
                                <input type="number" name="max_guests_per_room" class="form-control" value="{{ $settings->max_guests_per_room }}" min="4" max="12" required>
                                <small class="text-muted">Default maximum stage seats (1 Host + 9 Guests = 10 seats).</small>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold" style="font-size: 13px;">Default Room Announcement</label>
                                <input type="text" name="default_announcement" class="form-control" value="{{ $settings->default_announcement }}">
                                <small class="text-muted">Default banner message displayed in all new party rooms.</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary px-4" style="border-radius: 10px; font-weight: 600; padding: 10px 24px;">
                                <i class="fa-solid fa-save me-1"></i> Save Configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-circle-info text-primary me-2"></i> How Party Room Billing Works</h6>
                </div>
                <div class="card-body p-4 pt-0" style="font-size: 13px; line-height: 1.6; color: #475569;">
                    <p><strong>1. Free Audience:</strong> Any user can join any public party room as audience/listener for free.</p>
                    <p><strong>2. Stage Call Billing:</strong> When a user takes a stage seat (Seat 2..10) in an Audio or Video room, they are billed at the room rate (e.g. 100 coins/min).</p>
                    <p><strong>3. 50/50 Split Rule:</strong> Every minute, 50 coins are transferred to the Host's wallet balance, and 50 coins are logged to Admin revenue.</p>
                    <p><strong>4. Auto-Eviction:</strong> If a guest's coin balance runs below 100 coins, the system automatically steps them down from the seat back to the audience.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
