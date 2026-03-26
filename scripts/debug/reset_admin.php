<?php
require_once 'config/db.php';

$email = 'darshanyt75@gmail.com';
$new_password = 'Admin@123#'; // Temporary password
$hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

$stmt = mysqli_prepare($conn, "UPDATE admins SET password = ? WHERE email = ?");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $email);
    if (mysqli_stmt_execute($stmt)) {
        echo "Password reset successful for $email. New password: $new_password";
    } else {
        echo "Error updating password: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Error preparing statement: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
