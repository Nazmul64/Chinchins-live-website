<aside class="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('admin.dashboard') }}" class="brand-logo">
            <span class="logo-icon">
                <i class="fa-solid fa-shapes"></i>
            </span>
            <span>Onedash</span>
        </a>
    </div>

    @php
        $activeGiftsTotal = 0;
        $activeBagItemsCount = 0;
        $activeVipCardsCount = 0;
        $activeSpendLessCount = 0;
        $activeBasesTotal = 0;
        $pendingDepCount = 0;
        $pendingWithCount = 0;
        $pendingKycCount = 0;
        $pendingReportsCount = 0;
        $pendingResellerDepCount = 0;
        $pendingResellerWithCount = 0;

        try { $activeGiftsTotal = \App\Models\Gift::where('is_active', true)->count(); } catch (\Throwable $e) {}
        try { $activeBagItemsCount = \App\Models\BagItem::where('is_active', true)->count(); } catch (\Throwable $e) {}
        try { $activeVipCardsCount = \App\Models\VipPrivilegeCard::where('is_active', true)->count(); } catch (\Throwable $e) {}
        try { $activeSpendLessCount = \App\Models\SpendLessCard::where('is_active', true)->count(); } catch (\Throwable $e) {}
        try { $activeBasesTotal = \App\Models\ProfileBase::where('is_active', true)->count(); } catch (\Throwable $e) {}
        try { $pendingDepCount = \App\Models\DepositRequest::where('status', 'pending')->count(); } catch (\Throwable $e) {}
        try { $pendingWithCount = \App\Models\WithdrawRequest::where('status', 'pending')->count(); } catch (\Throwable $e) {}
        try { $pendingKycCount = \App\Models\KycVerification::where('status', 'pending')->count(); } catch (\Throwable $e) {}
        try { $pendingReportsCount = \App\Models\UserReport::where('status', 'pending')->count(); } catch (\Throwable $e) {}
        try { $pendingResellerDepCount = \App\Models\ResellerDeposit::where('status', 'pending')->count(); } catch (\Throwable $e) {}
        try { $pendingResellerWithCount = \App\Models\ResellerWithdrawal::where('status', 'pending')->count(); } catch (\Throwable $e) {}
        try { $unreadUserSupportCount = \App\Models\UserAdminSupportMessage::where('sender_type', 'user')->where('is_read_by_admin', false)->count(); } catch (\Throwable $e) {}
    @endphp

    <div class="sidebar-menu">
        <!-- Dashboard Section -->
        <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}" style="margin-bottom: 4px;">
            <div class="menu-item-left">
                <i class="fa-solid fa-house" style="color: #3b82f6;"></i>
                <span>Dashboard</span>
            </div>
        </a>

        <!-- Live Platform & Coin Management -->
        <div class="menu-category-title">Management</div>

        <!-- Users & Coins -->
        @hasPermission('users.view')
        <a href="{{ route('admin.users.index') }}" class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" style="margin-bottom: 4px;">
            <div class="menu-item-left">
                <i class="fa-solid fa-users" style="color: #3b82f6;"></i>
                <span>Users & Balance</span>
            </div>
        </a>
        @endhasPermission

        <!-- Live Chat Users & 24/7 Support -->
        <a href="{{ route('admin.support.index') }}" class="menu-item {{ request()->routeIs('admin.support.*') ? 'active' : '' }}" style="margin-bottom: 4px;">
            <div class="menu-item-left">
                <i class="fa-solid fa-headset" style="color: #ec4899;"></i>
                <span>Live Chat Users</span>
            </div>
            @if(isset($unreadUserSupportCount) && $unreadUserSupportCount > 0)
                <span class="badge bg-danger rounded-pill px-2" style="font-size: 11px;">{{ $unreadUserSupportCount }}</span>
            @endif
        </a>

        <!-- Payment Methods -->
        @hasPermission('payment_methods.view')
        <a href="{{ route('admin.payment-methods.index') }}" class="menu-item {{ request()->routeIs('admin.payment-methods.*') ? 'active' : '' }}" style="margin-bottom: 4px;">
            <div class="menu-item-left">
                <i class="fa-solid fa-credit-card" style="color: #10b981;"></i>
                <span>Payment Methods</span>
            </div>
        </a>
        @endhasPermission

        <!-- Resellers Management (New) -->
        <div class="menu-item-group {{ request()->routeIs('admin.resellers.*') ? 'active open' : '' }}">
            <button type="button" class="menu-item menu-dropdown-toggle {{ request()->routeIs('admin.resellers.*') ? 'active' : '' }}" style="margin-bottom: 4px; justify-content: space-between;">
                <div class="menu-item-left">
                    <i class="fa-solid fa-store" style="color: #f59e0b;"></i>
                    <span>Resellers</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    @if(($pendingResellerDepCount + $pendingResellerWithCount) > 0)
                        <span class="badge bg-danger rounded-pill" style="font-size: 10px; padding: 2px 6px;">{{ $pendingResellerDepCount + $pendingResellerWithCount }}</span>
                    @endif
                    <i class="fa-solid fa-chevron-right menu-arrow"></i>
                </div>
            </button>
            <div class="submenu" style="{{ request()->routeIs('admin.resellers.*') ? 'display: block;' : '' }}">
                <a href="{{ route('admin.resellers.index') }}" class="submenu-item {{ request()->routeIs('admin.resellers.index') ? 'active' : '' }}">
                    <span class="submenu-bullet"></span>
                    <span>All Resellers</span>
                </a>
                <a href="{{ route('admin.resellers.create') }}" class="submenu-item {{ request()->routeIs('admin.resellers.create') ? 'active' : '' }}">
                    <span class="submenu-bullet"></span>
                    <span>+ Add New Reseller</span>
                </a>
                <a href="{{ route('admin.resellers.chat') }}" class="submenu-item {{ request()->routeIs('admin.resellers.chat*') ? 'active' : '' }}">
                    <span class="submenu-bullet"></span>
                    <span>Reseller Live Chat</span>
                </a>
                <a href="{{ route('admin.resellers.transfers') }}" class="submenu-item {{ request()->routeIs('admin.resellers.transfers') ? 'active' : '' }}">
                    <span class="submenu-bullet"></span>
                    <span>Transfer Ledger</span>
                </a>
                <a href="{{ route('admin.resellers.deposits') }}" class="submenu-item {{ request()->routeIs('admin.resellers.deposits*') ? 'active' : '' }}">
                    <span class="submenu-bullet"></span>
                    <span>Coin Refills</span>
                    @if($pendingResellerDepCount > 0)
                        <span class="badge bg-danger ms-auto rounded-pill" style="font-size: 10px; padding: 1px 6px;">{{ $pendingResellerDepCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.resellers.withdrawals') }}" class="submenu-item {{ request()->routeIs('admin.resellers.withdrawals*') ? 'active' : '' }}">
                    <span class="submenu-bullet"></span>
                    <span>Withdrawals</span>
                    @if($pendingResellerWithCount > 0)
                        <span class="badge bg-danger ms-auto rounded-pill" style="font-size: 10px; padding: 1px 6px;">{{ $pendingResellerWithCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.resellers.settings') }}" class="submenu-item {{ request()->routeIs('admin.resellers.settings*') ? 'active' : '' }}">
                    <span class="submenu-bullet"></span>
                    <span>Reseller Settings</span>
                </a>
            </div>
        </div>

        <!-- Coin Packages / Gems Store -->
        @hasPermission('coin_packages.view')
        <a href="{{ route('admin.coin-packages.index') }}" class="menu-item {{ request()->routeIs('admin.coin-packages.*') ? 'active' : '' }}" style="margin-bottom: 4px;">
            <div class="menu-item-left">
                <i class="fa-solid fa-gem" style="color: #ec4899;"></i>
                <span>Coin Packages</span>
            </div>
        </a>
        @endhasPermission

        <!-- Gifts & Rewards System -->
        @hasPermission('gifts.view')
        <a href="{{ route('admin.gifts.index') }}" class="menu-item {{ request()->routeIs('admin.gifts.*') ? 'active' : '' }}" style="margin-bottom: 4px;">
            <div class="menu-item-left">
                <i class="fa-solid fa-gift" style="color: #f43f5e;"></i>
                <span>Gifts & Rewards</span>
            </div>
            @if($activeGiftsTotal > 0)
                <span class="badge bg-pink-subtle text-pink rounded-pill" style="font-size: 11px; padding: 2px 7px; background: rgba(244,63,94,0.15); color: #f43f5e;">{{ $activeGiftsTotal }}</span>
            @endif
        </a>
        @endhasPermission

        <!-- My Bag (আমার ব্যাগ) Items & Backpack System -->
        @hasPermission('bag_items.view')
        <div class="menu-item-group {{ request()->routeIs('admin.my-bag.*') ? 'active open' : '' }}">
            <button type="button" class="menu-item menu-dropdown-toggle {{ request()->routeIs('admin.my-bag.*') ? 'active' : '' }}" style="margin-bottom: 4px; justify-content: space-between;">
                <div class="menu-item-left">
                    <i class="fa-solid fa-bag-shopping" style="color: #a855f7;"></i>
                    <span>My Bag Items</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    @if($activeBagItemsCount > 0)
                        <span class="badge rounded-pill" style="font-size: 11px; padding: 2px 7px; background: rgba(168,85,247,0.15); color: #a855f7;">{{ $activeBagItemsCount }} Items</span>
                    @endif
                    <i class="fa-solid fa-chevron-right menu-arrow"></i>
                </div>
            </button>
            <div class="submenu" style="{{ request()->routeIs('admin.my-bag.*') ? 'display: block;' : '' }}">
                <a href="{{ route('admin.my-bag.index') }}" class="submenu-item {{ request()->routeIs('admin.my-bag.index') ? 'active' : '' }}">
                    <span class="submenu-bullet"></span>
                    <span>All Bag Items (6 Tabs)</span>
                </a>
                <a href="{{ route('admin.my-bag.inventory') }}" class="submenu-item {{ request()->routeIs('admin.my-bag.inventory') ? 'active' : '' }}">
                    <span class="submenu-bullet"></span>
                    <span>User Backpack Ledger</span>
                </a>
            </div>
        </div>
        @endhasPermission

        <!-- Premium VIP System & Privilege Cards -->
        @hasPermission('vip_cards.view')
        <div class="menu-item-group {{ request()->routeIs('admin.vip-cards.*') ? 'active open' : '' }}">
            <button type="button" class="menu-item menu-dropdown-toggle {{ request()->routeIs('admin.vip-cards.*') ? 'active' : '' }}" style="margin-bottom: 4px; justify-content: space-between;">
                <div class="menu-item-left">
                    <i class="fa-solid fa-crown" style="color: #f59e0b;"></i>
                    <span>Premium VIP</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    @if($activeVipCardsCount > 0)
                        <span class="badge bg-warning-subtle text-warning rounded-pill" style="font-size: 11px; padding: 2px 7px; background: rgba(245,158,11,0.15); color: #f59e0b;">{{ $activeVipCardsCount }} Cards</span>
                    @endif
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
        @endhasPermission

        <!-- Spend Less, Get More Gems (Monthly & Weekly Cards & Extra Reward) -->
        @hasPermission('spend_less_cards.view')
        <div class="menu-item-group {{ request()->routeIs('admin.spend-less-cards.*') ? 'active open' : '' }}">
            <button type="button" class="menu-item menu-dropdown-toggle {{ request()->routeIs('admin.spend-less-cards.*') ? 'active' : '' }}" style="margin-bottom: 4px; justify-content: space-between;">
                <div class="menu-item-left">
                    <i class="fa-solid fa-gem" style="color: #ec4899;"></i>
                    <span>Spend Less, Get More</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    @if($activeSpendLessCount > 0)
                        <span class="badge bg-pink-subtle text-pink rounded-pill" style="font-size: 11px; padding: 2px 7px; background: rgba(236,72,153,0.15); color: #ec4899;">{{ $activeSpendLessCount }} Cards</span>
                    @endif
                    <i class="fa-solid fa-chevron-right menu-arrow"></i>
                </div>
            </button>
            <div class="submenu" style="{{ request()->routeIs('admin.spend-less-cards.*') ? 'display: block;' : '' }}">
                <a href="{{ route('admin.spend-less-cards.index') }}" class="submenu-item {{ request()->routeIs('admin.spend-less-cards.index') ? 'active' : '' }}">
                    <span class="submenu-bullet"></span>
                    <span>Monthly & Weekly Cards</span>
                </a>
                <a href="{{ route('admin.spend-less-cards.subscriptions') }}" class="submenu-item {{ request()->routeIs('admin.spend-less-cards.subscriptions') ? 'active' : '' }}">
                    <span class="submenu-bullet"></span>
                    <span>User Subscriptions</span>
                </a>
            </div>
        </div>
        @endhasPermission

        <!-- Level Badges & Profile Avatar Bases -->
        @hasPermission('level_badges.view')
        <a href="{{ route('admin.profile-bases.index') }}" class="menu-item {{ request()->routeIs('admin.profile-bases.*') ? 'active' : '' }}" style="margin-bottom: 4px; justify-content: space-between;">
            <div class="menu-item-left">
                <i class="fa-solid fa-certificate" style="color: #f59e0b;"></i>
                <span>Level Badges & Frames</span>
            </div>
            @if($activeBasesTotal > 0)
                <span class="badge bg-amber-subtle text-amber rounded-pill" style="font-size: 11px; padding: 2px 7px; background: rgba(245,158,11,0.15); color: #f59e0b;">{{ $activeBasesTotal }} Tiers</span>
            @endif
        </a>
        @endhasPermission

        <!-- Deposit Requests -->
        @hasPermission('deposits.view')
        <a href="{{ route('admin.deposits.index') }}" class="menu-item {{ request()->routeIs('admin.deposits.*') ? 'active' : '' }}" style="margin-bottom: 4px; justify-content: space-between;">
            <div class="menu-item-left">
                <i class="fa-solid fa-money-bill-transfer" style="color: #f59e0b;"></i>
                <span>Deposit Requests</span>
            </div>
            @if($pendingDepCount > 0)
                <span class="badge bg-danger rounded-pill" style="font-size: 11px; padding: 2px 7px;">{{ $pendingDepCount }}</span>
            @endif
        </a>
        @endhasPermission

        <!-- Withdrawal Requests & Settings -->
        @hasPermission('withdrawals.view')
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
        @endhasPermission

        <!-- KYC Identity Verification -->
        @hasPermission('kyc.view')
        <a href="{{ route('admin.kyc.index') }}" class="menu-item {{ request()->routeIs('admin.kyc.*') ? 'active' : '' }}" style="margin-bottom: 4px; justify-content: space-between;">
            <div class="menu-item-left">
                <i class="fa-solid fa-id-card" style="color: #06b6d4;"></i>
                <span>KYC Verification</span>
            </div>
            @if($pendingKycCount > 0)
                <span class="badge bg-danger rounded-pill" style="font-size: 11px; padding: 2px 7px;">{{ $pendingKycCount }}</span>
            @endif
        </a>
        @endhasPermission

        <!-- User & In-Chat Reports Moderation -->
        @hasPermission('reports.view')
        <a href="{{ route('admin.reports.index') }}" class="menu-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" style="margin-bottom: 4px; justify-content: space-between;">
            <div class="menu-item-left">
                <i class="fa-solid fa-triangle-exclamation" style="color: #ef4444;"></i>
                <span>User Reports</span>
            </div>
            @if($pendingReportsCount > 0)
                <span class="badge bg-danger rounded-pill" style="font-size: 11px; padding: 2px 7px;">{{ $pendingReportsCount }}</span>
            @endif
        </a>
        @endhasPermission

        <!-- Audio & Video Calling Sessions & Revenue -->
        @hasPermission('calls.view')
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
        @endhasPermission

        <!-- Coin Transactions -->
        @hasPermission('transactions.view')
        <a href="{{ route('admin.transactions.index') }}" class="menu-item {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}" style="margin-bottom: 4px;">
            <div class="menu-item-left">
                <i class="fa-solid fa-coins" style="color: #8b5cf6;"></i>
                <span>Coin Ledger</span>
            </div>
        </a>
        @endhasPermission

        <!-- Staff & RBAC Management Section -->
        @canAnyPermission(['staff.view', 'roles.view', 'activity_logs.view', 'login_history.view'])
        <div class="menu-category-title">Administration</div>

        <div class="menu-item-group {{ request()->routeIs('admin.staff.*') || request()->routeIs('admin.roles.*') ? 'active open' : '' }}">
            <button type="button" class="menu-item menu-dropdown-toggle {{ request()->routeIs('admin.staff.*') || request()->routeIs('admin.roles.*') ? 'active' : '' }}" style="margin-bottom: 4px; justify-content: space-between;">
                <div class="menu-item-left">
                    <i class="fa-solid fa-user-shield" style="color: #3b82f6;"></i>
                    <span>Staff & Roles</span>
                </div>
                <i class="fa-solid fa-chevron-right menu-arrow"></i>
            </button>
            <div class="submenu" style="{{ request()->routeIs('admin.staff.*') || request()->routeIs('admin.roles.*') ? 'display: block;' : '' }}">
                @hasPermission('staff.view')
                <a href="{{ route('admin.staff.index') }}" class="submenu-item {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                    <span class="submenu-bullet"></span>
                    <span>Staff Members</span>
                </a>
                @endhasPermission
                @hasPermission('roles.view')
                <a href="{{ route('admin.roles.index') }}" class="submenu-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <span class="submenu-bullet"></span>
                    <span>Roles & Permissions</span>
                </a>
                @endhasPermission
            </div>
        </div>

        @hasPermission('activity_logs.view')
        <a href="{{ route('admin.activity-logs.index') }}" class="menu-item {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}" style="margin-bottom: 4px;">
            <div class="menu-item-left">
                <i class="fa-solid fa-clock-rotate-left" style="color: #6366f1;"></i>
                <span>Activity Audit Logs</span>
            </div>
        </a>
        @endhasPermission

        @hasPermission('login_history.view')
        <a href="{{ route('admin.login-history.index') }}" class="menu-item {{ request()->routeIs('admin.login-history.*') ? 'active' : '' }}" style="margin-bottom: 4px;">
            <div class="menu-item-left">
                <i class="fa-solid fa-right-to-bracket" style="color: #0ea5e9;"></i>
                <span>Login History</span>
            </div>
        </a>
        @endhasPermission
        @endcanAnyPermission

        <!-- App Branding & Settings -->
        @hasPermission('settings.view')
        <a href="{{ route('admin.settings.index') }}" class="menu-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" style="margin-bottom: 4px;">
            <div class="menu-item-left">
                <i class="fa-solid fa-sliders" style="color: #06b6d4;"></i>
                <span>App Branding & Config</span>
            </div>
        </a>
        @endhasPermission
    </div>
</aside>
