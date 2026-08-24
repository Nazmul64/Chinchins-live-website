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
        <!-- Dashboard Section (Single Top-level active menu) -->
        <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" style="margin-bottom: 4px;">
            <div class="menu-item-left">
                <i class="fa-solid fa-house" style="color: #3b82f6;"></i>
                <span>Dashboard</span>
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
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Profile Overview</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
                    <span class="submenu-bullet"></span>
                    <span>Edit Profile</span>
                </a>
                <a href="javascript:void(0)" class="submenu-item">
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
