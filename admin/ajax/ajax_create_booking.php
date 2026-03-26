<?php
/**
 * Admin — AJAX Process New Booking
 * PATH: /admin/ajax_create_booking.php
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/session_auth.php';
auth_guard('admin');
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cust_id = intval($_POST['customer_id'] ?? 0);
    $v_model = trim($_POST['vehicle_model'] ?? '');
    $v_num = strtoupper(trim($_POST['vehicle_number'] ?? ''));
    $svc_id = intval($_POST['service_id'] ?? 0);
    $date = $_POST['booking_date'] ?? '';
    $time = $_POST['booking_time'] ?? '';

    if (!$cust_id || !$v_model || !$v_num || !$svc_id || !$date || !$time) {
        echo json_encode(['status' => 'error', 'message' => 'All mandatory fields must be correctly filled.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO service_bookings (customer_id, service_id, booking_date, booking_time, status, vehicle_model, vehicle_number) VALUES (?, ?, ?, ?, 'Requested', ?, ?)");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Query preparation failed.']);
        exit;
    }
    
    $stmt->bind_param("iissss", $cust_id, $svc_id, $date, $time, $v_model, $v_num);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Booking created successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Submission failed: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
