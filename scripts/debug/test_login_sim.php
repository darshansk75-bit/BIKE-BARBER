<?php
require_once 'config/db.php';

$email = 'darshanyt75@gmail.com';
$password = 'Admin@123#';

echo "Testing login for $email with password $password\n";

$stmt = mysqli_prepare($conn, "SELECT * FROM admins WHERE email = ? LIMIT 1");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        echo "User found in database.\n";
        if (password_verify($password, $user['password'])) {
            echo "SUCCESS: Password matches!\n";
        } else {
            echo "FAILURE: Password does NOT match. Hash in DB starts with: " . substr($user['password'], 0, 10) . "...\n";
        }
    } else {
        echo "FAILURE: User not found in admins table.\n";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "FAILURE: Could not prepare statement.\n";
}
?>
