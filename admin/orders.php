<?php
/**
 * Admin — Orders Management
 * PATH: /admin/orders.php
 */
require_once __DIR__ . '/../includes/session_auth.php';
auth_guard('admin');

$page_title = 'Orders';
$admin_username = htmlspecialchars($_SESSION['username'] ?? 'Admin');
$admin_email = htmlspecialchars($_SESSION['email'] ?? 'admin@bikebarber.com');

require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../includes/admin_sidebar.php';
require_once __DIR__ . '/../includes/admin_topbar.php';
?>

        <main class="admin-content">

            <!-- Action Bar -->
            <div class="flex items-center justify-between mb-6 animate-in slide-up" style="position: relative; z-index: 50;">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight m-0">Order Management</h2>
                    <p class="text-muted-foreground text-sm mt-1 mb-0">Track, manage, and export all customer orders.</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="dropdown">
                        <button class="shadcn-btn shadcn-btn-outline" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-download"></i> Export / Download
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end admin-modal shadow-lg">
                            <li><a class="dropdown-item export-trigger" data-module="orders" data-type="csv" href="#"><i class="bi bi-filetype-csv me-2"></i> CSV</a></li>
                            <li><a class="dropdown-item export-trigger" data-module="orders" data-type="excel" href="#"><i class="bi bi-file-earmark-spreadsheet me-2"></i> Excel</a></li>
                            <li><a class="dropdown-item export-trigger" data-module="orders" data-type="pdf" href="#"><i class="bi bi-file-earmark-pdf me-2"></i> PDF Report</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Order Stats Row -->
            <div class="kpi-grid mb-6">
                <div class="shadcn-card animate-in slide-up delay-100">
                    <div class="shadcn-card-header flex flex-row items-center justify-between pb-2" style="padding-bottom: 0.5rem;">
                        <span class="text-sm font-medium">Total Orders</span>
                        <div class="glass-icon-box bg-primary-subtle">
                            <i class="bi bi-cart-check"></i>
                        </div>
                    </div>
                    <div class="shadcn-card-content">
                        <div class="text-2xl font-bold font-semibold">284</div>
                    </div>
                </div>

                <div class="shadcn-card animate-in slide-up delay-200">
                    <div class="shadcn-card-header flex flex-row items-center justify-between pb-2" style="padding-bottom: 0.5rem;">
                        <span class="text-sm font-medium">Pending</span>
                        <div class="glass-icon-box bg-warning-subtle">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                    <div class="shadcn-card-content">
                        <div class="text-2xl font-bold font-semibold">18</div>
                    </div>
                </div>

                <div class="shadcn-card animate-in slide-up delay-300">
                    <div class="shadcn-card-header flex flex-row items-center justify-between pb-2" style="padding-bottom: 0.5rem;">
                        <span class="text-sm font-medium">Completed</span>
                        <div class="glass-icon-box bg-success-subtle">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                    <div class="shadcn-card-content">
                        <div class="text-2xl font-bold font-semibold">248</div>
                    </div>
                </div>

                <div class="shadcn-card animate-in slide-up delay-400">
                    <div class="shadcn-card-header flex flex-row items-center justify-between pb-2" style="padding-bottom: 0.5rem;">
                        <span class="text-sm font-medium">Cancelled</span>
                        <div class="glass-icon-box bg-destructive-subtle">
                            <i class="bi bi-x-circle"></i>
                        </div>
                    </div>
                    <div class="shadcn-card-content">
                        <div class="text-2xl font-bold font-semibold">18</div>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="shadcn-card animate-in slide-up delay-500">
                <div class="p-6 flex items-center justify-between gap-4 border-b border-border" style="border-bottom: 1px solid var(--border)">
                    <div class="flex flex-1 items-center gap-2 max-w-sm" style="position: relative;">
                        <i class="bi bi-search text-muted-foreground" style="position: absolute; left: 0.75rem;"></i>
                        <input type="text" id="orderSearch" class="shadcn-input" placeholder="Search by Order ID, Customer..." style="padding-left: 2.25rem;" autocomplete="off">
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <select class="shadcn-input" id="statusFilter" style="width: auto;">
                            <option value="">All Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Processing">Processing</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                        <select class="shadcn-input" id="paymentFilter" style="width: auto;">
                            <option value="">All Payments</option>
                            <option value="Paid">Paid</option>
                            <option value="Pending">Pending</option>
                            <option value="Refunded">Refunded</option>
                        </select>
                    </div>
                </div>

                <div class="shadcn-table-wrapper" style="border-radius: 0; border: none;">
                    <table class="shadcn-table" id="ordersTable">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Product ID</th>
                                <th>Price</th>
                                <th>Order Status</th>
                                <th>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $orders_q = "SELECT o.*, c.name as customer_name, c.email as customer_email, 
                                        GROUP_CONCAT(p.product_name SEPARATOR ', ') as products,
                                        SUM(oi.quantity * oi.price) as total_price
                                        FROM orders o
                                        JOIN customers c ON o.customer_id = c.customer_id
                                        JOIN order_items oi ON o.order_id = oi.order_id
                                        JOIN products p ON oi.product_id = p.product_id
                                        GROUP BY o.order_id
                                        ORDER BY o.order_date DESC";
                            $orders_res = mysqli_query($conn, $orders_q);
                            
                            if (mysqli_num_rows($orders_res) > 0):
                                while($order = mysqli_fetch_assoc($orders_res)):
                                    $status_badge = 'bg-secondary-subtle';
                                    if($order['order_status'] == 'Completed') $status_badge = 'bg-success-subtle';
                                    if($order['order_status'] == 'Cancelled') $status_badge = 'bg-destructive-subtle';
                                    if($order['order_status'] == 'Processing') $status_badge = 'bg-primary-subtle';
                                    
                                    $pay_badge = 'bg-secondary-subtle';
                                    if($order['payment_status'] == 'Paid') $pay_badge = 'bg-success-subtle';
                                    if($order['payment_status'] == 'Refunded') $pay_badge = 'bg-destructive-subtle';
                            ?>
                            <tr id="order-row-<?= $order['order_id'] ?>">
                                <td><span style="font-family: monospace;">ORD<?= str_pad($order['order_id'], 4, '0', STR_PAD_LEFT) ?></span></td>
                                <td>
                                    <div class="flex-col">
                                        <span class="font-medium text-foreground"><?= htmlspecialchars($order['customer_name']) ?></span>
                                        <div class="text-xs text-muted-foreground mt-1" style="font-family: monospace;"><?= htmlspecialchars($order['customer_email']) ?></div>
                                    </div>
                                </td>
                                <td><span class="text-muted-foreground text-sm"><?= date('d M Y', strtotime($order['order_date'])) ?></span></td>
                                <td><div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($order['products']) ?>"><?= htmlspecialchars($order['products']) ?></div></td>
                                <td><span style="font-family: monospace;">-</span></td>
                                <td class="font-medium">₹<?= number_format($order['total_price'], 2) ?></td>
                                <td><span class="shadcn-badge <?= $status_badge ?> status-label"><?= $order['order_status'] ?></span></td>
                                <td><span class="shadcn-badge <?= $pay_badge ?>"><?= $order['payment_status'] ?></span></td>
                                <td class="flex items-center gap-1">
                                    <button class="shadcn-btn shadcn-btn-ghost shadcn-btn-icon order-action" data-id="<?= $order['order_id'] ?>" data-action="accept" title="Accept Order"><i class="bi bi-check-lg text-success"></i></button>
                                    <button class="shadcn-btn shadcn-btn-ghost shadcn-btn-icon order-action" data-id="<?= $order['order_id'] ?>" data-action="reject" title="Reject Order"><i class="bi bi-x-circle text-warning"></i></button>
                                    <button class="shadcn-btn shadcn-btn-ghost shadcn-btn-icon order-action" data-id="<?= $order['order_id'] ?>" data-action="delete" title="Delete Order"><i class="bi bi-trash text-destructive"></i></button>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-8 text-muted-foreground">No orders found matching your criteria.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-4 flex items-center justify-between border-t border-border mt-0" style="border-top: 1px solid var(--border)">
                    <span class="text-sm text-muted-foreground">Showing 1-4 of 284 orders</span>
                    <div class="flex items-center gap-1">
                        <button class="shadcn-btn shadcn-btn-outline shadcn-btn-icon" disabled style="height:2rem; width:2rem;"><i class="bi bi-chevron-left"></i></button>
                        <button class="shadcn-btn shadcn-btn-primary shadcn-btn-icon" style="height:2rem; width:2rem;">1</button>
                        <button class="shadcn-btn shadcn-btn-outline shadcn-btn-icon" style="height:2rem; width:2rem;">2</button>
                        <span class="text-muted-foreground px-2">...</span>
                        <button class="shadcn-btn shadcn-btn-outline shadcn-btn-icon" style="height:2rem; width:2rem;">41</button>
                        <button class="shadcn-btn shadcn-btn-outline shadcn-btn-icon" style="height:2rem; width:2rem;"><i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Search Logic ──
    const search = document.getElementById('orderSearch');
    if (search) {
        search.addEventListener('input', function() {
            const q = search.value.toLowerCase();
            document.querySelectorAll('#ordersTable tbody tr').forEach(function(r) {
                if (r.querySelector('td[colspan]')) return;
                r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

    // ── Action Handlers ──
    document.querySelectorAll('.order-action').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const action = this.dataset.action;
            const row = document.getElementById('order-row-' + id);

            if (!confirm(`Are you sure you want to ${action} this order?`)) return;

            const formData = new FormData();
            formData.append('order_id', id);
            formData.append('action', action);

            fetch('ajax/ajax_handle_order.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    if (action === 'delete') {
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 300);
                    } else {
                        const label = row.querySelector('.status-label');
                        label.textContent = (action === 'accept') ? 'Completed' : 'Cancelled';
                        label.className = 'shadcn-badge ' + (action === 'accept' ? 'bg-success-subtle' : 'bg-destructive-subtle') + ' status-label';
                    }
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Order Action Error:', err);
                alert('Something went wrong. Please try again.');
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
