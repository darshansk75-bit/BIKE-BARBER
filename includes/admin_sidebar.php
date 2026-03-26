<?php
// Determine active page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="admin-wrapper">
    <aside class="admin-sidebar" id="adminSidebar">
        <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="sidebar-brand">
            <div class="sidebar-brand-icon"><i class="bi bi-bicycle"></i></div>
            <div class="sidebar-brand-text">BIKE BARBER</div>
        </a>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Main</div>
            <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="sidebar-nav-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i><span>Analytics</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/products.php" class="sidebar-nav-item <?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
                <i class="bi bi-box-seam"></i><span>Products</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/orders.php" class="sidebar-nav-item <?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">
                <i class="bi bi-cart-check"></i><span>Orders</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/wallet.php" class="sidebar-nav-item <?php echo $current_page == 'wallet.php' ? 'active' : ''; ?>">
                <i class="bi bi-wallet2"></i><span>Wallet</span>
            </a>
            
            <div class="nav-section-label mt-4">Management</div>
            <a href="<?php echo SITE_URL; ?>/admin/bookings.php" class="sidebar-nav-item <?php echo $current_page == 'bookings.php' ? 'active' : ''; ?>">
                <i class="bi bi-calendar-check"></i><span>Service Bookings</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/manufacturers.php" class="sidebar-nav-item <?php echo $current_page == 'manufacturers.php' ? 'active' : ''; ?>">
                <i class="bi bi-building"></i><span>Manufacturers</span>
            </a>
        </nav>
        
        <div class="sidebar-logout-container p-6 mt-auto">
            <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="shadcn-btn shadcn-btn-ghost w-full justify-start text-muted-foreground sidebar-logout-btn" onclick="return confirm('Logout?');" style="color: var(--muted-foreground); display: flex; gap: 0.5rem;">
                <i class="bi bi-box-arrow-right"></i><span>Logout</span>
            </a>
        </div>
    </aside>

    <div class="admin-main">
