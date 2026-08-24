document.addEventListener('DOMContentLoaded', () => {
    // 1. Sidebar Toggle Logic
    const toggleSidebarBtn = document.getElementById('toggleSidebar');
    const sidebar = document.querySelector('.sidebar');
    const mainWrapper = document.querySelector('.main-wrapper');

    if (toggleSidebarBtn && sidebar && mainWrapper) {
        toggleSidebarBtn.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('mobile-open');
            } else {
                sidebar.classList.toggle('collapsed');
                mainWrapper.classList.toggle('expanded');
            }
        });
    }

    // 1.1 Sidebar Menu Dropdown Toggle Logic
    const dropdownToggles = document.querySelectorAll('.menu-dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            e.preventDefault();
            const parentGroup = toggle.closest('.menu-item-group');
            if (!parentGroup) return;

            const isOpen = parentGroup.classList.contains('open');

            // Close other open menus (accordion behavior)
            document.querySelectorAll('.menu-item-group.open').forEach(group => {
                if (group !== parentGroup) {
                    group.classList.remove('open');
                }
            });

            // Toggle current menu
            if (isOpen) {
                parentGroup.classList.remove('open');
            } else {
                parentGroup.classList.add('open');
            }
        });
    });

    // Submenu item click active handler
    const submenuItems = document.querySelectorAll('.submenu-item');
    submenuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // If it is a dummy link, keep UI active
            if (this.getAttribute('href') === 'javascript:void(0)' || this.getAttribute('href') === '#') {
                e.preventDefault();
                submenuItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });

    // 2. Profile Dropdown Toggle
    const userProfileBtn = document.getElementById('userProfileDropdownBtn');
    const userDropdownMenu = document.getElementById('userDropdownMenu');

    if (userProfileBtn && userDropdownMenu) {
        userProfileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdownMenu.classList.toggle('show');
        });

        document.addEventListener('click', () => {
            userDropdownMenu.classList.remove('show');
        });
    }

    // 3. Dark/Light Theme Toggle
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    if (themeToggleBtn) {
        const savedTheme = localStorage.getItem('onedash-theme') || 'light';
        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            const icon = themeToggleBtn.querySelector('i');
            if (icon) icon.className = 'fas fa-sun';
        }

        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('onedash-theme', newTheme);
            
            const icon = themeToggleBtn.querySelector('i');
            if (icon) {
                icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            }
        });
    }

    // 4. Initialize Charts
    initCharts();
});

