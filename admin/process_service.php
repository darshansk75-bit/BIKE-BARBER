<?php
/**
 * Admin — Process Service Creation
 * PATH: /admin/process_service.php
 */
require_once __DIR__ . '/../includes/session_auth.php';
auth_guard('admin');
require_once __DIR__ . '/../config/db.php';

// Suppress raw errors, use custom reporting
error_reporting(0);
ini_set('display_errors', 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    // 1. Sanitize & Validate
    $name = trim($_POST['service_name'] ?? '');
    $type = trim($_POST['service_type'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $duration = intval($_POST['duration_minutes'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if (empty($name) || empty($type) || $price <= 0 || $duration <= 0) {
        header("Location: bookings.php?msg=error&err=" . urlencode("All mandatory fields must be filled correctly."));
        exit;
    }

    // 2. Prepared Statement (OO Style)
    $stmt = $conn->prepare("INSERT INTO services (service_name, service_type, price, duration_minutes, description) VALUES (?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        header("Location: bookings.php?msg=error&err=" . urlencode("Database prepare error."));
        exit;
    }

    $stmt->bind_param("ssdss", $name, $type, $price, $duration, $description);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: bookings.php?msg=success&context=service_added");
        exit;
    } else {
        $error = $stmt->error;
        $stmt->close();
        header("Location: bookings.php?msg=error&err=" . urlencode("Failed to save service: " . $error));
        exit;
    }
} else {
    header("Location: bookings.php");
    exit;
}
