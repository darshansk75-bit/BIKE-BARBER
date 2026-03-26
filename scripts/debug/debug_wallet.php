<?php
include 'config/db.php';
$res = mysqli_query($conn, "SELECT * FROM admin_wallet");
print_r(mysqli_fetch_all($res, MYSQLI_ASSOC));
?>
