/**
 * BIKE BARBER — Admin Dashboard JS
 * Sidebar toggle, dropdowns, keyboard shortcuts
 */
document.addEventListener('DOMContentLoaded', function () {

    const sidebar      = document.getElementById('adminSidebar');
    const overlay      = document.getElementById('sidebarOverlay');
    const toggleBtn    = document.getElementById('sidebarToggle');
    const notifBtn     = document.getElementById('notificationBtn');
    const notifDrop    = document.getElementById('notificationDropdown');
    const profileBtn   = document.getElementById('profileBtn');
    const profileDrop  = document.getElementById('profileDropdown');

    const MOBILE = 992;

    // ── Sidebar Toggle ──
    function isMobile() { return window.innerWidth < MOBILE; }

    function toggleSidebar() {
        if (isMobile()) {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('show');
            document.body.classList.toggle('sidebar-mobile-open');
        } else {
            sidebar.classList.toggle('collapsed');
            document.body.classList.toggle('sidebar-collapsed');
            try { localStorage.setItem('sidebar_collapsed', sidebar.classList.contains('collapsed')); } catch(e) {}
        }
    }

    // Restore saved state on desktop
    if (!isMobile()) {
        try {
            if (localStorage.getItem('sidebar_collapsed') === 'true') {
                sidebar.classList.add('collapsed');
                document.body.classList.add('sidebar-collapsed');
            }
        } catch(e) {}
    }

    if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);

    // Close sidebar on overlay click (mobile)
    if (overlay) {
        overlay.addEventListener('click', function () {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('show');
            document.body.classList.remove('sidebar-mobile-open');
        });
    }

    // ── Dropdowns ──
    function closeAllDropdowns() {
        if (notifDrop) notifDrop.classList.remove('show');
        if (profileDrop) profileDrop.classList.remove('show');
        if (profileBtn) profileBtn.classList.remove('active');
    }

    if (notifBtn) {
        notifBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const open = notifDrop.classList.contains('show');
            closeAllDropdowns();
            if (!open) notifDrop.classList.add('show');
        });
    }

    if (profileBtn) {
        profileBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const open = profileDrop.classList.contains('show');
            closeAllDropdowns();
            if (!open) {
                profileDrop.classList.add('show');
                profileBtn.classList.add('active');
            }
        });
    }

    document.addEventListener('click', function (e) {
        if (notifDrop && !notifDrop.contains(e.target) && e.target !== notifBtn) {
            notifDrop.classList.remove('show');
        }
        if (profileDrop && !profileDrop.contains(e.target) && !profileBtn?.contains(e.target)) {
            profileDrop.classList.remove('show');
            if (profileBtn) profileBtn.classList.remove('active');
        }
    });

    // ── Keyboard Shortcuts ──
    document.addEventListener('keydown', function (e) {
        if (e.ctrlKey && e.key === 'b') {
            e.preventDefault();
            toggleSidebar();
        }
        if (e.key === 'Escape') {
            closeAllDropdowns();
            if (isMobile()) {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('show');
                document.body.classList.remove('sidebar-mobile-open');
            }
        }
    });

    // ── Auto-collapse on resize ──
    window.addEventListener('resize', function () {
        if (isMobile()) {
            sidebar.classList.remove('collapsed');
            document.body.classList.remove('sidebar-collapsed');
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('show');
            document.body.classList.remove('sidebar-mobile-open');
        }
    });

    // ── Active sidebar link ──
    const currentFile = window.location.pathname.split('/').pop();
    document.querySelectorAll('.sidebar-nav-item[data-page]').forEach(function (item) {
        item.classList.toggle('active', item.dataset.page === currentFile);
    });

    // ── Search auto-focus ──
    const searchInput = document.querySelector('.search-input-wrap input');
    document.addEventListener('keydown', function (e) {
        if (e.key === '/' && document.activeElement.tagName !== 'INPUT') {
            e.preventDefault();
            if (searchInput) searchInput.focus();
        }
    });

    // ── Tab switching (generic) ──
    document.querySelectorAll('.data-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            const parent = tab.closest('.data-card');
            if (!parent) return;
            parent.querySelectorAll('.data-tab').forEach(function (t) { t.classList.remove('active'); });
            tab.classList.add('active');
            const target = tab.dataset.tab;
            if (target) {
                parent.querySelectorAll('.tab-pane').forEach(function (pane) {
                    pane.style.display = pane.id === target ? '' : 'none';
                });
            }
        });
    });

    // ── Layout Observer (Fixes Chart.js Clipping on Sidebar Hover/Toggle) ──
    const mainContent = document.querySelector('.admin-main');
    if (mainContent && window.Chart) {
        const resizeObserver = new ResizeObserver(() => {
            // Trigger Resize for all active Chart.js instances
            Object.values(Chart.instances).forEach(chart => {
                chart.resize();
            });
        });
        resizeObserver.observe(mainContent);
    }
});
