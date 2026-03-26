<?php
include 'config/db.php';
$cols = [
    "card_holder VARCHAR(100) DEFAULT 'Admin User'",
    "card_number VARCHAR(20) DEFAULT '•••• •••• •••• 4521'",
    "card_expiry VARCHAR(10) DEFAULT '12/28'",
    "bank_balance DECIMAL(12,2) DEFAULT 0.00"
];

foreach ($cols as $col) {
    mysqli_query($conn, "ALTER TABLE admin_wallet ADD COLUMN $col");
}
echo "Migration attempted.\n";
?>
