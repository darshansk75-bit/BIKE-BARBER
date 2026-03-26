<?php
/**
 * Admin Dashboard — App Shell
 * PATH: /admin/dashboard.php
 * Contains: Sidebar Navigation + Top Header + Main Content Area
 */
require_once __DIR__ . '/../includes/session_auth.php';

// Security Guard — Admin Only
auth_guard('admin');

// Date Filtering Logic
$from_date = $_GET['from_date'] ?? date('Y-m-d', strtotime('-30 days'));
$to_date = $_GET['to_date'] ?? date('Y-m-d');

// Fetch Metrics for selected period
$metrics_q = "SELECT 
    (SELECT COUNT(*) FROM orders WHERE DATE(order_date) BETWEEN '$from_date' AND '$to_date') as total_orders,
    (SELECT SUM(amount) FROM wallet_transactions WHERE type='SALE' AND DATE(created_at) BETWEEN '$from_date' AND '$to_date') as revenue,
    (SELECT SUM(amount) FROM wallet_transactions WHERE type='EXPENSE' AND DATE(created_at) BETWEEN '$from_date' AND '$to_date') as expenses,
    (SELECT COUNT(*) FROM customers WHERE DATE(created_at) BETWEEN '$from_date' AND '$to_date') as new_customers";
$metrics_res = mysqli_query($conn, $metrics_q);
$metrics = mysqli_fetch_assoc($metrics_res);

$revenue = (float)($metrics['revenue'] ?? 0);
$expenses = (float)($metrics['expenses'] ?? 0);
$profit = $revenue - $expenses;
$revenue_format = '₹' . number_format($revenue, 2);
$expense_format = '₹' . number_format($expenses, 2);
$profit_format = '₹' . number_format($profit, 2);

