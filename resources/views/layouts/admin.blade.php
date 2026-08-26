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

    <!-- Bootstrap 5.3.3 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Onedash Admin Script -->
    <script src="{{ asset('assets/js/onedash.js') }}"></script>
    <script>
        // Floating quick setting action
        document.getElementById('floatingThemeToggle')?.addEventListener('click', () => {
            document.getElementById('themeToggleBtn')?.click();
        });
    </script>
    @stack('scripts')
</body>
</html>
