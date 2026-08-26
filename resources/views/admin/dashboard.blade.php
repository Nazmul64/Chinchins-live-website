@extends('layouts.admin')

@section('title', 'Color Dashboard 1')

@section('content')
<!-- Top Metric Cards Grid -->
<div class="stats-grid">
    <!-- Card 1: Total Users -->
    <a href="{{ route('admin.users.index') }}" class="stat-card text-decoration-none">
        <div class="stat-card-header">
            <span class="stat-title">Total Users</span>
            <span class="stat-badge trend-up">{{ $metrics['total_orders']['change'] ?? '+ Active' }} &uarr;</span>
        </div>
        <div class="stat-card-body">
            <span class="stat-value">{{ $metrics['total_orders']['value'] ?? '0' }}</span>
            <div class="stat-sparkline">
                <canvas id="ordersSparkline"></canvas>
            </div>
        </div>
    </a>

    <!-- Card 2: Total Coins -->
    <a href="{{ route('admin.users.index', ['sort' => 'coins_high']) }}" class="stat-card text-decoration-none">
        <div class="stat-card-header">
            <span class="stat-title">Coins in System</span>
            <span class="stat-badge" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">{{ $metrics['total_views']['change'] ?? 'Coins' }}</span>
        </div>
        <div class="stat-card-body">
            <span class="stat-value" style="color: #f59e0b;">{{ $metrics['total_views']['value'] ?? '0' }}</span>
            <div class="stat-sparkline">
                <canvas id="viewsSparkline"></canvas>
            </div>
        </div>
    </a>

    <!-- Card 3: Total Deposit Revenue -->
    <a href="{{ route('admin.deposits.index', ['status' => 'approved']) }}" class="stat-card text-decoration-none">
        <div class="stat-card-header">
            <span class="stat-title">Deposit Revenue</span>
            <span class="stat-badge trend-up">{{ $metrics['revenue']['change'] ?? '+ Deposited' }} &uarr;</span>
        </div>
        <div class="stat-card-body">
            <span class="stat-value">{{ $metrics['revenue']['value'] ?? '৳ 0.00' }}</span>
            <div class="stat-sparkline">
                <canvas id="revenueSparkline"></canvas>
            </div>
        </div>
    </a>

    <!-- Card 4: Pending Deposits -->
    <a href="{{ route('admin.deposits.index', ['status' => 'pending']) }}" class="stat-card text-decoration-none">
        <div class="stat-card-header">
            <span class="stat-title">Pending Deposits</span>
            <span class="stat-badge {{ ($pendingDeposits ?? 0) > 0 ? 'trend-down' : 'trend-up' }}">{{ $metrics['customers']['change'] ?? 'Action Needed' }}</span>
        </div>
        <div class="stat-card-body">
            <span class="stat-value" style="color: {{ ($pendingDeposits ?? 0) > 0 ? '#ef4444' : '#10b981' }};">{{ $metrics['customers']['value'] ?? '0' }}</span>
            <div class="stat-sparkline">
                <canvas id="customersSparkline"></canvas>
            </div>
        </div>
    </a>
</div>

<!-- Middle Row: Revenue Main Chart & By Device Donut -->
<div class="charts-grid-middle">
    <!-- Revenue Main Chart -->
    <div class="card-widget">
        <div class="widget-header">
            <h3 class="widget-title">Revenue</h3>
            <button class="widget-menu-btn" title="Options">
                <i class="fa-solid fa-ellipsis"></i>
            </button>
        </div>
        <div style="height: 310px; width: 100%; position: relative;">
            <canvas id="mainRevenueChart"></canvas>
        </div>
    </div>

    <!-- By Device Donut Chart -->
    <div class="card-widget">
        <div class="widget-header">
            <h3 class="widget-title">By Device</h3>
            <button class="widget-menu-btn" title="Options">
                <i class="fa-solid fa-ellipsis"></i>
            </button>
        </div>
        <div class="donut-widget-body" style="height: 310px;">
            <!-- Donut Canvas Container with Center Text -->
            <div class="donut-chart-container">
                <canvas id="deviceDonutChart"></canvas>
                <div class="donut-center-label">
                    <div class="donut-center-pct">{{ $metrics['device_stats']['total_visitors_percentage'] ?? '85' }}%</div>
                    <div class="donut-center-sub">Total Visitors</div>
                </div>
            </div>

            <!-- Legend List -->
            <div class="donut-legend">
                <div class="legend-item">
                    <div class="legend-dot-label">
                        <span class="legend-color-box" style="background-color: #3b82f6;"></span>
                        <span>Desktop</span>
                    </div>
                    <span class="legend-val">{{ $metrics['device_stats']['desktop'] ?? '15.2' }}%</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot-label">
                        <span class="legend-color-box" style="background-color: #10b981;"></span>
                        <span>Mobile</span>
                    </div>
                    <span class="legend-val">{{ $metrics['device_stats']['mobile'] ?? '62.3' }}%</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot-label">
                        <span class="legend-color-box" style="background-color: #f97316;"></span>
                        <span>Tablet</span>
                    </div>
                    <span class="legend-val">{{ $metrics['device_stats']['tablet'] ?? '22.5' }}%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Row: Traffic Source, Messages/Posts Stack, and Visitors Chart -->
