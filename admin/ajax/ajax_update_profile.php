<?php
/**
 * AJAX — Update Administrator Profile
 * PATH: /admin/ajax_update_profile.php
 */
require_once __DIR__ . '/../../includes/session_auth.php';
auth_guard('admin');
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_SESSION['user_id'] ?? null;
    if (!$admin_id || ($_SESSION['role'] ?? '') !== 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized or session expired.']);
        exit;
    }

    $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // 1. Ensure columns exist (Safety for new environments)
    $check_name = mysqli_query($conn, "SHOW COLUMNS FROM admins LIKE 'name'");
    if (mysqli_num_rows($check_name) == 0) mysqli_query($conn, "ALTER TABLE admins ADD COLUMN name VARCHAR(150) AFTER admin_id");
    
    $check_phone = mysqli_query($conn, "SHOW COLUMNS FROM admins LIKE 'phone'");
    if (mysqli_num_rows($check_phone) == 0) mysqli_query($conn, "ALTER TABLE admins ADD COLUMN phone VARCHAR(15) AFTER email");

    $check_img = mysqli_query($conn, "SHOW COLUMNS FROM admins LIKE 'profile_image'");
    if (mysqli_num_rows($check_img) == 0) mysqli_query($conn, "ALTER TABLE admins ADD COLUMN profile_image VARCHAR(255) AFTER phone");

    // Handle Image Removal
    if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
        $old_img_q = mysqli_query($conn, "SELECT profile_image FROM admins WHERE admin_id = $admin_id");
        $old_img = mysqli_fetch_assoc($old_img_q)['profile_image'] ?? '';
        if ($old_img && file_exists(__DIR__ . "/../../assets/uploads/profiles/$old_img")) {
            unlink(__DIR__ . "/../../assets/uploads/profiles/$old_img");
        }
        mysqli_query($conn, "UPDATE admins SET profile_image = NULL WHERE admin_id = $admin_id");
        $_SESSION['profile_image'] = '';
    }

    // 2. Handle Image Upload
    $image_update_sql = "";
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $file = $_FILES['profile_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];

        if (in_array($ext, $allowed) && $file['size'] <= 5 * 1024 * 1024) {
            $new_filename = 'admin_' . $admin_id . '_' . time() . '.' . $ext;
            $target_dir = __DIR__ . '/../../assets/uploads/profiles/';
            
            // Create dir if not exists
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

            // Delete old image if exists
            $old_q = mysqli_query($conn, "SELECT profile_image FROM admins WHERE admin_id = $admin_id");
            if ($old_row = mysqli_fetch_assoc($old_q)) {
                $old_path = $target_dir . $old_row['profile_image'];
                if (!empty($old_row['profile_image']) && file_exists($old_path)) unlink($old_path);
            }

            if (move_uploaded_file($file['tmp_name'], $target_dir . $new_filename)) {
                $image_update_sql = ", profile_image = '$new_filename'";
                $_SESSION['profile_image'] = $new_filename;
            }
        }
    }

    // 3. Update Database
    $sql = "UPDATE admins SET 
            name = '$name',
            phone = '$phone',
            email = '$email',
            username = '$username'
            $image_update_sql";

    if (!empty($password)) {
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        $sql .= ", password = '$hashed_pass'";
    }

    $sql .= " WHERE admin_id = $admin_id";

    if (mysqli_query($conn, $sql)) {
        // Update Session for immediate feedback
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['admin_name'] = $name;
        $_SESSION['admin_phone'] = $phone;

        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