require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../includes/admin_sidebar.php';
require_once __DIR__ . '/../includes/admin_topbar.php';
?>

        <!-- PAGE CONTENT -->
        <main class="admin-content">

            <!-- Welcome Greeting -->
            <div class="flex items-center justify-between flex-wrap gap-4 mb-6 animate-in slide-up" style="position: relative; z-index: 50;">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight" style="margin-bottom:0.25rem;">Welcome back, <?php echo $admin_username; ?>! 👋</h2>
                    <p class="text-muted-foreground text-sm m-0">Here's an overview of your store today.</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="period-selector flex items-center" style="padding: 0; overflow: visible; border-radius: 9999px;">
                        <!-- Date Range Picker with Clean Spacing -->
                        <button class="period-btn flex items-center gap-2" id="dashboardDatePicker" style="border-right: 1px solid var(--border); border-radius: 9999px 0 0 9999px; padding: 0.45rem 1.1rem;">
                            <i class="bi bi-calendar3 me-2 text-primary"></i> 
                            <span id="dashboardDateText"><?= date('M d, Y', strtotime($from_date)) ?> - <?= date('M d, Y', strtotime($to_date)) ?></span>
                        </button>
                        
                        <!-- Quick Select Dropdown -->
                        <div class="dropdown">
                            <button class="period-btn flex items-center gap-1" style="border-radius: 0 9999px 9999px 0; padding: 0.45rem 1.1rem;" data-bs-toggle="dropdown" aria-expanded="false">
                                <span>Filter Period</span>
                                <i class="bi bi-chevron-down" style="font-size: 0.75rem; margin-top: 2px;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end admin-modal shadow-lg" style="margin-top: 0.5rem; background: var(--card); border: 1px solid var(--border); backdrop-filter: var(--glass-blur); border-radius: var(--radius); z-index: 1050;">
                                <li><a class="dropdown-item text-sm py-2" href="?from_date=<?= date('Y-m-d') ?>&to_date=<?= date('Y-m-d') ?>">Today</a></li>
                                <li><a class="dropdown-item text-sm py-2" href="?from_date=<?= date('Y-m-d', strtotime('-7 days')) ?>&to_date=<?= date('Y-m-d') ?>">Last 7 days</a></li>
                                <li><a class="dropdown-item text-sm py-2" href="?from_date=<?= date('Y-m-d', strtotime('-30 days')) ?>&to_date=<?= date('Y-m-d') ?>">Last 30 days</a></li>
                                <li><a class="dropdown-item text-sm py-2" href="?from_date=<?= date('Y-01-01') ?>&to_date=<?= date('Y-12-31') ?>">This Year</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="shadcn-btn shadcn-btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-download"></i> Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end admin-modal shadow-lg">
                            <li><a class="dropdown-item export-trigger" data-module="analytics" data-type="csv" href="#"><i class="bi bi-filetype-csv me-2"></i> CSV</a></li>
                            <li><a class="dropdown-item export-trigger" data-module="analytics" data-type="excel" href="#"><i class="bi bi-file-earmark-spreadsheet me-2"></i> Excel</a></li>
                            <li><a class="dropdown-item export-trigger" data-module="analytics" data-type="pdf" href="#"><i class="bi bi-file-earmark-pdf me-2"></i> PDF Report</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="kpi-grid mb-6">
                <!-- Total Orders -->
                <div class="shadcn-card animate-in slide-up delay-100">
                    <div class="shadcn-card-header flex flex-row items-center justify-between pb-2" style="padding-bottom: 0.5rem;">
                        <span class="text-sm font-medium">Orders Found</span>
                        <div class="glass-icon-box bg-primary-subtle">
                            <i class="bi bi-cart-check"></i>
                        </div>
                    </div>
                    <div class="shadcn-card-content">
                        <div class="text-2xl font-bold font-semibold"><?= (int)$metrics['total_orders'] ?></div>
                        <p class="text-xs text-muted-foreground mt-1 flex items-center gap-1">
                           Total orders in period
                        </p>
                    </div>
                </div>

                <!-- Total Sales -->
                <div class="shadcn-card animate-in slide-up delay-200">
                    <div class="shadcn-card-header flex flex-row items-center justify-between pb-2" style="padding-bottom: 0.5rem;">
                        <span class="text-sm font-medium">Total Revenue</span>
                        <div class="glass-icon-box bg-purple-subtle">
                            <i class="bi bi-bag-check"></i>
                        </div>
                    </div>
                    <div class="shadcn-card-content">
                        <div class="text-2xl font-bold font-semibold"><?= $revenue_format ?></div>
                        <p class="text-xs text-muted-foreground mt-1 flex items-center gap-1" style="color: var(--success); margin-bottom: 0;">
                            Gross sales volume
                        </p>
                    </div>
                </div>

                <!-- Amount Burned -->
                <div class="shadcn-card animate-in slide-up delay-300">
                    <div class="shadcn-card-header flex flex-row items-center justify-between pb-2" style="padding-bottom: 0.5rem;">
                        <span class="text-sm font-medium">Expenses</span>
                        <div class="glass-icon-box bg-warning-subtle">
                            <i class="bi bi-fire"></i>
                        </div>
                    </div>
                    <div class="shadcn-card-content">
                        <div class="text-2xl font-bold font-semibold"><?= $expense_format ?></div>
                        <p class="text-xs text-muted-foreground mt-1 flex items-center gap-1" style="color: var(--destructive); margin-bottom: 0;">
                           Total costs incurred
                        </p>
                    </div>
                </div>

                <!-- Profit Gained -->
                <div class="shadcn-card animate-in slide-up delay-400">
                    <div class="shadcn-card-header flex flex-row items-center justify-between pb-2" style="padding-bottom: 0.5rem;">
                        <span class="text-sm font-medium">Net Profit</span>
                        <div class="glass-icon-box bg-success-subtle">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                    </div>
                    <div class="shadcn-card-content">
                        <div class="text-2xl font-bold font-semibold"><?= $profit_format ?></div>
                        <p class="text-xs text-muted-foreground mt-1 flex items-center gap-1" style="color: var(--success); margin-bottom: 0;">
                           Estimated profitability
                        </p>
                    </div>
                </div>
            </div>

            <!-- Charts Section 1 -->
            <div class="shadcn-card mb-6 animate-in slide-up delay-500">
                <div class="shadcn-card-header flex flex-row items-center justify-between">
                    <div>
                        <h3 class="shadcn-card-title">Revenue Overview</h3>
                        <p class="shadcn-card-description mt-1">Monthly revenue trend for the current period</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="period-selector">
                            <button class="period-btn active" data-period="7D">7 Days</button>
                            <button class="period-btn" data-period="30D">30 Days</button>
                            <button class="period-btn" data-period="90D">90 Days</button>
                        </div>
                    </div>
                </div>
                <div class="shadcn-card-content">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>

            <!-- Charts Section 2 -->
            <div class="kpi-grid mb-6">
                <!-- Sales Breakdown -->
                <div class="shadcn-card animate-in slide-up delay-700">
                    <div class="shadcn-card-header">
                        <h3 class="shadcn-card-title">Sales Breakdown</h3>
                        <p class="shadcn-card-description mt-1">Services vs Product revenue split</p>
                    </div>
                    <div class="shadcn-card-content flex items-center justify-center gap-6" style="padding-top: 1rem;">
                        <div style="width: 150px; height: 150px;">
                            <canvas id="salesBreakdownChart"></canvas>
                        </div>
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center gap-2">
                                <span style="width:10px;height:10px;border-radius:50%;background:#10b981"></span>
                                <div>
                                    <p class="text-sm font-medium text-foreground m-0 leading-none">Products</p>
                                    <p class="text-xs text-muted-foreground m-0 mt-1">68%</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span style="width:10px;height:10px;border-radius:50%;background:#f59e0b"></span>
                                <div>
                                    <p class="text-sm font-medium text-foreground m-0 leading-none">Services</p>
                                    <p class="text-xs text-muted-foreground m-0 mt-1">32%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Order Trends Bar Chart -->
                <div class="shadcn-card animate-in slide-up delay-700">
                    <div class="shadcn-card-header">
                        <h3 class="shadcn-card-title">Recent Order Trends</h3>
                        <p class="shadcn-card-description mt-1">Orders placed over the last 7 days</p>
                    </div>
                    <div class="shadcn-card-content">
                        <canvas id="orderTrendsChart" height="120"></canvas>
                    </div>
                </div>
            </div>

            <!-- Flatpickr Datepicker -->
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
            <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const totalRevenue = <?= (float)$revenue ?>;
                const totalExpenses = <?= (float)$expenses ?>;
                
                // Initialize Flatpickr Date Range
                flatpickr("#dashboardDatePicker", {
                    mode: "range",
                    dateFormat: "Y-m-d",
                    altInput: false,
                    allowInput: false,
                    defaultDate: ["<?= $from_date ?>", "<?= $to_date ?>"],
                    onChange: function(selectedDates, dateStr, instance) {
                        if (selectedDates.length === 2) {
                            const params = new URLSearchParams(window.location.search);
                            params.set('from_date', instance.formatDate(selectedDates[0], "Y-m-d"));
                            params.set('to_date', instance.formatDate(selectedDates[1], "Y-m-d"));
                            // Use absolute location for refresh
                            window.location.href = window.location.pathname + '?' + params.toString();
                        }
                    }
                });

                // Quick Select Dropdown Logic
                const quickSelectItems = document.querySelectorAll('.admin-content .period-selector .dropdown-menu .dropdown-item');
                const quickSelectBtnText = document.querySelector('.admin-content .period-selector .dropdown .period-btn span');
                quickSelectItems.forEach(item => {
                    item.addEventListener('click', function(e) {
                        // Let link work, keep visually active
                        quickSelectItems.forEach(i => i.classList.remove('active'));
                        this.classList.add('active');
                    });
                });

                const htmlEl = document.documentElement;
                const getThemeColors = () => {
                    const isLight = htmlEl.getAttribute('data-theme') === 'light';
                    return {
                        text: isLight ? '#71717a' : 'rgba(255, 255, 255, 0.7)',
                        grid: isLight ? 'rgba(0, 0, 0, 0.05)' : 'rgba(255, 255, 255, 0.05)',
                        primary: '#3b82f6',
                        secondary: '#8b5cf6',
                        card: isLight ? '#ffffff' : '#09090b',
                        border: isLight ? '#e2e8f0' : '#27272a'
                    };
                };

                let colors = getThemeColors();
                Chart.defaults.font.family = "'Inter', sans-serif";
                Chart.defaults.color = colors.text;

                // 1. Revenue Line Chart with Dummy Data
                const revenueCtx = document.getElementById('revenueChart');
                let revenueChart;
                
                const dummyChartData = {
                    '7D': {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                        data: [12400, 18900, 14200, 22100, 19800, 28500, 24300]
                    },
                    '30D': {
                        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'],
                        data: [85000, 92000, 110000, 98000, 125000]
                    },
                    '90D': {
                        labels: ['Jan', 'Feb', 'Mar'],
                        data: [320000, 350000, 380000]
                    }
                };

                if(revenueCtx) {
                    revenueChart = new Chart(revenueCtx.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: dummyChartData['30D'].labels,
                            datasets: [{
                                label: 'Revenue trend',
                                data: dummyChartData['30D'].data,
                                borderColor: colors.secondary,
                                backgroundColor: 'rgba(139, 92, 246, 0.05)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 4,
                                pointBackgroundColor: colors.secondary,
                                pointBorderColor: htmlEl.getAttribute('data-theme') === 'light' ? '#ffffff' : '#09090b',
                                pointBorderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { grid: { display: false }, ticks: { color: colors.text } },
                                y: { 
                                    grid: { color: colors.grid, drawBorder: false }, 
                                    border: { display:false },
                                    ticks: { 
                                        color: colors.text,
                                        callback: (value) => '₹' + (value/1000).toFixed(0) + 'k'
                                    } 
                                }
                            }
                        }
                    });

                    // Handle Period Switching
                    const periodButtons = document.querySelectorAll('.shadcn-card .period-btn');
                    periodButtons.forEach(btn => {
                        btn.addEventListener('click', function() {
                            const period = this.getAttribute('data-period');
                            if(!dummyChartData[period]) return;

                            periodButtons.forEach(b => b.classList.remove('active'));
                            this.classList.add('active');

                            revenueChart.data.labels = dummyChartData[period].labels;
                            revenueChart.data.datasets[0].data = dummyChartData[period].data;
                            revenueChart.update();
                        });
                    });
                }

                // 2. Sales Breakdown (Doughnut)
                const breakdownCtx = document.getElementById('salesBreakdownChart');
                if(breakdownCtx){
                    new Chart(breakdownCtx.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: ['Revenue', 'Expenses'],
                            datasets: [{
                                data: [68, 32], // Dummy values for now
                                backgroundColor: ['#10b981', '#f59e0b'],
                                borderWidth: 0,
                                hoverOffset: 10
                            }]
                        },
                        options: { 
                            cutout: '75%',
                            plugins: { legend: { display: false } }
                        }
                    });
                }

                // 3. Trends (Bar)
                const trendsCtx = document.getElementById('orderTrendsChart');
                let trendsChart;
                if(trendsCtx) {
                    trendsChart = new Chart(trendsCtx.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                            datasets: [{
                                label: 'Orders',
                                data: [18, 25, 14, 32, 28, 42, 35],
                                backgroundColor: ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#06b6d4', '#6366f1'],
                                borderRadius: 6,
                                barThickness: 20
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                x: { grid: { display: false }, ticks: { color: colors.text } },
                                y: { 
                                    grid: { color: colors.grid, drawBorder: false }, 
                                    border: { display:false },
                                    ticks: { color: colors.text }
                                }
                            }
                        }
                    });
                }

                // Theme Observer
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'data-theme') {
                            const newColors = getThemeColors();
                            if(revenueChart) {
                                revenueChart.options.scales.x.ticks.color = newColors.text;
                                revenueChart.options.scales.y.ticks.color = newColors.text;
                                revenueChart.options.scales.y.grid.color = newColors.grid;
                                revenueChart.update();
                            }
                            if(trendsChart) {
                                trendsChart.options.scales.x.ticks.color = newColors.text;
                                trendsChart.options.scales.y.ticks.color = newColors.text;
                                trendsChart.options.scales.y.grid.color = newColors.grid;
                                trendsChart.update();
                            }
                        }
                    });
                });
                observer.observe(htmlEl, { attributes: true });
            });
            </script>

        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
