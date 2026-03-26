<?php
ob_start();
/**
 * AJAX — Update Wallet Details
 * PATH: /admin/ajax_update_wallet.php
 */
require_once __DIR__ . '/../../includes/session_auth.php';
auth_guard('admin');
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log incoming data
    error_log("Incoming Wallet POST: " . json_encode($_POST));

    $bank_balance = mysqli_real_escape_string($conn, $_POST['bank_balance'] ?? '0');
    $card_holder = mysqli_real_escape_string($conn, $_POST['card_holder'] ?? 'Admin User');
    $card_number = mysqli_real_escape_string($conn, $_POST['card_number'] ?? '•••• •••• •••• 4521');
    $card_expiry = mysqli_real_escape_string($conn, $_POST['card_expiry'] ?? '12/28');

    // Ensure a row exists first
    $check_q = mysqli_query($conn, "SELECT COUNT(*) FROM admin_wallet");
    $count = mysqli_fetch_row($check_q)[0];
    
    if ($count == 0) {
        $aid = $_SESSION['admin_id'] ?? 1925; // Fallback to known ID if session missing
        mysqli_query($conn, "INSERT INTO admin_wallet (admin_id, total_investment, total_sales, total_expense, total_profit, bank_balance, card_holder, card_number, card_expiry) VALUES ($aid, 0, 0, 0, 0, '$bank_balance', '$card_holder', '$card_number', '$card_expiry')");
    } else {
        // Update all existing rows (should only be 1)
        $sql = "UPDATE admin_wallet SET 
                bank_balance = '$bank_balance', 
                card_holder = '$card_holder', 
                card_number = '$card_number', 
                card_expiry = '$card_expiry'";
        mysqli_query($conn, $sql);
    }

    if (mysqli_errno($conn) == 0) {
        ob_clean();
        echo json_encode(['status' => 'success']);
    } else {
        error_log("Wallet Update Error: " . mysqli_error($conn));
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
} else {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