<div class="charts-grid-bottom">
    <!-- Card 1: Traffic Source Gauge -->
    <div class="card-widget">
        <div class="widget-header">
            <h3 class="widget-title">Traffic Source</h3>
            <button class="widget-menu-btn" title="Options">
                <i class="fa-solid fa-ellipsis"></i>
            </button>
        </div>
        <div class="traffic-gauge-container" style="height: 250px;">
            <div class="gauge-canvas-box">
                <canvas id="trafficGaugeChart"></canvas>
                <div class="gauge-center-text">
                    <div class="gauge-title">Total Traffic</div>
                    <div class="gauge-val">{{ $metrics['traffic_percentage'] ?? '78' }}%</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Stacked Mini Widgets (Messages & Total Posts) -->
    <div class="stacked-mini-widgets">
        <!-- Messages Mini Card -->
        <div class="mini-stat-box" style="height: 140px;">
            <div class="mini-stat-header">
                <div>
                    <div class="mini-stat-title">Messages</div>
                    <div class="mini-stat-num" style="color: #ec4899;">{{ $metrics['messages_count'] ?? '289' }}</div>
                </div>
                <button class="widget-menu-btn" title="Options">
                    <i class="fa-solid fa-ellipsis"></i>
                </button>
            </div>
            <div style="height: 55px; width: 100%;">
                <canvas id="messagesChart"></canvas>
            </div>
        </div>

        <!-- Total Posts Mini Card -->
        <div class="mini-stat-box" style="height: 140px;">
            <div class="mini-stat-header">
                <div>
                    <div class="mini-stat-title">Total Posts</div>
                    <div class="mini-stat-num" style="color: #10b981;">{{ $metrics['posts_count'] ?? '489' }}</div>
                </div>
                <button class="widget-menu-btn" title="Options">
                    <i class="fa-solid fa-ellipsis"></i>
                </button>
            </div>
            <div style="height: 55px; width: 100%;">
                <canvas id="postsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Card 3: Visitors Stacked Bar Chart -->
    <div class="card-widget">
        <div class="widget-header">
            <h3 class="widget-title">Visitors</h3>
            <button class="widget-menu-btn" title="Options">
                <i class="fa-solid fa-ellipsis"></i>
            </button>
        </div>
        <div style="height: 250px; width: 100%; position: relative;">
            <canvas id="visitorsStackedBarChart"></canvas>
        </div>
    </div>
</div>

<!-- Recent Deposits & Platform Management Grid -->
<div class="row g-4 mt-1 mb-4">
    <!-- Recent Deposit Requests -->
    <div class="col-12 col-xl-7">
        <div class="card p-3" style="border: 1px solid var(--border-color); border-radius: var(--radius-md); background: var(--bg-card);">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0" style="font-size: 16px; font-weight: 600; color: var(--text-primary);">
                    <i class="fa-solid fa-money-bill-transfer text-warning me-2"></i> Recent Deposit Requests
                </h5>
                <a href="{{ route('admin.deposits.index') }}" class="text-primary fw-bold text-decoration-none" style="font-size: 13px;">
                    View All &rarr;
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead>
                        <tr class="text-muted" style="border-bottom: 1px solid var(--border-color);">
                            <th>User</th>
                            <th>Method</th>
                            <th>Amount & Coins</th>
                            <th>TrxID</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentDeposits as $dep)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $dep->user?->avatar_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($dep->user?->display_name ?? 'User') . '&background=3b82f6&color=fff' }}" 
                                             class="rounded-circle" style="width: 30px; height: 30px; object-fit: cover;">
                                        <span>{{ $dep->user?->display_name ?? 'User' }}</span>
                                    </div>
                                </td>
                                <td><span class="badge bg-primary">{{ $dep->payment_method_name }}</span></td>
                                <td>
                                    <strong>৳ {{ number_format($dep->amount, 2) }}</strong>
                                    <small class="text-muted d-block">{{ number_format($dep->coins) }} Coins</small>
                                </td>
                                <td><code>{{ $dep->transaction_id }}</code></td>
                                <td>
                                    <span class="badge {{ $dep->status === 'approved' ? 'bg-success' : ($dep->status === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                        {{ ucfirst($dep->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">No recent deposit requests.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Shortcuts -->
    <div class="col-12 col-xl-5">
        <div class="card p-3" style="border: 1px solid var(--border-color); border-radius: var(--radius-md); background: var(--bg-card); height: 100%;">
            <h5 class="mb-3" style="font-size: 16px; font-weight: 600; color: var(--text-primary);">
                <i class="fa-solid fa-bolt text-primary me-2"></i> Quick Actions
            </h5>
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('admin.users.index') }}" class="p-3 rounded d-flex justify-content-between align-items-center text-decoration-none" style="background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary); transition: var(--transition);">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fa-solid fa-users text-primary fa-lg"></i>
                        <div>
                            <strong style="font-size: 14px;">Manage Users & Coins</strong>
                            <small class="text-muted d-block">Search users, add/deduct coins balance</small>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-muted"></i>
                </a>

                <a href="{{ route('admin.deposits.index') }}" class="p-3 rounded d-flex justify-content-between align-items-center text-decoration-none" style="background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary); transition: var(--transition);">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fa-solid fa-money-bill-transfer text-warning fa-lg"></i>
                        <div>
                            <strong style="font-size: 14px;">Approve Deposits</strong>
                            <small class="text-muted d-block">{{ $pendingDeposits ?? 0 }} pending requests waiting</small>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-muted"></i>
                </a>

                <a href="{{ route('admin.payment-methods.index') }}" class="p-3 rounded d-flex justify-content-between align-items-center text-decoration-none" style="background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary); transition: var(--transition);">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fa-solid fa-credit-card text-success fa-lg"></i>
                        <div>
                            <strong style="font-size: 14px;">bKash & Nagad Gateways</strong>
                            <small class="text-muted d-block">{{ $activeMethodsCount ?? 0 }} active payment accounts</small>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-muted"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
