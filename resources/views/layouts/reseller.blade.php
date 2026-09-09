<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Reseller Portal') - ChinChins Live</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&family=Fira+Code:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Onedash & Chinchins CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/onedash.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/chinchins-admin.css') }}">

    <style>
        :root {
            --reseller-sidebar-width: 270px;
            --reseller-sidebar-bg: #0f172a;
            --reseller-sidebar-hover: rgba(255, 255, 255, 0.08);
            --reseller-sidebar-active: #4f46e5;
            --reseller-topbar-height: 70px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Layout Structure */
        .reseller-layout {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* Sidebar Styling */
        .reseller-sidebar {
            width: var(--reseller-sidebar-width);
            background: linear-gradient(180deg, #0f172a 0%, #1e1b4b 100%);
            color: #fff;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-right: 1px solid rgba(255, 255, 255, 0.07);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
        }

        .reseller-sidebar-brand {
            padding: 20px 22px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            text-decoration: none;
            color: #fff;
        }

        .reseller-sidebar-brand .brand-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #f59e0b, #ef4444);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            color: #fff;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.45);
            flex-shrink: 0;
        }

        .reseller-sidebar-brand .brand-text {
            display: flex;
            flex-direction: column;
        }

        .reseller-sidebar-brand .brand-title {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.3px;
            color: #fff;
            line-height: 1.2;
        }

        .reseller-sidebar-brand .brand-sub {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        /* Reseller User Profile Widget in Sidebar */
        .reseller-sidebar-profile {
            padding: 16px 20px;
            margin: 14px 14px 6px 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .reseller-sidebar-profile img {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #f59e0b;
            flex-shrink: 0;
        }

        .reseller-profile-info {
            flex-grow: 1;
            overflow: hidden;
        }

        .reseller-profile-info .name {
            font-size: 14px;
            font-weight: 700;
            color: #f8fafc;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .reseller-profile-info .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            color: #fbbf24;
            background: rgba(245, 158, 11, 0.15);
            padding: 2px 8px;
            border-radius: 50px;
            margin-top: 3px;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #22c55e;
            display: inline-block;
            box-shadow: 0 0 8px #22c55e;
        }

        /* Navigation List */
        .reseller-nav-container {
            flex-grow: 1;
            padding: 12px 14px;
            overflow-y: auto;
        }

        .reseller-nav-group-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            padding: 12px 14px 6px 14px;
        }

        .reseller-side-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 16px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            border-radius: 10px;
            margin-bottom: 4px;
            transition: all 0.2s ease;
        }

        .reseller-side-link .link-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .reseller-side-link .link-left i {
            font-size: 16px;
            width: 20px;
            text-align: center;
            color: #94a3b8;
            transition: all 0.2s ease;
        }

        .reseller-side-link:hover {
            color: #fff;
            background: var(--reseller-sidebar-hover);
        }

        .reseller-side-link:hover .link-left i {
            color: #60a5fa;
            transform: scale(1.1);
        }

        .reseller-side-link.active {
            color: #fff;
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);
        }

        .reseller-side-link.active .link-left i {
            color: #fff;
        }

        /* Sidebar Stock Footer Widget */
        .reseller-sidebar-footer {
            padding: 14px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(0, 0, 0, 0.2);
        }

        .stock-balance-box {
            background: rgba(245, 158, 11, 0.12);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 10px;
        }

        .stock-balance-box .title {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stock-balance-box .amount {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 800;
            color: #fbbf24;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 2px;
        }

        /* Main Content Area */
        .reseller-main-wrapper {
            flex-grow: 1;
            margin-left: var(--reseller-sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #f8fafc;
            width: calc(100% - var(--reseller-sidebar-width));
        }

        /* Top Header */
        .reseller-topbar {
            height: var(--reseller-topbar-height);
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1020;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        }

        .reseller-topbar .page-title {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }

        .reseller-topbar-balance {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(239, 68, 68, 0.08));
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 50px;
            padding: 6px 16px;
            font-weight: 700;
            font-size: 14px;
            color: #d97706;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Page Content Container */
        .reseller-content {
            padding: 28px;
            flex-grow: 1;
        }

        /* Mobile Sidebar Overlay */
        .reseller-sidebar-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1035;
        }

        @media (max-width: 991.98px) {
            .reseller-sidebar {
                transform: translateX(-100%);
            }
            .reseller-sidebar.show {
                transform: translateX(0);
            }
            .reseller-main-wrapper {
                margin-left: 0;
                width: 100%;
            }
            .reseller-sidebar-backdrop.show {
                display: block;
            }
            .reseller-topbar {
                padding: 0 16px;
            }
            .reseller-content {
                padding: 16px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="reseller-layout">
        <!-- Mobile Backdrop -->
        <div class="reseller-sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- Left Navigation Sidebar -->
        <aside class="reseller-sidebar" id="resellerSidebar">
            <!-- Sidebar Brand Header -->
            <a href="{{ route('reseller.dashboard') }}" class="reseller-sidebar-brand">
                <div class="brand-icon">
                    <i class="fa-solid fa-gem"></i>
                </div>
                <div class="brand-text">
                    <span class="brand-title">ChinChins</span>
                    <span class="brand-sub">Reseller Portal</span>
                </div>
            </a>

            <!-- Reseller Profile Snapshot -->
            @if(isset($reseller) && $reseller)
                <div class="reseller-sidebar-profile">
                    <img src="{{ $reseller->avatar_url }}" alt="{{ $reseller->name }}">
                    <div class="reseller-profile-info">
                        <div class="name">{{ $reseller->name }}</div>
                        <div class="badge-pill">
                            <span class="status-dot"></span>
                            <span>{{ $reseller->level ?? 'Level Agent' }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Navigation Links -->
            <div class="reseller-nav-container">
                <div class="reseller-nav-group-label">Overview</div>

                <a href="{{ route('reseller.dashboard') }}" class="reseller-side-link {{ request()->routeIs('reseller.dashboard') ? 'active' : '' }}">
                    <div class="link-left">
                        <i class="fa-solid fa-gauge-high"></i>
                        <span>Dashboard</span>
                    </div>
                </a>

                <a href="{{ route('reseller.chat') }}" class="reseller-side-link {{ request()->routeIs('reseller.chat*') ? 'active' : '' }}">
                    <div class="link-left">
                        <i class="fa-solid fa-comments"></i>
                        <span>Customer Chat</span>
                    </div>
                    @php
                        $unreadCustomerCount = 0;
                        try {
                            if (isset($reseller) && $reseller) {
                                $unreadCustomerCount = \App\Models\ResellerChatMessage::where('reseller_id', $reseller->id)
                                    ->where('sender_type', 'user')
                                    ->where('is_read', false)
                                    ->count();
                            }
                        } catch (\Throwable $e) {}
                    @endphp
                    @if($unreadCustomerCount > 0)
                        <span class="badge rounded-pill bg-danger" style="font-size: 11px;">{{ $unreadCustomerCount }}</span>
                    @endif
                </a>

                <a href="{{ route('reseller.admin-chat') }}" class="reseller-side-link {{ request()->routeIs('reseller.admin-chat*') ? 'active' : '' }}">
                    <div class="link-left">
                        <i class="fa-solid fa-headset"></i>
                        <span>Admin Support</span>
                    </div>
                    @php
                        $unreadAdminCount = 0;
                        try {
                            if (isset($reseller) && $reseller) {
                                $unreadAdminCount = \App\Models\ResellerChatMessage::where('reseller_id', $reseller->id)
                                    ->where('sender_type', 'admin')
                                    ->where('is_read', false)
                                    ->count();
                            }
                        } catch (\Throwable $e) {}
                    @endphp
                    @if($unreadAdminCount > 0)
                        <span class="badge rounded-pill bg-warning text-dark" style="font-size: 11px;">{{ $unreadAdminCount }}</span>
                    @endif
                </a>

                <div class="reseller-nav-group-label">Coins & Operations</div>

                <a href="{{ route('reseller.transfers') }}" class="reseller-side-link {{ request()->routeIs('reseller.transfers') ? 'active' : '' }}">
                    <div class="link-left">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                        <span>Transfer Ledger</span>
                    </div>
                </a>

                <a href="{{ route('reseller.deposits') }}" class="reseller-side-link {{ request()->routeIs('reseller.deposits') ? 'active' : '' }}">
                    <div class="link-left">
                        <i class="fa-solid fa-circle-down"></i>
                        <span>Refill Stock</span>
                    </div>
                </a>

                <a href="{{ route('reseller.withdrawals') }}" class="reseller-side-link {{ request()->routeIs('reseller.withdrawals') ? 'active' : '' }}">
                    <div class="link-left">
                        <i class="fa-solid fa-circle-up"></i>
                        <span>Withdrawals</span>
                    </div>
                </a>
            </div>

            <!-- Sidebar Stock Footer -->
            <div class="reseller-sidebar-footer">
                @if(isset($reseller) && $reseller)
                    <div class="stock-balance-box">
                        <div class="title">Stock Balance</div>
                        <div class="amount">
                            <i class="fa-solid fa-coins text-warning"></i>
                            <span>{{ number_format($reseller->coins_balance) }}</span>
                            <small style="font-size: 11px; color: #cbd5e1; font-weight: normal;">Gems</small>
                        </div>
                    </div>
                @endif

                <div class="d-flex align-items-center justify-content-between gap-2">
                    <a href="{{ route('reseller.deposits') }}" class="btn btn-warning btn-sm fw-bold flex-grow-1 rounded-3">
                        <i class="fa-solid fa-plus me-1"></i> Refill
                    </a>
                    <form action="{{ route('reseller.logout') }}" method="POST" class="d-inline m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-3 px-3" title="Logout">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <div class="reseller-main-wrapper">
            <!-- Top Navbar Header -->
            <header class="reseller-topbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-light d-lg-none rounded-3 border" id="toggleSidebarBtn" type="button" aria-label="Toggle Navigation">
                        <i class="fa-solid fa-bars-staggered"></i>
                    </button>
                    <h1 class="page-title">@yield('title', 'Reseller Dashboard')</h1>
                </div>

                <div class="d-flex align-items-center gap-3">
                    @if(isset($reseller) && $reseller)
                        <div class="reseller-topbar-balance d-none d-sm-flex" title="Available Stock Balance">
                            <i class="fa-solid fa-gem text-warning"></i>
                            <span>{{ number_format($reseller->coins_balance) }} Gems</span>
                        </div>

                        <a href="{{ route('reseller.chat') }}" class="btn btn-light btn-sm rounded-3 border d-none d-md-flex align-items-center gap-2" title="Customer Chat">
                            <i class="fa-solid fa-comments text-primary"></i>
                            <span class="fw-semibold">Chat</span>
                        </a>

                        <div class="dropdown">
                            <button class="btn p-0 border-0 d-flex align-items-center gap-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="outline: none;">
                                <img src="{{ $reseller->avatar_url }}" alt="{{ $reseller->name }}" class="rounded-circle border border-2 border-warning" style="width: 38px; height: 38px; object-fit: cover;">
                                <div class="text-start d-none d-md-block" style="line-height: 1.2;">
                                    <div class="fw-bold text-dark" style="font-size: 13px;">{{ $reseller->name }}</div>
                                    <small class="text-muted" style="font-size: 11px;">{{ $reseller->phone ?? 'Reseller' }}</small>
                                </div>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2" style="min-width: 200px;">
                                <li>
                                    <div class="px-3 py-2 border-bottom">
                                        <div class="fw-bold">{{ $reseller->name }}</div>
                                        <small class="text-muted">{{ $reseller->email }}</small>
                                    </div>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('reseller.dashboard') }}">
                                        <i class="fa-solid fa-gauge-high text-muted me-2"></i> Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('reseller.chat') }}">
                                        <i class="fa-solid fa-comments text-muted me-2"></i> Customer Chat
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('reseller.admin-chat') }}">
                                        <i class="fa-solid fa-headset text-muted me-2"></i> Admin Support
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('reseller.logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item py-2 text-danger">
                                            <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endif
                </div>
            </header>

            <!-- Page Content -->
            <main class="reseller-content">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Global Floating Toast Container -->
    <div class="ch-toast-container" id="globalToastContainer"></div>

    <!-- Bootstrap 5.3.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Sidebar Toggle for Mobile / Small Screens
        const sidebar = document.getElementById('resellerSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        const toggleBtn = document.getElementById('toggleSidebarBtn');

        if (toggleBtn && sidebar && backdrop) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('show');
                backdrop.classList.toggle('show');
            });

            backdrop.addEventListener('click', () => {
                sidebar.classList.remove('show');
                backdrop.classList.remove('show');
            });
        }

        // Global Toast Notification Helper
        window.showToast = function(message, type = 'success', title = null) {
            let container = document.getElementById('globalToastContainer');
            if (!container) {
                container = document.createElement('div');
                container.id = 'globalToastContainer';
                container.className = 'ch-toast-container';
                document.body.appendChild(container);
            }
            const toast = document.createElement('div');
            toast.className = `ch-toast ch-toast-${type}`;
            toast.innerHTML = `<div class="ch-toast-content"><div class="ch-toast-title">${title || 'Notification'}</div><div class="ch-toast-message">${message}</div></div>`;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        };

        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                window.showToast(@json(session('success')), 'success', 'Success');
            @endif
            @if(session('error'))
                window.showToast(@json(session('error')), 'error', 'Error');
            @endif
        });
    </script>
    @stack('scripts')
</body>
</html>
