@extends('layouts.admin')

@section('title', 'Color Dashboard 1')

@section('content')
<!-- Top Metric Cards Grid -->
<div class="stats-grid">
    <!-- Card 1: Total Orders -->
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-title">Total Orders</span>
            <span class="stat-badge trend-up">+ 16% &uarr;</span>
        </div>
        <div class="stat-card-body">
            <span class="stat-value">{{ $metrics['total_orders']['value'] ?? '8,542' }}</span>
            <div class="stat-sparkline">
                <canvas id="ordersSparkline"></canvas>
            </div>
        </div>
    </div>

    <!-- Card 2: Total Views -->
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-title">Total Views</span>
            <span class="stat-badge trend-down">- 3.4% &darr;</span>
        </div>
        <div class="stat-card-body">
            <span class="stat-value">{{ $metrics['total_views']['value'] ?? '12.5M' }}</span>
            <div class="stat-sparkline">
                <canvas id="viewsSparkline"></canvas>
            </div>
        </div>
    </div>

    <!-- Card 3: Revenue -->
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-title">Revenue</span>
            <span class="stat-badge trend-up">+ 24% &uarr;</span>
        </div>
        <div class="stat-card-body">
            <span class="stat-value">{{ $metrics['revenue']['value'] ?? '$64.5K' }}</span>
            <div class="stat-sparkline">
                <canvas id="revenueSparkline"></canvas>
            </div>
        </div>
    </div>

    <!-- Card 4: Customers -->
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-title">Customers</span>
            <span class="stat-badge trend-up">+ 8.2% &uarr;</span>
        </div>
        <div class="stat-card-body">
            <span class="stat-value">{{ $metrics['customers']['value'] ?? '25.8K' }}</span>
            <div class="stat-sparkline">
                <canvas id="customersSparkline"></canvas>
            </div>
        </div>
    </div>
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
@endsection
