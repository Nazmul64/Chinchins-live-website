@extends('layouts.admin')

@section('title', 'Daily Check-in Rewards Management')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Banner -->
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">Daily Rewards</span>
            </div>
            <h1 class="page-title mb-1">
                <i class="fa-solid fa-calendar-check text-warning me-2"></i>
                <span>7-Day Check-in & Claim Rewards Management</span>
            </h1>
            <p class="page-subtitle mb-0">Configure daily reward coins, upload custom icons for Day 1–7, and monitor user 12-hour cooldown claims.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold" style="font-size: 13px;">
                <i class="fa-solid fa-clock me-1"></i> 12-Hour Cooldown Logic Active
            </span>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3" style="border-left: 4px solid #3b82f6 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Total User Claims</span>
                        <h3 class="fw-bolder mt-1 mb-0">{{ number_format($totalClaims) }}</h3>
                    </div>
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(59, 130, 246, 0.15); color: #3b82f6; font-size: 20px;">
                        <i class="fa-solid fa-gift"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3" style="border-left: 4px solid #10b981 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Today's Claims</span>
                        <h3 class="fw-bolder mt-1 mb-0 text-success">{{ number_format($todayClaims) }}</h3>
                    </div>
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(16, 185, 129, 0.15); color: #10b981; font-size: 20px;">
                        <i class="fa-solid fa-calendar-day"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3" style="border-left: 4px solid #f59e0b !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Total Coins Distributed</span>
                        <h3 class="fw-bolder mt-1 mb-0 text-warning">{{ number_format($totalCoinsDistributed) }}</h3>
                    </div>
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(245, 158, 11, 0.15); color: #f59e0b; font-size: 20px;">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3" style="border-left: 4px solid #8b5cf6 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Active Days</span>
                        <h3 class="fw-bolder mt-1 mb-0 text-primary">{{ $rewards->where('is_active', true)->count() }} / 7</h3>
                    </div>
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(139, 92, 246, 0.15); color: #8b5cf6; font-size: 20px;">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 7-Day Rewards Visual Cards Grid -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-0"><i class="fa-solid fa-boxes-stacked text-primary me-2"></i>7-Day Reward Schedule Configuration</h5>
                <small class="text-muted">Click on any day card to edit coins or upload custom asset.</small>
            </div>
        </div>
        <div class="card-body px-4 pb-4 pt-2">
            <div class="row g-3">
                @foreach($rewards as $reward)
                <div class="col-12 col-sm-6 col-md-4 col-xl">
                    <div class="card h-100 rounded-4 text-center p-3 position-relative border transition-all" 
                         style="background: {{ $reward->day_number == 7 ? 'linear-gradient(135deg, #FFFDE7 0%, #FFF8E1 100%)' : '#FAFAFA' }}; border: {{ $reward->day_number == 7 ? '2px solid #FFD54F' : '1px solid #E5E7EB' }} !important; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                        
                        <!-- Day Badge -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge {{ $reward->day_number == 7 ? 'bg-warning text-dark' : 'bg-primary' }} rounded-pill px-2 py-1" style="font-size: 11px;">
                                Day {{ $reward->day_number }} {{ $reward->day_number == 7 ? '⭐ Grand' : '' }}
                            </span>
                            <span class="badge {{ $reward->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} rounded-pill px-2 py-1" style="font-size: 10px;">
                                {{ $reward->is_active ? 'Active' : 'Disabled' }}
                            </span>
                        </div>

                        <!-- Icon preview -->
                        <div class="my-3 d-flex justify-content-center align-items-center" style="height: 80px;">
                            <img src="{{ $reward->icon_image_url }}" 
                                 alt="Day {{ $reward->day_number }}" 
                                 style="max-height: 75px; max-width: 75px; object-fit: contain; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));"
                                 onerror="this.src='/uploads/claim/day_{{ $reward->day_number }}.svg'">
                        </div>

                        <!-- Reward Coins -->
                        <div class="fw-bolder fs-5 text-warning mb-1">
                            +{{ $reward->reward_coins }} <small class="text-muted fs-6" style="font-size: 12px !important;">Coins</small>
                        </div>

                        <!-- Edit Button -->
                        <button type="button" 
                                class="btn btn-sm btn-outline-primary rounded-pill mt-2 w-100 fw-semibold"
                                data-bs-toggle="modal" 
                                data-bs-target="#editModalDay{{ $reward->id }}">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Configure
                        </button>
                    </div>
                </div>

                <!-- Edit Modal for Day {{ $reward->day_number }} -->
                <div class="modal fade" id="editModalDay{{ $reward->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4 border-0 shadow">
                            <form action="{{ route('admin.daily-rewards.update', $reward->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-header border-0 pb-0 pt-4 px-4">
                                    <h5 class="modal-title fw-bold">
                                        <i class="fa-solid fa-gift text-warning me-2"></i>Configure Day {{ $reward->day_number }} Reward
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <!-- Current Preview -->
                                    <div class="text-center p-3 bg-light rounded-3 mb-3">
                                        <img src="{{ $reward->icon_image_url }}" 
                                             alt="Preview" 
                                             style="height: 70px; object-fit: contain;"
                                             id="previewImg{{ $reward->id }}"
                                             onerror="this.src='/uploads/claim/day_{{ $reward->day_number }}.svg'">
                                        <div class="small text-muted mt-2">Current Icon Image</div>
                                    </div>

                                    <!-- Reward Coins -->
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-muted">Reward Coins Amount (+)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="fa-solid fa-coins text-warning"></i></span>
                                            <input type="number" 
                                                   name="reward_coins" 
                                                   class="form-control" 
                                                   value="{{ $reward->reward_coins }}" 
                                                   required 
                                                   min="1">
                                        </div>
                                    </div>

                                    <!-- Upload Image -->
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-muted">Upload Custom Icon / Image</label>
                                        <input type="file" 
                                               name="image" 
                                               class="form-control" 
                                               accept="image/*"
                                               onchange="previewImage(this, 'previewImg{{ $reward->id }}')">
                                        <small class="text-muted" style="font-size: 11px;">Saved to <code>public/uploads/claim/</code>. Supports PNG, JPG, WebP, SVG (Max: 4MB).</small>
                                    </div>

                                    <!-- Active Toggle -->
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="is_active" 
                                               value="1" 
                                               id="isActiveSwitch{{ $reward->id }}" 
                                               {{ $reward->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="isActiveSwitch{{ $reward->id }}">Active in Daily Claim</label>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary rounded-3 px-4">
                                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Claim Logs -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-list-check text-success me-2"></i>Recent User Daily Claim History</h5>
            <span class="badge bg-light text-muted fw-bold">Live 12-Hour Interval Stream</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">User</th>
                            <th>Account ID</th>
                            <th>Day Claimed</th>
                            <th>Coins Awarded</th>
                            <th>Claimed At</th>
                            <th class="pe-4">Next Allowed Claim</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentClaims as $claim)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $claim->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($claim->user->name ?? 'User') }}" 
                                         class="rounded-circle" 
                                         style="width: 32px; height: 32px; object-fit: cover;">
                                    <span class="fw-bold">{{ $claim->user->name ?? 'User #' . $claim->user_id }}</span>
                                </div>
                            </td>
                            <td><code>{{ $claim->user->account_id ?? $claim->user_id }}</code></td>
                            <td>
                                <span class="badge bg-primary rounded-pill px-2.5 py-1">Day {{ $claim->day_claimed }}</span>
                            </td>
                            <td class="fw-bold text-warning">+{{ number_format($claim->coins_awarded) }} Coins</td>
                            <td class="text-muted">{{ $claim->claimed_at->format('d M Y, h:i A') }}</td>
                            <td class="pe-4 text-success fw-semibold">
                                {{ $claim->claimed_at->addHours(12)->diffForHumans() }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-inbox fs-3 d-block mb-2 opacity-50"></i>
                                No user claim logs found yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById(previewId).src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
