<?php
/**
 * Admin — Update Booking Status (AJAX)
 * PATH: /admin/update_booking_status.php
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/session_auth.php';
auth_guard('admin');
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['booking_id'] ?? 0);
    $status = $_POST['status'] ?? '';

    $allowed_statuses = ['Requested', 'Confirmed', 'In Progress', 'Completed', 'Cancelled'];

    if (!$id || !in_array($status, $allowed_statuses)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid booking ID or status.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE service_bookings SET status = ? WHERE booking_id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
