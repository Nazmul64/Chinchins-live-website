@extends('layouts.admin')

@section('title', 'User Bag Inventories & Allocations')

@section('content')
<div class="container-fluid px-0">
    <div class="premium-page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none" style="font-size: 13px;">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <a href="{{ route('admin.my-bag.index') }}" class="text-muted text-decoration-none" style="font-size: 13px;">My Bag</a>
                <i class="fa-solid fa-chevron-right text-muted" style="font-size: 10px;"></i>
                <span class="text-primary fw-bold" style="font-size: 13px;">User Inventories</span>
            </div>
            <h1 class="page-title">
                <i class="fa-solid fa-boxes-stacked" style="color: #a855f7;"></i>
                <span>User Bag Inventory Ledger</span>
            </h1>
            <p class="page-subtitle">Track all bag items owned, equipped, or redeemed by users across the live platform.</p>
        </div>
        <a href="{{ route('admin.my-bag.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold" style="font-size: 13px;">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Bag Items
        </a>
    </div>

    <!-- Inventory Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted fw-bold" style="font-size: 12px; text-transform: uppercase;">
                    <tr>
                        <th class="ps-4">User</th>
                        <th>Bag Item</th>
                        <th>Category</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th>Equipped</th>
                        <th>Source</th>
                        <th>Expires / Remaining</th>
                        <th class="text-end pe-4">Granted At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($userItems as $uItem)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $uItem->user?->avatar_url ?? asset('assets/images/defaults/avatar.png') }}" class="rounded-circle" width="36" height="36" style="object-fit: cover;">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $uItem->user?->display_name ?? 'Unknown User' }}</div>
                                        <small class="text-muted font-monospace">ID: {{ $uItem->user?->display_id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width: 38px; height: 38px; border-radius: 8px; background: #181428; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                        <img src="{{ $uItem->bagItem?->icon_full_url }}" style="max-width: 80%; max-height: 80%; object-fit: contain;">
                                    </div>
                                    <div class="fw-semibold text-dark">{{ $uItem->bagItem?->name ?? 'Deleted Item' }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 11px;">
                                    {{ $uItem->bagItem?->category_name }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold">x{{ $uItem->quantity }}</span>
                            </td>
                            <td>
                                @if($uItem->status === 'unused')
                                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1">Unused</span>
                                @elseif($uItem->status === 'used')
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1">Used</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1">Expired</span>
                                @endif
                            </td>
                            <td>
                                @if($uItem->is_equipped)
                                    <span class="badge bg-success text-white rounded-pill px-2 py-1"><i class="fa-solid fa-check me-1"></i> Equipped</span>
                                @else
                                    <span class="text-muted small">No</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-capitalize small fw-semibold text-muted">{{ $uItem->acquired_from }}</span>
                            </td>
                            <td>
                                <span class="small fw-semibold {{ $uItem->is_valid ? 'text-primary' : 'text-danger' }}">
                                    {{ $uItem->remaining_days_text }}
                                </span>
                            </td>
                            <td class="text-end pe-4 text-muted small">
                                {{ $uItem->created_at?->format('d M Y, h:i A') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-boxes-stacked fa-3x text-muted mb-3 opacity-50"></i>
                                <p class="mb-0">No user backpack inventory records yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($userItems->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $userItems->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
