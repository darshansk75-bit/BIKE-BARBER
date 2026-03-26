<?php
/**
 * Admin — Quick Add Customer (AJAX)
 * PATH: /admin/ajax_add_customer.php
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/session_auth.php';
auth_guard('admin');
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($phone)) {
        echo json_encode(['status' => 'error', 'message' => 'Name and Phone are required for quick add.']);
        exit;
    }

    // Check if phone already exists to avoid duplicate errors
    $check = $conn->prepare("SELECT customer_id FROM customers WHERE phone = ?");
    $check->bind_param("s", $phone);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'A customer with this phone number already exists.']);
        exit;
    }
    $check->close();

    // Insert with defaults
    $default_email = "cust_" . time() . "@bikebarber.com";
    $default_pass = password_hash("bike123", PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO customers (name, phone, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $phone, $default_email, $default_pass);

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'customer_id' => $stmt->insert_id,
            'name' => $name
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Save failed: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
