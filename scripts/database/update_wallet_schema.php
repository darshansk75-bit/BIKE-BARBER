<?php
include 'config/db.php';
$sql = "ALTER TABLE admin_wallet 
        ADD COLUMN IF NOT EXISTS card_holder VARCHAR(100) DEFAULT 'Admin User',
        ADD COLUMN IF NOT EXISTS card_number VARCHAR(20) DEFAULT '•••• •••• •••• 4521',
        ADD COLUMN IF NOT EXISTS card_expiry VARCHAR(10) DEFAULT '12/28',
        ADD COLUMN IF NOT EXISTS bank_balance DECIMAL(12,2) DEFAULT 0.00";
if(mysqli_query($conn, $sql)) {
    echo "admin_wallet table updated successfully.\n";
} else {
    echo "Error updating table: " . mysqli_error($conn) . "\n";
}
?>
