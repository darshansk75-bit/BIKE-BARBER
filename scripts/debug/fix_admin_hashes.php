<?php
/**
 * One-time script to hash plain-text passwords in the admins table.
 * PATH: /fix_admin_hashes.php
 */
require_once __DIR__ . '/config/db.php';

echo "<h2>Admin Password Hash Migration</h2>";

$result = mysqli_query($conn, "SELECT admin_id, password FROM admins");

if ($result) {
    $updated_count = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row['admin_id'];
        $pass = $row['password'];

        // Check if it's already a hash (BCRYPT hashes start with $2y$)
        if (substr($pass, 0, 4) !== '$2y$') {
            echo "Hashing password for admin ID: $id...<br>";
            $hashed = password_hash($pass, PASSWORD_BCRYPT);
            
            $update_stmt = mysqli_prepare($conn, "UPDATE admins SET password = ? WHERE admin_id = ?");
            if ($update_stmt) {
                mysqli_stmt_bind_param($update_stmt, "si", $hashed, $id);
                if (mysqli_stmt_execute($update_stmt)) {
                    $updated_count++;
                } else {
                    echo "Error updating admin ID $id: " . mysqli_error($conn) . "<br>";
                }
                mysqli_stmt_close($update_stmt);
            }
        } else {
            echo "Admin ID $id already has a hashed password.<br>";
        }
    }
    echo "<br><strong>Migration complete. $updated_count passwords hashed.</strong>";
} else {
    echo "Error fetching admins: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
