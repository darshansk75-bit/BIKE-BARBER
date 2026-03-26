<?php
/**
 * Admin — Process New Booking
 * PATH: /admin/process_booking.php
 */
require_once __DIR__ . '/../includes/session_auth.php';
auth_guard('admin');
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_booking'])) {
    $cust_id = intval($_POST['customer_id'] ?? 0);
    $v_model = trim($_POST['vehicle_model'] ?? '');
    $v_num = strtoupper(trim($_POST['vehicle_number'] ?? ''));
    $svc_id = intval($_POST['service_id'] ?? 0);
    $date = $_POST['booking_date'] ?? '';
    $time = $_POST['booking_time'] ?? '';

    if (!$cust_id || !$v_model || !$v_num || !$svc_id || !$date || !$time) {
        header("Location: bookings.php?msg=error&err=" . urlencode("All required fields must be filled."));
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO service_bookings (customer_id, service_id, booking_date, booking_time, status, vehicle_model, vehicle_number) VALUES (?, ?, ?, ?, 'Requested', ?, ?)");
    $stmt->bind_param("iissss", $cust_id, $svc_id, $date, $time, $v_model, $v_num);

    if ($stmt->execute()) {
        header("Location: bookings.php?msg=success");
    } else {
        header("Location: bookings.php?msg=error&err=" . urlencode($stmt->error));
    }
    $stmt->close();
} else {
    header("Location: bookings.php");
}
?>
