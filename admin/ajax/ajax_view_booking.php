<?php
/**
 * Admin — View Booking Breakdown (AJAX)
 * PATH: /admin/ajax_view_booking.php
 */
require_once __DIR__ . '/../../includes/session_auth.php';
auth_guard('admin');
require_once __DIR__ . '/../../config/db.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) { echo "<div class='p-10 text-destructive text-center'>Invalid ID</div>"; exit; }

$sql = "SELECT sb.*, c.name as customer_name, c.phone as customer_phone, c.email as customer_email, s.service_name, s.price, s.duration_minutes, s.description as svc_desc
        FROM service_bookings sb
        JOIN customers c ON sb.customer_id = c.customer_id
        JOIN services s ON sb.service_id = s.service_id
        WHERE sb.booking_id = $id";
$res = mysqli_query($conn, $sql);
$b = mysqli_fetch_assoc($res);

if (!$b) { echo "<div class='p-10 text-destructive text-center'>Booking not found</div>"; exit; }
?>
<div class="p-6">
    <div class="row g-4">
        <!-- Customer Info -->
        <div class="col-md-6">
            <h6 class="text-xs font-bold uppercase text-muted-foreground mb-3 tracking-widest">Customer Details</h6>
            <div class="p-4 rounded-lg bg-muted/20 border border-border/50">
                <div class="font-bold text-foreground mb-1"><?= htmlspecialchars($b['customer_name']) ?></div>
                <div class="text-sm text-muted-foreground mb-2">CUST<?= str_pad($b['customer_id'], 4, '0', STR_PAD_LEFT) ?></div>
                <div class="flex items-center gap-2 text-sm mb-1 text-foreground">
                    <i class="bi bi-telephone text-primary"></i> <?= htmlspecialchars($b['customer_phone']) ?>
                </div>
                <div class="flex items-center gap-2 text-sm text-foreground">
                    <i class="bi bi-envelope text-primary"></i> <?= htmlspecialchars($b['customer_email']) ?>
                </div>
            </div>
        </div>
        <!-- Vehicle Info -->
        <div class="col-md-6">
            <h6 class="text-xs font-bold uppercase text-muted-foreground mb-3 tracking-widest">Vehicle Details</h6>
            <div class="p-4 rounded-lg bg-muted/20 border border-border/50">
                <div class="font-bold text-foreground mb-1"><?= htmlspecialchars($b['vehicle_model']) ?></div>
                <div class="text-xs font-mono bg-primary/10 text-primary px-2 py-0.5 rounded inline-block mb-3"><?= htmlspecialchars($b['vehicle_number']) ?></div>
                <div class="text-xs text-muted-foreground">Standard appointment for bike maintenance and specialized services.</div>
            </div>
        </div>
        <!-- Appointment Info -->
        <div class="col-12 mt-2">
            <h6 class="text-xs font-bold uppercase text-muted-foreground mb-3 tracking-widest">Appointment Details</h6>
            <div class="p-4 rounded-lg bg-primary/5 border border-primary/20 flex flex-wrap gap-x-12 gap-y-4">
                <div>
                    <div class="text-xs text-muted-foreground mb-1">Service</div>
                    <div class="font-bold text-primary"><?= htmlspecialchars($b['service_name']) ?></div>
                </div>
                <div>
                    <div class="text-xs text-muted-foreground mb-1">Date</div>
                    <div class="font-bold"><?= date('D, d M Y', strtotime($b['booking_date'])) ?></div>
                </div>
                <div>
                    <div class="text-xs text-muted-foreground mb-1">Time Slot</div>
                    <div class="font-bold"><?= date('h:i A', strtotime($b['booking_time'])) ?></div>
                </div>
                <div>
                    <div class="text-xs text-muted-foreground mb-1">Duration</div>
                    <div class="font-bold"><?= $b['duration_minutes'] ?> Mins</div>
                </div>
                <div>
                    <div class="text-xs text-muted-foreground mb-1">Status</div>
                    <span class="shadcn-badge shadcn-badge-outline" style="background: rgba(var(--primary-rgb), 0.1);"><?= $b['status'] ?></span>
                </div>
            </div>
        </div>
        <!-- Pricing -->
        <div class="col-12">
            <div class="flex justify-between items-center p-4 border-t border-border mt-2">
                <span class="text-sm font-medium text-muted-foreground">Estimated Cost</span>
                <span class="text-2xl font-bold text-foreground">₹<?= number_format($b['price'], 2) ?></span>
            </div>
        </div>
    </div>
</div>
