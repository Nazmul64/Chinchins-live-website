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

        <!-- Application Dropdown -->
        <div class="menu-item-group">
            <button type="button" class="menu-item menu-dropdown-toggle">
                <div class="menu-item-left">
                    <i class="fa-solid fa-table-cells-large"></i>
                    <span>Application</span>
                </div>
                <i class="fa-solid fa-chevron-right menu-arrow"></i>
            </button>
            <div class="submenu">
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Chat</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Email / Inbox</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Calendar</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>File Manager</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Contacts</span>
                </a>
            </div>
        </div>

        <!-- UI Elements Category -->
        <div class="menu-category-title">UI Elements</div>

        <!-- Widgets Dropdown -->
        <div class="menu-item-group">
            <button type="button" class="menu-item menu-dropdown-toggle">
                <div class="menu-item-left">
                    <i class="fa-solid fa-droplet"></i>
                    <span>Widgets</span>
                </div>
                <i class="fa-solid fa-chevron-right menu-arrow"></i>
            </button>
            <div class="submenu">
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Static Widgets</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Data Widgets</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Chart Widgets</span>
                </a>
            </div>
        </div>

        <!-- eCommerce Dropdown -->
        <div class="menu-item-group">
            <button type="button" class="menu-item menu-dropdown-toggle">
                <div class="menu-item-left">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span>eCommerce</span>
                </div>
                <i class="fa-solid fa-chevron-right menu-arrow"></i>
            </button>
            <div class="submenu">
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Products</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Product Details</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Orders</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Customers</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Cart & Checkout</span>
                </a>
            </div>
        </div>

        <!-- Components Dropdown -->
        <div class="menu-item-group">
            <button type="button" class="menu-item menu-dropdown-toggle">
                <div class="menu-item-left">
                    <i class="fa-solid fa-cubes"></i>
                    <span>Components</span>
                </div>
                <i class="fa-solid fa-chevron-right menu-arrow"></i>
            </button>
            <div class="submenu">
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Buttons & Badges</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Alerts & Notifications</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Cards & Accordions</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Modals & Popups</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Progress & Spinners</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Tabs & Navs</span>
                </a>
            </div>
        </div>

        <!-- Icons Dropdown -->
        <div class="menu-item-group">
            <button type="button" class="menu-item menu-dropdown-toggle">
                <div class="menu-item-left">
                    <i class="fa-solid fa-icons"></i>
                    <span>Icons</span>
                </div>
                <i class="fa-solid fa-chevron-right menu-arrow"></i>
            </button>
            <div class="submenu">
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>FontAwesome Icons</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Feather Icons</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Bootstrap Icons</span>
                </a>
            </div>
        </div>

        <!-- Forms & Tables Category -->
        <div class="menu-category-title">Forms & Tables</div>

        <!-- Forms Dropdown -->
        <div class="menu-item-group">
            <button type="button" class="menu-item menu-dropdown-toggle">
                <div class="menu-item-left">
                    <i class="fa-regular fa-file-lines"></i>
                    <span>Forms</span>
                </div>
                <i class="fa-solid fa-chevron-right menu-arrow"></i>
            </button>
            <div class="submenu">
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Form Elements</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Form Layouts</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Form Validation</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Form Wizard</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>File Upload</span>
                </a>
            </div>
        </div>

        <!-- Tables Dropdown -->
        <div class="menu-item-group">
            <button type="button" class="menu-item menu-dropdown-toggle">
                <div class="menu-item-left">
                    <i class="fa-solid fa-table"></i>
                    <span>Tables</span>
                </div>
                <i class="fa-solid fa-chevron-right menu-arrow"></i>
            </button>
            <div class="submenu">
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Basic Tables</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Data Tables</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Responsive Tables</span>
                </a>
            </div>
        </div>

        <!-- Pages Category -->
        <div class="menu-category-title">Pages</div>

        <!-- Authentication Dropdown -->
        <div class="menu-item-group">
            <button type="button" class="menu-item menu-dropdown-toggle">
                <div class="menu-item-left">
                    <i class="fa-solid fa-lock"></i>
                    <span>Authentication</span>
                </div>
                <i class="fa-solid fa-chevron-right menu-arrow"></i>
            </button>
            <div class="submenu">
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Sign In</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Sign Up</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Forgot Password</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Reset Password</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Lock Screen</span>
                </a>
            </div>
        </div>

        <!-- User Profile Dropdown -->
        <div class="menu-item-group">
            <button type="button" class="menu-item menu-dropdown-toggle">
                <div class="menu-item-left">
                    <i class="fa-regular fa-user"></i>
                    <span>User Profile</span>
                </div>
                <i class="fa-solid fa-chevron-right menu-arrow"></i>
            </button>
            <div class="submenu">
                <a href="{{ route('admin.profile') }}" class="submenu-item {{ request()->routeIs('admin.profile') ? 'active' : '' }}">
                    <span class="submenu-bullet"></span>
                    <span>Profile Overview</span>
                </a>
                <a href="{{ route('admin.profile') }}" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Edit Profile</span>
                </a>
                <a href="{{ route('admin.profile') }}" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Account Settings</span>
                </a>
            </div>
        </div>

        <!-- Timeline Dropdown -->
        <div class="menu-item-group">
            <button type="button" class="menu-item menu-dropdown-toggle">
                <div class="menu-item-left">
                    <i class="fa-solid fa-timeline"></i>
                    <span>Timeline</span>
                </div>
                <i class="fa-solid fa-chevron-right menu-arrow"></i>
            </button>
            <div class="submenu">
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Vertical Timeline</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Horizontal Timeline</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Activity Stream</span>
                </a>
            </div>
        </div>
    </div>
</aside>
