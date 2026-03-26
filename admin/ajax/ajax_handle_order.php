<?php
/**
 * AJAX — Handle Order Actions (Accept/Reject/Delete)
 * PATH: /admin/ajax_handle_order.php
 */
require_once __DIR__ . '/../../includes/session_auth.php';
auth_guard('admin');
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if (!$order_id || !$action) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required data.']);
        exit;
    }

    if ($action === 'accept') {
        $sql = "UPDATE orders SET order_status = 'Completed' WHERE order_id = $order_id";
    } elseif ($action === 'reject') {
        $sql = "UPDATE orders SET order_status = 'Cancelled' WHERE order_id = $order_id";
    } elseif ($action === 'delete') {
        $sql = "DELETE FROM orders WHERE order_id = $order_id";
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        exit;
    }

    if (mysqli_query($conn, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