function initCharts() {
    // --- Top Metric Sparklines ---

    // 1. Orders Sparkline (Wavy Pink Line)
    const ordersCanvas = document.getElementById('ordersSparkline');
    if (ordersCanvas) {
        new Chart(ordersCanvas, {
            type: 'line',
            data: {
                labels: [1, 2, 3, 4, 5, 6, 7, 8],
                datasets: [{
                    data: [15, 20, 18, 25, 45, 38, 42, 35],
                    borderColor: '#f43f5e',
                    borderWidth: 2.5,
                    pointRadius: 0,
                    tension: 0.45,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: { x: { display: false }, y: { display: false } }
            }
        });
    }

    // 2. Views Sparkline (Blue Mini Bar Chart)
    const viewsCanvas = document.getElementById('viewsSparkline');
    if (viewsCanvas) {
        new Chart(viewsCanvas, {
            type: 'bar',
            data: {
                labels: [1, 2, 3, 4, 5, 6, 7],
                datasets: [{
                    data: [8, 12, 6, 16, 14, 18, 15],
                    backgroundColor: '#3b82f6',
                    borderRadius: 2,
                    barThickness: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: { x: { display: false }, y: { display: false } }
            }
        });
    }

    // 3. Revenue Sparkline (Wavy Green Line)
    const revSparklineCanvas = document.getElementById('revenueSparkline');
    if (revSparklineCanvas) {
        new Chart(revSparklineCanvas, {
            type: 'line',
            data: {
                labels: [1, 2, 3, 4, 5, 6, 7, 8],
                datasets: [{
                    data: [10, 12, 28, 20, 32, 28, 38, 30],
                    borderColor: '#10b981',
                    borderWidth: 2.5,
                    pointRadius: 0,
                    tension: 0.45,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: { x: { display: false }, y: { display: false } }
            }
        });
    }

    // 4. Customers Sparkline (Orange Mini Bar Chart)
    const custSparklineCanvas = document.getElementById('customersSparkline');
    if (custSparklineCanvas) {
        new Chart(custSparklineCanvas, {
            type: 'bar',
            data: {
                labels: [1, 2, 3, 4, 5, 6, 7],
                datasets: [{
                    data: [6, 14, 10, 18, 15, 20, 16],
                    backgroundColor: '#f97316',
                    borderRadius: 2,
                    barThickness: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: { x: { display: false }, y: { display: false } }
            }
        });
    }

    // --- Middle Row Charts ---

    // 5. Main Revenue Area Chart (Smooth Wave with Blue Gradient)
    const mainRevCanvas = document.getElementById('mainRevenueChart');
    if (mainRevCanvas) {
        const ctx = mainRevCanvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.45)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.02)');

        new Chart(mainRevCanvas, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
                datasets: [{
                    label: 'Revenue',
                    data: [220, 380, 240, 620, 250, 480, 420, 300, 490, 280],
                    borderColor: '#3b82f6',
                    borderWidth: 3,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.45,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 10,
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 12 },
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8', font: { size: 12 } }
                    },
                    y: {
                        min: 0,
                        max: 800,
                        ticks: { stepSize: 200, color: '#94a3b8', font: { size: 12 } },
                        grid: { color: 'rgba(226, 232, 240, 0.6)' }
                    }
                }
            }
        });
    }

    // 6. By Device Donut Chart
    const deviceCanvas = document.getElementById('deviceDonutChart');
    if (deviceCanvas) {
        new Chart(deviceCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Mobile', 'Tablet', 'Desktop'],
                datasets: [{
                    data: [62.3, 22.5, 15.2],
                    backgroundColor: ['#10b981', '#f97316', '#3b82f6'],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '76%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return ` ${context.label}: ${context.raw}%`;
                            }
                        }
                    }
                }
            }
        });
    }

    // --- Bottom Row Charts ---

    // 7. Traffic Source Semi Gauge / Donut
    const trafficCanvas = document.getElementById('trafficGaugeChart');
    if (trafficCanvas) {
        new Chart(trafficCanvas, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [78, 22],
                    backgroundColor: ['#8b5cf6', '#e2e8f0'],
                    borderWidth: 0,
                    borderRadius: [10, 0]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '80%',
                plugins: { legend: { display: false }, tooltip: { enabled: false } }
            }
        });
    }

    // 8. Messages Sparkline
    const msgCanvas = document.getElementById('messagesChart');
    if (msgCanvas) {
        const ctx = msgCanvas.getContext('2d');
        const pinkGrad = ctx.createLinearGradient(0, 0, 0, 70);
        pinkGrad.addColorStop(0, 'rgba(236, 72, 153, 0.3)');
        pinkGrad.addColorStop(1, 'rgba(236, 72, 153, 0.0)');

        new Chart(msgCanvas, {
            type: 'line',
            data: {
                labels: [1, 2, 3, 4, 5, 6, 7, 8, 9],
                datasets: [{
                    data: [20, 25, 40, 32, 45, 30, 48, 42, 28],
                    borderColor: '#ec4899',
                    borderWidth: 2,
                    backgroundColor: pinkGrad,
                    fill: true,
                    pointRadius: 0,
                    tension: 0.45
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: { x: { display: false }, y: { display: false } }
            }
        });
    }

    // 9. Total Posts Sparkline
    const postsCanvas = document.getElementById('postsChart');
    if (postsCanvas) {
        const ctx = postsCanvas.getContext('2d');
        const greenGrad = ctx.createLinearGradient(0, 0, 0, 70);
        greenGrad.addColorStop(0, 'rgba(16, 185, 129, 0.3)');
        greenGrad.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

        new Chart(postsCanvas, {
            type: 'line',
            data: {
                labels: [1, 2, 3, 4, 5, 6, 7, 8, 9],
                datasets: [{
                    data: [15, 30, 22, 35, 20, 40, 28, 38, 25],
                    borderColor: '#10b981',
                    borderWidth: 2,
                    backgroundColor: greenGrad,
                    fill: true,
                    pointRadius: 0,
                    tension: 0.45
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: { x: { display: false }, y: { display: false } }
            }
        });
    }

    // 10. Visitors Stacked Bar Chart
    const visitorsCanvas = document.getElementById('visitorsStackedBarChart');
    if (visitorsCanvas) {
        new Chart(visitorsCanvas, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [
                    {
                        label: 'New Visitors',
                        data: [600, 500, 850, 580, 750, 420, 620],
                        backgroundColor: '#3b82f6',
                        borderRadius: 4,
                        barThickness: 18
                    },
                    {
                        label: 'Returning Visitors',
                        data: [400, 380, 680, 380, 520, 300, 420],
                        backgroundColor: '#93c5fd',
                        borderRadius: 4,
                        barThickness: 18
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 10,
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false },
                        ticks: { color: '#94a3b8', font: { size: 12 } }
                    },
                    y: {
                        stacked: true,
                        min: 0,
                        max: 1600,
                        ticks: { stepSize: 400, color: '#94a3b8', font: { size: 12 } },
                        grid: { color: 'rgba(226, 232, 240, 0.6)' }
                    }
                }
            }
        });
    }
}
