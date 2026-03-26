<?php
require_once 'c:/wamp64/www/PROJECT_UPDATED/config/db.php';
$sql = "ALTER TABLE services 
        ADD COLUMN duration_minutes INT NOT NULL DEFAULT 30,
        ADD COLUMN service_type VARCHAR(50) NOT NULL";
if (mysqli_query($conn, $sql)) {
    echo "Success";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
