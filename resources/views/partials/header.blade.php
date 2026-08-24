<header class="top-header">
    <div class="header-left">
        <button type="button" class="toggle-sidebar-btn" id="toggleSidebar" title="Toggle Sidebar">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Type here to search">
        </div>
    </div>

    <div class="header-right">
        <!-- Country Flag -->
        <button class="header-icon-btn" title="Country / Language">
            <img src="https://flagcdn.com/w40/my.png" alt="Country Flag" class="country-flag">
        </button>

        <!-- Dark/Light Mode -->
        <button class="header-icon-btn" id="themeToggleBtn" title="Toggle Dark/Light Mode">
            <i class="fa-solid fa-moon"></i>
        </button>

        <!-- App Grid -->
        <button class="header-icon-btn" title="Applications">
            <i class="fa-solid fa-table-cells-large"></i>
        </button>

        <!-- Notification Bell -->
        <button class="header-icon-btn" title="Notifications">
            <i class="fa-regular fa-bell"></i>
            <span class="badge-pill">5</span>
        </button>

        <!-- Messages Icon -->
        <button class="header-icon-btn" title="Messages">
            <i class="fa-regular fa-comment-dots"></i>
            <span class="badge-pill">8</span>
        </button>

        <!-- User Profile Dropdown -->
        <div class="user-profile-btn" id="userProfileDropdownBtn">
            <img 
                src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80" 
                alt="Jhon Deo Avatar" 
                class="user-avatar"
            >
            <div class="user-info">
                <span class="user-name">{{ Auth::user()->name ?? 'Jhon Deo' }}</span>
                <span class="user-role">HR Manager</span>
            </div>
            <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem; color: var(--text-muted); margin-left: 4px;"></i>

            <!-- Dropdown Menu -->
            <div class="dropdown-menu-custom" id="userDropdownMenu">
                <a href="javascript:void(0)" class="dropdown-item-custom">
                    <i class="fa-regular fa-user"></i> My Profile
                </a>
                <a href="javascript:void(0)" class="dropdown-item-custom">
                    <i class="fa-solid fa-gear"></i> Settings
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                    @csrf
                    <button type="submit" class="dropdown-item-custom" style="color: #ef4444;">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
