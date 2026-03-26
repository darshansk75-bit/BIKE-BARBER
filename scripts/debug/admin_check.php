<?php
require_once 'config/db.php';

echo "<h2>Admin Credentials Check</h2>";

$result = mysqli_query($conn, "SELECT admin_id, username, email FROM admins");
if ($result && mysqli_num_rows($result) > 0) {
    echo "<table border='1'><tr><th>ID</th><th>Username</th><th>Email</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr><td>{$row['admin_id']}</td><td>{$row['username']}</td><td>{$row['email']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "No admins found in the 'admins' table.";
}

mysqli_close($conn);
?>
