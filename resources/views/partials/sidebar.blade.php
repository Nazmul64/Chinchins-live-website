<aside class="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('admin.dashboard') }}" class="brand-logo">
            <span class="logo-icon">
                <i class="fa-solid fa-shapes"></i>
            </span>
            <span>Onedash</span>
        </a>
    </div>

    <div class="sidebar-menu">
        <!-- Dashboard Section -->
        <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" style="margin-bottom: 4px;">
            <div class="menu-item-left">
                <i class="fa-solid fa-house" style="color: #3b82f6;"></i>
                <span>Dashboard</span>
            </div>
        </a>

        <!-- Live Platform & Coin Management -->
        <div class="menu-category-title">Management</div>

        <!-- Users & Coins -->
        <a href="{{ route('admin.users.index') }}" class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" style="margin-bottom: 4px;">
            <div class="menu-item-left">
                <i class="fa-solid fa-users" style="color: #3b82f6;"></i>
                <span>Users & Balance</span>
            </div>
        </a>

        <!-- Payment Methods -->
        <a href="{{ route('admin.payment-methods.index') }}" class="menu-item {{ request()->routeIs('admin.payment-methods.*') ? 'active' : '' }}" style="margin-bottom: 4px;">
            <div class="menu-item-left">
                <i class="fa-solid fa-credit-card" style="color: #10b981;"></i>
                <span>Payment Methods</span>
            </div>
        </a>

        <!-- Coin Packages / Gems Store -->
        <a href="{{ route('admin.coin-packages.index') }}" class="menu-item {{ request()->routeIs('admin.coin-packages.*') ? 'active' : '' }}" style="margin-bottom: 4px;">
            <div class="menu-item-left">
                <i class="fa-solid fa-gem" style="color: #ec4899;"></i>
                <span>Coin Packages</span>
            </div>
        </a>

        <!-- Gifts & Rewards System -->
        <a href="{{ route('admin.gifts.index') }}" class="menu-item {{ request()->routeIs('admin.gifts.*') ? 'active' : '' }}" style="margin-bottom: 4px;">
            <div class="menu-item-left">
                <i class="fa-solid fa-gift" style="color: #f43f5e;"></i>
                <span>Gifts & Rewards</span>
            </div>
            @php
                $activeGiftsTotal = \App\Models\Gift::where('is_active', true)->count();
            @endphp
            <span class="badge bg-pink-subtle text-pink rounded-pill" style="font-size: 11px; padding: 2px 7px; background: rgba(244,63,94,0.15); color: #f43f5e;">{{ $activeGiftsTotal }}</span>
        </a>

        <!-- Premium VIP System & Privilege Cards -->
        <div class="menu-item-group {{ request()->routeIs('admin.vip-cards.*') ? 'active open' : '' }}">
            <button type="button" class="menu-item menu-dropdown-toggle {{ request()->routeIs('admin.vip-cards.*') ? 'active' : '' }}" style="margin-bottom: 4px; justify-content: space-between;">
                <div class="menu-item-left">
                    <i class="fa-solid fa-crown" style="color: #f59e0b;"></i>
                    <span>Premium VIP</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    @php
                        $activeVipCardsCount = \App\Models\VipPrivilegeCard::where('is_active', true)->count();
                    @endphp
                    <span class="badge bg-warning-subtle text-warning rounded-pill" style="font-size: 11px; padding: 2px 7px;">{{ $activeVipCardsCount }} Cards</span>
                    <i class="fa-solid fa-chevron-right menu-arrow"></i>
                </div>
            </button>
            <div class="submenu" style="{{ request()->routeIs('admin.vip-cards.*') ? 'display: block;' : '' }}">
                <a href="{{ route('admin.vip-cards.index') }}" class="submenu-item {{ request()->routeIs('admin.vip-cards.index') ? 'active' : '' }}">
                    <span class="submenu-bullet"></span>
                    <span>VIP Packages & Banner</span>
                </a>
                <a href="{{ route('admin.vip-cards.subscriptions') }}" class="submenu-item {{ request()->routeIs('admin.vip-cards.subscriptions') ? 'active' : '' }}">
                    <span class="submenu-bullet"></span>
                    <span>User Subscriptions</span>
                </a>
            </div>
        </div>

        <!-- Deposit Requests -->
        @php
            $pendingDepCount = \App\Models\DepositRequest::where('status', 'pending')->count();
            $pendingWithCount = \App\Models\WithdrawRequest::where('status', 'pending')->count();
            $pendingKycCount = \App\Models\KycVerification::where('status', 'pending')->count();
        @endphp
        <a href="{{ route('admin.deposits.index') }}" class="menu-item {{ request()->routeIs('admin.deposits.*') ? 'active' : '' }}" style="margin-bottom: 4px; justify-content: space-between;">
            <div class="menu-item-left">
                <i class="fa-solid fa-money-bill-transfer" style="color: #f59e0b;"></i>
                <span>Deposit Requests</span>
            </div>
            @if($pendingDepCount > 0)
                <span class="badge bg-danger rounded-pill" style="font-size: 11px; padding: 2px 7px;">{{ $pendingDepCount }}</span>
            @endif
        </a>

        <!-- Withdrawal Requests & Settings -->
        <div class="menu-item-group {{ request()->routeIs('admin.withdrawals.*') ? 'active open' : '' }}">
            <button type="button" class="menu-item menu-dropdown-toggle {{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }}" style="margin-bottom: 4px; justify-content: space-between;">
                <div class="menu-item-left">
                    <i class="fa-solid fa-hand-holding-dollar" style="color: #3b82f6;"></i>
                    <span>Withdrawals</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    @if($pendingWithCount > 0)
                        <span class="badge bg-danger rounded-pill" style="font-size: 11px; padding: 2px 7px;">{{ $pendingWithCount }}</span>
                    @endif
                    <i class="fa-solid fa-chevron-right menu-arrow"></i>
                </div>
            </button>
            <div class="submenu" style="{{ request()->routeIs('admin.withdrawals.*') ? 'display: block;' : '' }}">
                <a href="{{ route('admin.withdrawals.index') }}" class="submenu-item {{ request()->routeIs('admin.withdrawals.index') ? 'active' : '' }}">
                    <span class="submenu-bullet"></span>
                    <span>All Requests</span>
                    @if($pendingWithCount > 0)
                        <span class="badge bg-danger ms-auto rounded-pill" style="font-size: 10px; padding: 1px 6px;">{{ $pendingWithCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.withdrawals.settings') }}" class="submenu-item {{ request()->routeIs('admin.withdrawals.settings') ? 'active' : '' }}">
                    <span class="submenu-bullet"></span>
                    <span>Withdraw Settings</span>
                </a>
            </div>
        </div>

        <!-- KYC Identity Verification -->
        <a href="{{ route('admin.kyc.index') }}" class="menu-item {{ request()->routeIs('admin.kyc.*') ? 'active' : '' }}" style="margin-bottom: 4px; justify-content: space-between;">
            <div class="menu-item-left">
                <i class="fa-solid fa-id-card" style="color: #06b6d4;"></i>
                <span>KYC Verification</span>
            </div>
            @if($pendingKycCount > 0)
                <span class="badge bg-danger rounded-pill" style="font-size: 11px; padding: 2px 7px;">{{ $pendingKycCount }}</span>
            @endif
        </a>

        <!-- Audio & Video Calling Sessions & Revenue -->
        <div class="menu-item-group {{ request()->routeIs('admin.calls.*') ? 'active open' : '' }}">
            <button type="button" class="menu-item menu-dropdown-toggle {{ request()->routeIs('admin.calls.*') ? 'active' : '' }}" style="margin-bottom: 4px; justify-content: space-between;">
                <div class="menu-item-left">
                    <i class="fa-solid fa-video" style="color: #ec4899;"></i>
                    <span>Call & Revenue</span>
                </div>
                <i class="fa-solid fa-chevron-right menu-arrow"></i>
            </button>
            <div class="submenu" style="{{ request()->routeIs('admin.calls.*') ? 'display: block;' : '' }}">
                <a href="{{ route('admin.calls.index') }}" class="submenu-item {{ request()->routeIs('admin.calls.index') ? 'active' : '' }}">
                    <span class="submenu-bullet"></span>
                    <span>Call Sessions Log</span>
                </a>
                <a href="{{ route('admin.calls.settings') }}" class="submenu-item {{ request()->routeIs('admin.calls.settings') ? 'active' : '' }}">
                    <span class="submenu-bullet"></span>
                    <span>Call & Ringtone Settings</span>
                </a>
            </div>
        </div>

        <!-- Coin Transactions -->
        <a href="{{ route('admin.transactions.index') }}" class="menu-item {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}" style="margin-bottom: 4px;">
            <div class="menu-item-left">
                <i class="fa-solid fa-coins" style="color: #8b5cf6;"></i>
                <span>Coin Ledger</span>
            </div>
        </a>

        <!-- App Branding & Settings -->
        <a href="{{ route('admin.settings.index') }}" class="menu-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" style="margin-bottom: 4px;">
            <div class="menu-item-left">
                <i class="fa-solid fa-sliders" style="color: #06b6d4;"></i>
                <span>App Branding & Config</span>
            </div>
        </a>
    </div>
</aside>
