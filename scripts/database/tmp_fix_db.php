<?php
require_once __DIR__ . '/config/db.php';

// Ensure admins table has columns
$cols = [
    'name' => "ALTER TABLE admins ADD COLUMN name VARCHAR(150) AFTER admin_id",
    'phone' => "ALTER TABLE admins ADD COLUMN phone VARCHAR(15) AFTER email",
    'profile_image' => "ALTER TABLE admins ADD COLUMN profile_image VARCHAR(255) AFTER phone"
];

foreach ($cols as $col => $sql) {
    $res = mysqli_query($conn, "SHOW COLUMNS FROM admins LIKE '$col'");
    if (mysqli_num_rows($res) == 0) {
        if (mysqli_query($conn, $sql)) {
            echo "Added $col.<br>";
        } else {
            echo "Error adding $col: " . mysqli_error($conn) . "<br>";
        }
    } else {
        echo "$col already exists.<br>";
    }
}
?>
