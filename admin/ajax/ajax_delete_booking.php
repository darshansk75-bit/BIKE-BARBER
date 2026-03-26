<?php
/**
 * Admin — Delete Booking (AJAX)
 * PATH: /admin/ajax_delete_booking.php
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/session_auth.php';
auth_guard('admin');
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) { echo json_encode(['status' => 'error', 'message' => 'Invalid ID']); exit; }

    $stmt = $conn->prepare("DELETE FROM service_bookings WHERE booking_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Deletion failed: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
