<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Color Dashboard 1') - Onedash Admin</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&family=Fira+Code:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome 6 CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/onedash.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/chinchins-admin.css') }}">

    <!-- Chart.js 4 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @stack('styles')
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar Navigation Partial -->
        @include('partials.sidebar')

        <!-- Main Wrapper -->
        <div class="main-wrapper">
            <!-- Header Partial -->
            @include('partials.header')

            <!-- Page Content -->
            <main class="page-content">
                @yield('content')
            </main>

            <!-- Footer Partial -->
            @include('partials.footer')
        </div>

        <!-- Floating Quick Settings Icon (as present in the reference screenshot) -->
        <div class="floating-settings-tab" title="Quick Theme Settings" id="floatingThemeToggle">
            <i class="fa-solid fa-gear"></i>
        </div>
    </div>

    <!-- Global Floating Toast Container -->
    <div class="ch-toast-container" id="globalToastContainer"></div>

    <!-- Bootstrap 5.3.3 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Onedash Admin Script -->
    <script src="{{ asset('assets/js/onedash.js') }}"></script>
    <script>
        // Floating quick setting action
        document.getElementById('floatingThemeToggle')?.addEventListener('click', () => {
            document.getElementById('themeToggleBtn')?.click();
        });

        /**
         * Global Modern Toaster Notification Function
         * @param {string} message - Notification text
         * @param {string} type - 'success' | 'error' | 'warning' | 'info'
         * @param {string|null} title - Optional custom title
         * @param {number} duration - Auto dismiss time in ms (default: 4000ms)
         */
        window.showToast = function(message, type = 'success', title = null, duration = 4000) {
            let container = document.getElementById('globalToastContainer');
            if (!container) {
                container = document.createElement('div');
                container.id = 'globalToastContainer';
                container.className = 'ch-toast-container';
                document.body.appendChild(container);
            }

            const validTypes = ['success', 'error', 'warning', 'info'];
            if (!validTypes.includes(type)) type = 'success';

            const defaultTitles = {
                success: 'Success',
                error: 'Action Failed',
                warning: 'Attention',
                info: 'Information'
            };

            const icons = {
                success: 'fa-solid fa-circle-check',
                error: 'fa-solid fa-circle-xmark',
                warning: 'fa-solid fa-triangle-exclamation',
                info: 'fa-solid fa-circle-info'
            };

            const displayTitle = title || defaultTitles[type];
            const displayIcon = icons[type];

            const toast = document.createElement('div');
            toast.className = `ch-toast ch-toast-${type}`;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="ch-toast-icon-wrap">
                    <i class="${displayIcon}"></i>
                </div>
                <div class="ch-toast-content">
                    <div class="ch-toast-title">${displayTitle}</div>
                    <div class="ch-toast-message">${message}</div>
                </div>
                <button type="button" class="ch-toast-close" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <div class="ch-toast-progress">
                    <div class="ch-toast-progress-bar" style="animation-duration: ${duration}ms;"></div>
                </div>
            `;

            container.appendChild(toast);

            let dismissTimer = setTimeout(() => {
                dismissToast();
            }, duration);

            function dismissToast() {
                clearTimeout(dismissTimer);
                toast.style.animation = 'chToastSlideOut 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards';
                setTimeout(() => {
                    toast.remove();
                }, 320);
            }

            toast.querySelector('.ch-toast-close')?.addEventListener('click', (e) => {
                e.stopPropagation();
                dismissToast();
            });

            // Pause timer on hover
            toast.addEventListener('mouseenter', () => {
                clearTimeout(dismissTimer);
                const bar = toast.querySelector('.ch-toast-progress-bar');
                if (bar) bar.style.animationPlayState = 'paused';
            });

            toast.addEventListener('mouseleave', () => {
                const bar = toast.querySelector('.ch-toast-progress-bar');
                if (bar) bar.style.animationPlayState = 'running';
                dismissTimer = setTimeout(() => {
                    dismissToast();
                }, 2000);
            });
        };

        // Auto-trigger toast on page load if Laravel Session has flash message
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                window.showToast(@json(session('success')), 'success', 'Success');
            @endif
            @if(session('error'))
                window.showToast(@json(session('error')), 'error', 'Error');
            @endif
            @if(session('warning'))
                window.showToast(@json(session('warning')), 'warning', 'Warning');
            @endif
            @if(session('info'))
                window.showToast(@json(session('info')), 'info', 'Notice');
            @endif
            @if($errors->any())
                window.showToast(@json($errors->first()), 'error', 'Validation Error');
            @endif
        });
    </script>
    @stack('scripts')
</body>
</html>
