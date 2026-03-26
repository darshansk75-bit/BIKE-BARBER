<?php
include 'config/db.php';
$sql = "UPDATE admin_wallet SET 
        bank_balance = '1234.56', 
        card_holder = 'Test Name', 
        card_number = '1111 2222 3333 4444', 
        card_expiry = '01/25'";
if (mysqli_query($conn, $sql)) echo "Update successful.\n";
else echo "Update failed: " . mysqli_error($conn) . "\n";
?>
