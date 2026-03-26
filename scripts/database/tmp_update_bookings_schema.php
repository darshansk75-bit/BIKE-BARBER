<?php
require_once 'c:/wamp64/www/PROJECT_UPDATED/config/db.php';
$sql = "ALTER TABLE service_bookings ADD COLUMN vehicle_model VARCHAR(150), ADD COLUMN vehicle_number VARCHAR(50)";
mysqli_query($conn, $sql);
?>
