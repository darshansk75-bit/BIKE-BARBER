<?php
/**
 * Admin — Service Bookings Management
 * PATH: /admin/bookings.php
 */
require_once __DIR__ . '/../includes/session_auth.php';
auth_guard('admin');

$page_title = 'Service Bookings';
$admin_username = htmlspecialchars($_SESSION['username'] ?? 'Admin');
$admin_email = htmlspecialchars($_SESSION['email'] ?? 'admin@bikebarber.com');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../includes/admin_sidebar.php';
require_once __DIR__ . '/../includes/admin_topbar.php';
?>

        <main class="admin-content">
            <?php if (isset($_GET['msg'])): ?>
                <?php if ($_GET['msg'] === 'success'): ?>
                    <div class="alert alert-success alert-dismissible fade show mb-4 animate-in fade-in" role="alert" style="background: rgba(22, 163, 74, 0.1); border: 1px solid var(--success); color: var(--success); border-radius: var(--radius); padding: 1rem; border-left: 4px solid var(--success);">
                        <i class="bi bi-check-circle-fill me-2"></i> Action completed successfully.
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close" style="filter: var(--theme-icon-filter);"></button>
                    </div>
                <?php elseif ($_GET['msg'] === 'error'): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4 animate-in fade-in" role="alert" style="background: rgba(153, 27, 27, 0.1); border: 1px solid var(--destructive); color: var(--foreground); border-radius: var(--radius); padding: 1rem; border-left: 4px solid var(--destructive);">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Error:</strong> <?= htmlspecialchars($_GET['err'] ?? 'Failed to process request.') ?>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close" style="filter: var(--theme-icon-filter);"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="flex items-center justify-between mb-6 animate-in slide-up">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight m-0">Service Bookings</h2>
                    <p class="text-muted-foreground text-sm mt-1 mb-0">Manage bike service appointments, track progress, and schedule slots.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="shadcn-btn shadcn-btn-secondary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                        <i class="bi bi-gear"></i> Add Service
                    </button>
                    <button class="shadcn-btn shadcn-btn-primary" data-bs-toggle="modal" data-bs-target="#newBookingModal">
                        <i class="bi bi-plus-lg"></i> New Booking
                    </button>
                </div>
            </div>

            <!-- Booking KPIs -->
            <div class="kpi-grid mb-6">
                <div class="shadcn-card animate-in slide-up delay-100">
                    <div class="shadcn-card-header flex flex-row items-center justify-between pb-2" style="padding-bottom: 0.5rem;">
                        <span class="text-sm font-medium">Total Bookings</span>
                        <div class="glass-icon-box" style="color: var(--primary); background: rgba(250, 250, 250, 0.1);">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                    </div>
                    <div class="shadcn-card-content">
                        <div class="text-2xl font-bold font-semibold">156</div>
                    </div>
                </div>

                <div class="shadcn-card animate-in slide-up delay-200">
                    <div class="shadcn-card-header flex flex-row items-center justify-between pb-2" style="padding-bottom: 0.5rem;">
                        <span class="text-sm font-medium">Awaiting Confirm</span>
                        <div class="glass-icon-box" style="color: var(--warning); background: rgba(234, 88, 12, 0.1);">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                    <div class="shadcn-card-content">
                        <div class="text-2xl font-bold font-semibold">12</div>
                    </div>
                </div>

                <div class="shadcn-card animate-in slide-up delay-300">
                    <div class="shadcn-card-header flex flex-row items-center justify-between pb-2" style="padding-bottom: 0.5rem;">
                        <span class="text-sm font-medium">In Progress</span>
                        <div class="glass-icon-box" style="color: #60a5fa; background: rgba(96, 165, 250, 0.1);">
                            <i class="bi bi-tools"></i>
                        </div>
                    </div>
                    <div class="shadcn-card-content">
                        <div class="text-2xl font-bold font-semibold">8</div>
                    </div>
                </div>

                <div class="shadcn-card animate-in slide-up delay-400">
                    <div class="shadcn-card-header flex flex-row items-center justify-between pb-2" style="padding-bottom: 0.5rem;">
                        <span class="text-sm font-medium">Completed</span>
                        <div class="glass-icon-box" style="color: var(--success); background: rgba(22, 163, 74, 0.1);">
                            <i class="bi bi-check2-all"></i>
                        </div>
                    </div>
                    <div class="shadcn-card-content">
                        <div class="text-2xl font-bold font-semibold">136</div>
                    </div>
                </div>
            </div>

            <!-- Booking Analytics Histogram -->
            <div class="shadcn-card mb-6 animate-in slide-up delay-500">
                <div class="shadcn-card-header">
                    <h3 class="shadcn-card-title">Booking Analytics</h3>
                    <p class="shadcn-card-description">Customer booked slots over time</p>
                </div>
                <div class="shadcn-card-content">
                    <div style="height: 300px;">
                        <canvas id="bookingHistogramChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Bookings Table -->
            <div class="shadcn-card">
                <!-- Tabs -->
                <div class="flex items-center border-b border-border" style="border-bottom: 1px solid var(--border)">
                    <button class="data-tab active" data-target="allBookings">
                        <i class="bi bi-list-ul"></i> All Bookings
                    </button>
                    <button class="data-tab" data-target="todayBookings">
                        <i class="bi bi-calendar-day"></i> Today
                    </button>
                    <button class="data-tab" data-target="upcomingBookings">
                        <i class="bi bi-calendar-week"></i> Upcoming
                    </button>
                    <button class="data-tab" data-target="serviceCatalog">
                        <i class="bi bi-card-list"></i> Service Catalog
                    </button>
                </div>

                <!-- Search & Filter Bar -->
                <div class="p-6 flex items-center justify-between gap-4">
                    <div class="flex flex-1 items-center gap-2 max-w-sm" style="position: relative;">
                        <i class="bi bi-search text-muted-foreground" style="position: absolute; left: 0.75rem;"></i>
                        <input type="text" id="bookingSearch" class="shadcn-input" placeholder="Search by Booking ID, Customer, Vehicle..." style="padding-left: 2.25rem;" autocomplete="off">
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <select class="shadcn-input" id="serviceTypeFilter" style="width: auto;">
                            <option value="">All Services</option>
                            <option value="Full Service">Full Service</option>
                            <option value="Oil Change">Oil Change</option>
                            <option value="Brake Service">Brake Service</option>
                            <option value="Chain Service">Chain Service</option>
                            <option value="General Checkup">General Checkup</option>
                        </select>
                        <select class="shadcn-input" id="bookingStatusFilter" style="width: auto;">
                            <option value="">All Status</option>
                            <option value="Requested">Requested</option>
                            <option value="Confirmed">Confirmed</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <!-- Tab: All Bookings -->
                <div class="data-tab-pane active" id="allBookings">
                    <div class="shadcn-table-wrapper" style="border-radius: 0; border: none;">
                        <table class="shadcn-table" id="bookingsTable">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>Service Type</th>
                                    <th>Date & Slot</th>
                                    <th>Estimated Cost</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $limit = 5;
                                $page = max(1, intval($_GET['page'] ?? 1));
                                $offset = ($page - 1) * $limit;

                                // Count total
                                $count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM service_bookings");
                                $total_bookings = mysqli_fetch_assoc($count_res)['total'];
                                $total_pages = ceil($total_bookings / $limit);

                                $booking_sql = "SELECT sb.*, c.name as customer_name, c.customer_id as customer_ref, s.service_name, s.price 
                                               FROM service_bookings sb
                                               JOIN customers c ON sb.customer_id = c.customer_id
                                               JOIN services s ON sb.service_id = s.service_id
                                               ORDER BY sb.booking_id DESC
                                               LIMIT $limit OFFSET $offset";
                                $booking_res = mysqli_query($conn, $booking_sql);
                                
                                if (mysqli_num_rows($booking_res) > 0) {
                                    while ($b = mysqli_fetch_assoc($booking_res)) {
                                        $id = $b['booking_id'];
                                        $status = $b['status'];
                                        
                                        // Badge styling based on status
                                        $badge_styles = [
                                            'Requested' => 'color:var(--warning); background:rgba(234, 88, 12, 0.1)',
                                            'Confirmed' => 'color:var(--primary); background:rgba(250, 250, 250, 0.1)',
                                            'In Progress' => 'color:#60a5fa; background:rgba(96, 165, 250, 0.1)',
                                            'Completed' => 'color:var(--success); background:rgba(22, 163, 74, 0.1)',
                                            'Cancelled' => 'color:var(--destructive); background:rgba(153, 27, 27, 0.1)'
                                        ];
                                        $s_style = $badge_styles[$status] ?? 'color:var(--muted-foreground); background:var(--muted)';
                                ?>
                                <tr>
                                    <td><span style="font-family: monospace;">BK<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?></span></td>
                                    <td>
                                        <div class="flex-col">
                                            <span class="font-medium text-foreground"><?= htmlspecialchars($b['customer_name']) ?></span>
                                            <div class="text-xs text-muted-foreground mt-1" style="font-family: monospace;">CUST<?= str_pad($b['customer_ref'], 4, '0', STR_PAD_LEFT) ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex-col">
                                            <span class="font-medium text-foreground"><?= htmlspecialchars($b['vehicle_model']) ?></span>
                                            <div class="text-xs text-muted-foreground mt-1" style="font-family: monospace;"><?= htmlspecialchars($b['vehicle_number']) ?></div>
                                        </div>
                                    </td>
                                    <td><span class="text-sm font-medium text-primary"><?= htmlspecialchars($b['service_name']) ?></span></td>
                                    <td><div class="flex-col"><span class="text-sm text-foreground"><?= date('d M Y', strtotime($b['booking_date'])) ?></span><span class="text-xs text-muted-foreground mt-1"><?= date('h:i A', strtotime($b['booking_time'])) ?></span></div></td>
                                    <td class="font-medium">₹<?= number_format($b['price'], 2) ?></td>
                                    <td><span class="shadcn-badge shadcn-badge-secondary" style="<?= $s_style ?>"><?= $status ?></span></td>
                                    <td class="flex items-center gap-1">
                                        <?php if ($status === 'Requested'): ?>
                                            <button class="shadcn-btn shadcn-btn-ghost shadcn-btn-icon text-success hover:bg-success/10 update-status" data-id="<?= $id ?>" data-status="Confirmed" title="Confirm"><i class="bi bi-check-lg"></i></button>
                                            <button class="shadcn-btn shadcn-btn-ghost shadcn-btn-icon text-destructive hover:bg-destructive/10 update-status" data-id="<?= $id ?>" data-status="Cancelled" title="Reject"><i class="bi bi-x-lg"></i></button>
                                        <?php elseif ($status === 'Confirmed'): ?>
                                            <button class="shadcn-btn shadcn-btn-ghost shadcn-btn-icon text-primary update-status" data-id="<?= $id ?>" data-status="In Progress" title="Start Service"><i class="bi bi-play"></i></button>
                                        <?php elseif ($status === 'In Progress'): ?>
                                            <button class="shadcn-btn shadcn-btn-ghost shadcn-btn-icon text-success update-status" data-id="<?= $id ?>" data-status="Completed" title="Complete"><i class="bi bi-check2-all"></i></button>
                                        <?php endif; ?>
                                        <button class="shadcn-btn shadcn-btn-ghost shadcn-btn-icon text-destructive hover:bg-destructive/10 delete-booking" data-id="<?= $id ?>" title="Delete Record"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                <tr>
                                    <td colspan="8" class="text-center p-8 text-muted-foreground">
                                        <i class="bi bi-calendar-x block text-4xl mb-3 opacity-20"></i>
                                        No bookings found. Create one to begin.
                                    </td>
                                </tr>
                                <?php } ?>
  </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab: Today -->
                <div class="data-tab-pane" id="todayBookings">
                    <div class="shadcn-table-wrapper" style="border-radius: 0; border: none;">
                        <p class="p-6 text-muted-foreground text-sm m-0">Filtered today's bookings...</p>
                    </div>
                </div>

                <!-- Tab: Upcoming -->
                <div class="data-tab-pane" id="upcomingBookings">
                    <div class="shadcn-table-wrapper" style="border-radius: 0; border: none;">
                        <p class="p-6 text-muted-foreground text-sm m-0">Filtered upcoming bookings...</p>
                    </div>
                </div>

                <!-- Tab: Service Catalog -->
                <div class="data-tab-pane" id="serviceCatalog">
                    <div class="shadcn-table-wrapper" style="border-radius: 0; border: none;">
                        <table class="shadcn-table">
                            <thead>
                                <tr>
                                    <th>Service Name</th>
                                    <th>Category/Type</th>
                                    <th>Price</th>
                                    <th>Duration</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $svc_res = mysqli_query($conn, "SELECT * FROM services ORDER BY service_id DESC");
                                if (mysqli_num_rows($svc_res) > 0) {
                                    while ($s = mysqli_fetch_assoc($svc_res)) {
                                ?>
                                <tr>
                                    <td class="font-medium"><?= htmlspecialchars($s['service_name']) ?></td>
                                    <td><span class="shadcn-badge shadcn-badge-outline"><?= htmlspecialchars($s['service_type']) ?></span></td>
                                    <td class="font-bold text-foreground">₹<?= number_format($s['price'], 2) ?></td>
                                    <td>
                                        <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                            <i class="bi bi-clock"></i>
                                            <span><?= $s['duration_minutes'] ?> Mins</span>
                                        </div>
                                    </td>
                                    <td class="text-sm text-muted-foreground"><?= htmlspecialchars($s['description'] ?: 'N/A') ?></td>
                                </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center p-8 text-muted-foreground">
                                        <i class="bi bi-gear-wide-connected block text-4xl mb-3 opacity-20 animate-spin-slow"></i>
                                        No services found in catalog. Create one to begin.
                                    </td>
                                </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="p-4 flex items-center justify-between border-t border-border mt-0" style="border-top: 1px solid var(--border)">
                    <span class="text-sm text-muted-foreground">Showing <?= min($offset + 1, $total_bookings) ?> to <?= min($offset + $limit, $total_bookings) ?> of <?= $total_bookings ?> bookings</span>
                    <div class="flex items-center gap-1">
                        <!-- Prev -->
                        <a href="?page=<?= max(1, $page-1) ?>" class="shadcn-btn shadcn-btn-outline shadcn-btn-icon <?= ($page <= 1) ? 'disabled pointer-events-none opacity-50' : '' ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                        
                        <?php 
                        // Show a few pages around current
                        $start_loop = max(1, $page - 1);
                        $end_loop = min($total_pages, $page + 1);
                        
                        if($start_loop > 1) echo '<span class="text-muted-foreground px-1">...</span>';
                        
                        for($i=$start_loop; $i<=$end_loop; $i++): 
                        ?>
                            <a href="?page=<?= $i ?>" class="shadcn-btn <?= ($i == $page) ? 'shadcn-btn-primary' : 'shadcn-btn-outline' ?> shadcn-btn-icon">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if($end_loop < $total_pages): ?>
                            <span class="text-muted-foreground px-1">...</span>
                            <a href="?page=<?= $total_pages ?>" class="shadcn-btn shadcn-btn-outline shadcn-btn-icon">
                                <?= $total_pages ?>
                            </a>
                        <?php endif; ?>

                        <!-- Next -->
                        <a href="?page=<?= min($total_pages, $page+1) ?>" class="shadcn-btn shadcn-btn-outline shadcn-btn-icon <?= ($page >= $total_pages) ? 'disabled pointer-events-none opacity-50' : '' ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- MODAL: NEW BOOKING -->
<div class="modal fade" id="newBookingModal" tabindex="-1" aria-labelledby="newBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content admin-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="newBookingModalLabel"><i class="bi bi-calendar-plus me-2"></i>New Service Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="newBookingForm" method="POST" action="process_booking.php" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6 mb-2">
                            <label class="text-sm font-medium mb-2 block" for="bkCustomer">Customer <span class="text-destructive">*</span></label>
                            <div class="flex items-center gap-2">
                                <select class="shadcn-input flex-1" id="bkCustomer" name="customer_id" required>
                                    <option value="">Select customer...</option>
                                    <?php
                                    $cust_res = mysqli_query($conn, "SELECT customer_id, name FROM customers ORDER BY name ASC");
                                    while($c = mysqli_fetch_assoc($cust_res)) {
                                        echo "<option value='{$c['customer_id']}'>{$c['name']} (CUST" . str_pad($c['customer_id'], 4, '0', STR_PAD_LEFT) . ")</option>";
                                    }
                                    ?>
                                </select>
                                <button type="button" class="shadcn-btn shadcn-btn-outline b-sm px-2 py-1 h-9 flex-shrink-0" onclick="toggleQuickAdd()" title="Quick Add New Customer">
                                    <i class="bi bi-person-plus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- QUICK ADD CUSTOMER INLINE FORM -->
                        <div class="col-12" id="quickAddCustomerSection" style="display: none;">
                            <div class="p-3 border border-dashed border-primary/30 rounded-lg bg-primary/5 animate-in slide-in-from-top-4">
                                <h6 class="text-xs font-bold uppercase tracking-wider text-primary mb-3"><i class="bi bi-person-plus-fill me-1"></i> Quick Add New Customer</h6>
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <input type="text" id="quickCustName" class="shadcn-input w-full text-xs" placeholder="Full Name (e.g. John Doe)">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="tel" id="quickCustPhone" class="shadcn-input w-full text-xs" placeholder="Mobile Number">
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="shadcn-btn shadcn-btn-primary w-full text-xs h-9" onclick="saveQuickCustomer()">
                                            <i class="bi bi-save me-1"></i> Add
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="text-sm font-medium mb-2 block" for="bkVehicleModel">Vehicle Model <span class="text-destructive">*</span></label>
                            <input type="text" class="shadcn-input w-full" id="bkVehicleModel" name="vehicle_model" placeholder="e.g. Honda Activa 6G" required>
                        </div>
                        <div class="col-md-6">
                            <label class="text-sm font-medium mb-2 block" for="bkVehicleNumber">Vehicle Number <span class="text-destructive">*</span></label>
                            <input type="text" class="shadcn-input w-full" id="bkVehicleNumber" name="vehicle_number" placeholder="e.g. MH 02 AB 1234" required style="text-transform: uppercase;">
                        </div>
                        <div class="col-md-6">
                            <label class="text-sm font-medium mb-2 block" for="bkServiceType">Service <span class="text-destructive">*</span></label>
                            <select class="shadcn-input w-full" id="bkServiceType" name="service_id" required>
                                <option value="">Select service...</option>
                                <?php
                                $s_res = mysqli_query($conn, "SELECT service_id, service_name FROM services ORDER BY service_name ASC");
                                while($sr = mysqli_fetch_assoc($s_res)) {
                                    echo "<option value='{$sr['service_id']}'>{$sr['service_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="text-sm font-medium mb-2 block" for="bkDate">Preferred Date <span class="text-destructive">*</span></label>
                            <input type="date" class="shadcn-input w-full" id="bkDate" name="booking_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="text-sm font-medium mb-2 block" for="bkSlot">Time Slot <span class="text-destructive">*</span></label>
                            <select class="shadcn-input w-full" id="bkSlot" name="booking_time" required>
                                <option value="">Select slot...</option>
                                <option value="09:00">09:00 AM</option>
                                <option value="10:00">10:00 AM</option>
                                <option value="11:00">11:00 AM</option>
                                <option value="12:00">12:00 PM</option>
                                <option value="14:00">02:00 PM</option>
                                <option value="15:00">03:00 PM</option>
                                <option value="16:00">04:00 PM</option>
                            </select>
                        </div>
                        <div class="col-12 mt-4">
                            <label class="text-sm font-medium mb-2 block" for="bkNotes">Special Notes <span class="text-xs text-muted-foreground ml-2">(Optional)</span></label>
                            <textarea class="shadcn-input w-full" id="bkNotes" name="bkNotes" rows="3" placeholder="Any specific issues or requests..." style="min-height: 80px;"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="shadcn-btn shadcn-btn-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="shadcn-btn shadcn-btn-primary" id="finalCreateBookingBtn"><i class="bi bi-check-lg me-1"></i> Create Booking</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: ADD SERVICE -->
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-modal">
            <div class="modal-header">
                <h5 class="modal-title font-bold flex items-center gap-2" id="addServiceModalLabel"><i class="bi bi-gear text-primary"></i>Add New Service</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--theme-icon-filter);"></button>
            </div>
            <div class="modal-body">
                <form id="addServiceForm" method="POST" action="process_service.php">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="text-sm font-medium mb-2 block" for="svcName">Service Name <span class="text-destructive">*</span></label>
                            <input type="text" id="svcName" name="service_name" class="shadcn-input w-full" placeholder="e.g. Engine Tuning" required>
                        </div>
                        <div class="col-md-6">
                            <label class="text-sm font-medium mb-2 block" for="svcType">Service Type <span class="text-destructive">*</span></label>
                            <select id="svcType" name="service_type" class="shadcn-input w-full" required>
                                <option value="">Select type...</option>
                                <option value="Repair">Repair</option>
                                <option value="Wash">Wash</option>
                                <option value="Inspection">Inspection</option>
                                <option value="Accessory Fitting">Accessory Fitting</option>
                                <option value="Detailing">Detailing</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="text-sm font-medium mb-2 block" for="svcPrice">Price (₹) <span class="text-destructive">*</span></label>
                            <input type="number" id="svcPrice" name="price" class="shadcn-input w-full" placeholder="0.00" step="0.01" required>
                        </div>
                        <div class="col-12">
                            <label class="text-sm font-medium mb-2 block" for="svcDuration">Duration <span class="text-destructive">*</span></label>
                            <select id="svcDuration" name="duration_minutes" class="shadcn-input w-full" required>
                                <option value="">Select duration...</option>
                                <option value="30">30 Mins</option>
                                <option value="45">45 Mins</option>
                                <option value="60">60 Mins (1 Hour)</option>
                                <option value="90">90 Mins</option>
                                <option value="120">120 Mins (2 Hours)</option>
                                <option value="180">180 Mins (3 Hours)</option>
                            </select>
                        </div>
                        <div class="col-12 text-muted-foreground p-3 bg-muted/20 border border-border rounded-md mb-2">
                             <small><i class="bi bi-info-circle me-1"></i> Duration is used to calculate slot availability and prevent double bookings.</small>
                        </div>
                        <div class="col-12 mt-1">
                            <label class="text-sm font-medium mb-2 block" for="svcDesc">Description</label>
                            <textarea id="svcDesc" name="description" class="shadcn-input w-full" rows="3" placeholder="Detailed service description..." style="min-height: 80px;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer px-0 pb-0 border-0 mt-4">
                        <button type="button" class="shadcn-btn shadcn-btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="shadcn-btn shadcn-btn-primary" name="add_service"><i class="bi bi-check-lg me-1"></i> Save Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── CLEAN URL FROM MESSAGE PARAMS ──
    if (window.history.replaceState && window.location.search.includes('msg=')) {
        const url = new URL(window.location.href);
        url.searchParams.delete('msg');
        url.searchParams.delete('err');
        url.searchParams.delete('context');
        window.history.replaceState({}, '', url.toString());
    }

    // Booking Analytics Histogram Chart
    const ctx = document.getElementById('bookingHistogramChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Booked Slots',
                    data: [12, 19, 15, 8, 22, 30, 10],
                    backgroundColor: [
                        'rgba(99, 102, 241, 0.85)', // Indigo
                        'rgba(59, 130, 246, 0.85)', // Blue
                        'rgba(14, 165, 233, 0.85)', // Sky
                        'rgba(16, 185, 129, 0.85)', // Emerald
                        'rgba(245, 158, 11, 0.85)', // Amber
                        'rgba(244, 63, 94, 0.85)',  // Rose
                        'rgba(168, 85, 247, 0.85)'  // Purple
                    ],
                    borderRadius: 6,
                    barThickness: 28,
                    maxBarThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { 
                            color: '#a1a1aa',
                            font: { family: "'Inter', sans-serif", weight: '500' }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { 
                            color: 'rgba(128, 128, 128, 0.1)', // Universal grid color for both themes
                            drawTicks: false
                        },
                        border: { display: false },
                        ticks: { 
                            color: '#a1a1aa',
                            stepSize: 5,
                            font: { family: "'Inter', sans-serif" }
                        }
                    }
                }
            }
        });
    }

    // ── BOOKING ACTIONS (STATUS UPDATES) ──
    document.querySelectorAll('.update-status').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const status = this.dataset.status;
            
            if (confirm(`Change status to ${status}?`)) {
                fetch('update_booking_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `booking_id=${id}&status=${status}`
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        location.reload();
                    } else {
                        alert('Error updating status: ' + (res.message || 'Unknown error'));
                    }
                });
            }
        });
    });

    // ── NEW BOOKING FLOW ──
    window.toggleQuickAdd = function() {
        const sec = document.getElementById('quickAddCustomerSection');
        sec.style.display = (sec.style.display === 'none') ? 'block' : 'none';
        if (sec.style.display === 'block') document.getElementById('quickCustName').focus();
    };

    window.saveQuickCustomer = function() {
        const nameInput = document.getElementById('quickCustName');
        const phoneInput = document.getElementById('quickCustPhone');
        const name = nameInput.value.trim();
        const phone = phoneInput.value.trim();
        
        if (!name || !phone) { alert('Please enter both name and mobile number.'); return; }

        fetch('ajax/ajax_add_customer.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `name=${encodeURIComponent(name)}&phone=${encodeURIComponent(phone)}`
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                const select = document.getElementById('bkCustomer');
                const opt = document.createElement('option');
                opt.value = res.customer_id;
                opt.text = `${res.name} (CUST${String(res.customer_id).padStart(4, '0')})`;
                select.add(opt);
                select.value = res.customer_id;
                
                // Reset and hide
                nameInput.value = '';
                phoneInput.value = '';
                toggleQuickAdd();
            } else {
                alert('Add fail: ' + res.message);
            }
        })
        .catch(err => alert('Network error. Check connection.'));
    };

    const finalCreateBtn = document.getElementById('finalCreateBookingBtn');
    if (finalCreateBtn) {
        finalCreateBtn.addEventListener('click', function() {
            const form = document.getElementById('newBookingForm');
            if (!form.checkValidity()) { form.reportValidity(); return; }

            const formData = new FormData(form);
            finalCreateBtn.disabled = true;
            finalCreateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

            fetch('ajax/ajax_create_booking.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    // Redirect for fresh list & success msg
                    location.href = 'bookings.php?msg=success';
                } else {
                    alert('Booking fail: ' + res.message);
                    finalCreateBtn.disabled = false;
                    finalCreateBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Create Booking';
                }
            })
            .catch(err => {
                alert('Connection error.');
                finalCreateBtn.disabled = false;
                finalCreateBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Create Booking';
            });
        });
    }

    // Tab switching logic
    const tabs = document.querySelectorAll('.data-tab');
    const panes = document.querySelectorAll('.data-tab-pane');
    const filterGroup = document.querySelector('.flex.items-center.gap-2.flex-wrap'); // The filter dropdowns container
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            panes.forEach(p => p.classList.remove('active'));
            
            tab.classList.add('active');
            const targetId = tab.getAttribute('data-target');
            const target = document.getElementById(targetId);
            if(target) target.classList.add('active');

            const statusFilter = document.getElementById('bookingStatusFilter');

            // Hide filters logic
            if (targetId === 'serviceCatalog') {
                if(filterGroup) filterGroup.style.display = 'none';
            } else {
                if(filterGroup) filterGroup.style.display = 'flex';
                
                // Remove only "All Status" for Today and Upcoming
                if (targetId === 'todayBookings' || targetId === 'upcomingBookings') {
                    if(statusFilter) statusFilter.style.display = 'none';
                } else {
                    if(statusFilter) statusFilter.style.display = 'block';
                }
            }
        });
    });

    // Search functionality (Unified for Bookings and Catalog)
    var search = document.getElementById('bookingSearch');
    if (search) {
        search.addEventListener('input', function() {
            var q = search.value.toLowerCase();
            document.querySelectorAll('#bookingsTable tbody tr').forEach(function(r) {
                r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
            document.querySelectorAll('#serviceCatalog tbody tr').forEach(function(r) {
                r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

    // ── DELETE BOOKING RECORD ──
    document.querySelectorAll('.delete-booking').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            if (confirm('Are you sure you want to PERMANENTLY delete this booking record? This action cannot be undone.')) {
                this.disabled = true;
                const icon = this.querySelector('i');
                const oldClass = icon.className;
                icon.className = 'spinner-border spinner-border-sm';
                
                fetch('ajax/ajax_delete_booking.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}`
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        location.reload();
                    } else {
                        alert('Delete failed: ' + res.message);
                        this.disabled = false;
                        icon.className = oldClass;
                    }
                })
                .catch(err => {
                    alert('Network error.');
                    this.disabled = false;
                    icon.className = oldClass;
                });
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
