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
                        <td class="text-end">
                            <form action="{{ route('admin.rank-badges.destroy', $badge->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this {{ $period }} rank badge?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 8px;" title="Delete Badge">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
