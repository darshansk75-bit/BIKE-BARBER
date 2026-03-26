<?php
include 'config/db.php';
$res = mysqli_query($conn, "SELECT * FROM admins");
print_r(mysqli_fetch_all($res, MYSQLI_ASSOC));
?>
