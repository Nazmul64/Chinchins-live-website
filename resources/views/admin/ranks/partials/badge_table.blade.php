@if($badges->isEmpty())
    <div class="text-center py-5">
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; font-size: 28px;">
            <i class="fa-solid fa-award"></i>
        </div>
        <h5 class="fw-bold text-dark mb-1">No {{ $period }} Badges Configured</h5>
        <p class="text-muted mb-3" style="font-size: 13px;">Add rank badges for top positions (1st, 2nd, 3rd) for {{ strtolower($period) }} leaderboards.</p>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadBadgeModal" style="border-radius: 8px;">
            <i class="fa-solid fa-plus me-1"></i> Add {{ $period }} Badge
        </button>
    </div>
@else
    <div class="table-responsive">
        <table class="table align-middle table-hover mb-0">
            <thead class="table-light">
                <tr style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    <th style="width: 80px;">Rank</th>
                    <th style="width: 90px;">Badge Icon</th>
                    <th style="width: 90px;">Avatar Frame</th>
                    <th>Badge Details</th>
                    <th>Category</th>
                    <th>Min Required Coins</th>
                    <th>Status</th>
                    <th class="text-end" style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($badges as $badge)
                    <tr>
                        <td>
                            @if($badge->rank_position == 1)
                                <span class="badge" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; font-size: 13px; font-weight: 700; padding: 6px 10px; border-radius: 8px;">
                                    🥇 #1
                                </span>
                            @elseif($badge->rank_position == 2)
                                <span class="badge" style="background: linear-gradient(135deg, #94a3b8, #64748b); color: #fff; font-size: 13px; font-weight: 700; padding: 6px 10px; border-radius: 8px;">
                                    🥈 #2
                                </span>
                            @elseif($badge->rank_position == 3)
                                <span class="badge" style="background: linear-gradient(135deg, #b45309, #78350f); color: #fff; font-size: 13px; font-weight: 700; padding: 6px 10px; border-radius: 8px;">
                                    🥉 #3
                                </span>
                            @else
                                <span class="badge bg-light text-dark border fw-bold" style="font-size: 12px; padding: 5px 8px;">
                                    #{{ $badge->rank_position }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="p-1 rounded bg-light border d-inline-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                @if($badge->badge_icon)
                                    <img src="{{ asset($badge->badge_icon) }}" alt="{{ $badge->badge_name }}" style="max-width: 48px; max-height: 48px; object-fit: contain;">
                                @else
                                    <i class="fa-solid fa-image text-muted"></i>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="p-1 rounded bg-light border d-inline-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                @if($badge->avatar_frame)
                                    <img src="{{ asset($badge->avatar_frame) }}" alt="Frame" style="max-width: 48px; max-height: 48px; object-fit: contain;">
                                @else
                                    <span class="text-muted" style="font-size: 10px;">None</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark" style="font-size: 14px;">{{ $badge->badge_name }}</div>
                            <small class="text-muted" style="font-size: 11px;">
                                ID: #{{ $badge->id }} &bull; Path: <code>{{ $badge->badge_icon }}</code>
                            </small>
                        </td>
                        <td>
                            @if($badge->category === 'rich')
                                <span class="badge rounded-pill" style="background: rgba(245, 158, 11, 0.15); color: #d97706; font-size: 12px; padding: 5px 10px;">
                                    <i class="fa-solid fa-coins me-1"></i> Rich (Gifter)
                                </span>
                            @else
                                <span class="badge rounded-pill" style="background: rgba(236, 72, 153, 0.15); color: #db2777; font-size: 12px; padding: 5px 10px;">
                                    <i class="fa-solid fa-heart me-1"></i> Charm (Host)
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-bold text-dark">{{ number_format($badge->min_required_coins) }}</span>
                            <small class="text-muted">Coins</small>
                        </td>
                        <td>
                            <form action="{{ route('admin.rank-badges.toggle', $badge->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $badge->is_active ? 'btn-success' : 'btn-outline-secondary' }}" style="border-radius: 20px; font-size: 11px; padding: 3px 10px;">
                                    <i class="fa-solid {{ $badge->is_active ? 'fa-circle-check' : 'fa-circle-pause' }} me-1"></i>
                                    {{ $badge->is_active ? 'Active' : 'Disabled' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editBadgeModal{{ $badge->id }}" title="Edit Badge" style="border-radius: 8px;">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <form action="{{ route('admin.rank-badges.destroy', $badge->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this {{ $period }} rank badge?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 8px;" title="Delete Badge">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>

                            <!-- Edit Badge Modal -->
                            <div class="modal fade text-start" id="editBadgeModal{{ $badge->id }}" tabindex="-1" aria-labelledby="editBadgeModalLabel{{ $badge->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content border-0 shadow" style="border-radius: 14px; overflow: hidden;">
                                        <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e293b, #0f172a); border-bottom: none;">
                                            <h5 class="modal-title fw-bold" id="editBadgeModalLabel{{ $badge->id }}">
                                                <i class="fa-solid fa-pen-to-square text-warning me-2"></i> Edit {{ ucfirst($badge->period_type) }} Rank #{{ $badge->rank_position }} Badge
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('admin.rank-badges.update', $badge->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body p-4">
                                                <div class="row g-3">
                                                    <!-- Badge Name -->
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">Badge Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="badge_name" class="form-control" value="{{ $badge->badge_name }}" required style="border-radius: 8px;">
                                                        <small class="text-muted">Descriptive title shown in app leaderboards.</small>
                                                    </div>

                                                    <!-- Period Type -->
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">Period Type <span class="text-danger">*</span></label>
                                                        <select name="period_type" class="form-select" required style="border-radius: 8px;">
                                                            <option value="daily" {{ $badge->period_type === 'daily' ? 'selected' : '' }}>Daily (প্রতিদিনের র্যাংক)</option>
                                                            <option value="weekly" {{ $badge->period_type === 'weekly' ? 'selected' : '' }}>Weekly (সাপ্তাহিক র্যাংক)</option>
                                                            <option value="monthly" {{ $badge->period_type === 'monthly' ? 'selected' : '' }}>Monthly (মাসিক র্যাংক)</option>
                                                        </select>
                                                    </div>

                                                    <!-- Category -->
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">Category <span class="text-danger">*</span></label>
                                                        <select name="category" class="form-select" required style="border-radius: 8px;">
                                                            <option value="rich" {{ $badge->category === 'rich' ? 'selected' : '' }}>Rich (সর্বোচ্চ খরচকারী / Gifter)</option>
                                                            <option value="charm" {{ $badge->category === 'charm' ? 'selected' : '' }}>Charm (সর্বোচ্চ আকর্ষণীয় / Host Earner)</option>
                                                        </select>
                                                    </div>

                                                    <!-- Rank Position -->
                                                    <div class="col-md-3">
                                                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">Rank Position <span class="text-danger">*</span></label>
                                                        <input type="number" name="rank_position" min="1" max="100" class="form-control" value="{{ $badge->rank_position }}" required style="border-radius: 8px;">
                                                        <small class="text-muted">1 = 1st, 2 = 2nd, etc.</small>
                                                    </div>

                                                    <!-- Min Required Coins -->
                                                    <div class="col-md-3">
                                                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">Min Coins <span class="text-danger">*</span></label>
                                                        <input type="number" name="min_required_coins" min="0" class="form-control" value="{{ $badge->min_required_coins }}" required style="border-radius: 8px;">
                                                        <small class="text-muted">Threshold to earn</small>
                                                    </div>

                                                    <!-- Status Switch -->
                                                    <div class="col-12">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="badgeActiveSwitch{{ $badge->id }}" {{ $badge->is_active ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-semibold text-dark" for="badgeActiveSwitch{{ $badge->id }}" style="font-size: 13px;">Active & Visible in Flutter App</label>
                                                        </div>
                                                    </div>

                                                    <!-- Badge Icon File -->
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                                                            <i class="fa-solid fa-image text-primary me-1"></i> Change Badge Icon (Max 50MB)
                                                        </label>
                                                        <input type="file" name="badge_icon" class="form-control" accept="image/png,image/webp,image/gif,image/jpeg,image/svg+xml" style="border-radius: 8px;" onchange="previewFile(this, 'edit_badge_preview_{{ $badge->id }}')">
                                                        <small class="text-muted d-block mt-1">Leave empty to keep current icon. (PNG/WEBP/GIF up to 50MB)</small>
                                                        <div class="mt-2 text-center p-2 border rounded" style="background: #f8fafc; height: 95px; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                                                            @if($badge->badge_icon)
                                                                <img id="edit_badge_preview_{{ $badge->id }}" src="{{ asset($badge->badge_icon) }}" alt="Badge Preview" style="max-height: 70px; max-width: 100%;">
                                                                <small class="text-muted mt-1" style="font-size: 10px;">Current Icon</small>
                                                            @else
                                                                <img id="edit_badge_preview_{{ $badge->id }}" src="" alt="Badge Preview" style="max-height: 70px; max-width: 100%; display: none;">
                                                                <span id="edit_badge_preview_{{ $badge->id }}_placeholder" class="text-muted" style="font-size: 12px;">No badge icon</span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <!-- Avatar Frame File -->
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">
                                                            <i class="fa-solid fa-circle-notch text-warning me-1"></i> Change Avatar Frame (Max 50MB)
                                                        </label>
                                                        <input type="file" name="avatar_frame" class="form-control" accept="image/png,image/webp,image/gif,image/jpeg,image/svg+xml" style="border-radius: 8px;" onchange="previewFile(this, 'edit_frame_preview_{{ $badge->id }}')">
                                                        <small class="text-muted d-block mt-1">Leave empty to keep current frame. (PNG/WEBP/GIF up to 50MB)</small>
                                                        <div class="mt-2 text-center p-2 border rounded" style="background: #f8fafc; height: 95px; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                                                            @if($badge->avatar_frame)
                                                                <img id="edit_frame_preview_{{ $badge->id }}" src="{{ asset($badge->avatar_frame) }}" alt="Frame Preview" style="max-height: 70px; max-width: 100%;">
                                                                <small class="text-muted mt-1" style="font-size: 10px;">Current Frame</small>
                                                            @else
                                                                <img id="edit_frame_preview_{{ $badge->id }}" src="" alt="Frame Preview" style="max-height: 70px; max-width: 100%; display: none;">
                                                                <span id="edit_frame_preview_{{ $badge->id }}_placeholder" class="text-muted" style="font-size: 12px;">No frame assigned (Optional)</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light" style="border-top: 1px solid #e2e8f0;">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                                                <button type="submit" class="btn btn-primary" style="border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #3b82f6, #2563eb); border: none;">
                                                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
