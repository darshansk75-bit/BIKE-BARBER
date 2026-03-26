<?php
/**
 * Customer Dashboard
 * PATH: /customer/dashboard.php
 */
require_once __DIR__ . '/../includes/session_auth.php';

// Security Guard
auth_guard('customer');

$page_title = 'Customer Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="fixed top-0 left-0 right-0 z-50 bg-white/70 backdrop-blur-md border-b border-slate-200 px-6 py-4">
    <div class="flex justify-between items-center w-full max-w-6xl mx-auto">
        <div class="text-2xl font-extrabold tracking-tight text-slate-800 flex items-center">
            BIKE <span class="text-indigo-600 ml-1">BARBER</span>
        </div>
        <div class="flex items-center space-x-4">
            <span class="text-slate-500 text-sm hidden sm:inline-block">Logged in as <strong>Customer</strong></span>
            <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 border border-red-200 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center">
                <i class="bi bi-box-arrow-right mr-1"></i> Logout
            </a>
        </div>
    </div>
</div>

<!-- Add top padding to account for fixed header -->
<div class="container mx-auto px-4 pt-32 pb-8 animate-fade-up">

    <!-- Welcome Card -->
    <div class="glass-panel-v3 rounded-3xl p-8 md:p-12 text-center md:text-left shadow-xl relative overflow-hidden w-full max-w-5xl mx-auto border-white/40">
        <!-- Decorative Glow -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-purple-500/10 rounded-full blur-3xl"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-center gap-8">
            <div class="w-24 h-24 bg-gradient-to-br from-brand-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg shadow-brand-500/20 text-4xl text-white font-bold flex-shrink-0">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
            </div>
            
            <div>
                <h1 class="text-3xl md:text-4xl font-light text-slate-800 mb-2">
                    Welcome back, <span class="text-indigo-600 font-semibold"><?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                </h1>
                <p class="text-slate-500 text-lg max-w-2xl font-light">
                    Your sleek new customer dashboard is ready. Browse accessories, book services, and manage your orders all from one place.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-up {
        animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
