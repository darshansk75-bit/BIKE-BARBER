<?php
/**
 * Google OAuth Callback
 * PATH: /auth/google/callback.php
 * Validates CSRF state, authenticates with Google, then logs user in as customer.
 */
require_once __DIR__ . '/../../includes/session_auth.php';
require_once __DIR__ . '/../../vendor/autoload.php';

guest_guard();

// CSRF Protection: Verify the state parameter
if (!isset($_GET['state']) || !isset($_SESSION['google_oauth_state']) || $_GET['state'] !== $_SESSION['google_oauth_state']) {
    unset($_SESSION['google_oauth_state']);
    header('Location: ' . SITE_URL . '/auth/login/index.php?error=Invalid authentication request. Please try again.');
    exit;
}
unset($_SESSION['google_oauth_state']); // Consume the state token

if (isset($_GET['code'])) {
    $client = new Google_Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri(GOOGLE_REDIRECT_URI);

    $client->authenticate($_GET['code']);
    $token = $client->getAccessToken();
    
    if (!$token) {
        header('Location: ' . SITE_URL . '/auth/login/index.php?error=Google login failed. Please try again.');
        exit;
    }

    $google_service = new Google_Service_Oauth2($client);
    $data = $google_service->userinfo->get();

    $email = $data->email;
    $name = $data->name;

    if (empty($email)) {
        header('Location: ' . SITE_URL . '/auth/login/index.php?error=Could not retrieve email from Google.');
        exit;
    }

    // Check if customer exists
    $stmt = mysqli_prepare($conn, "SELECT * FROM customers WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
    } else {
        // Auto-create new customer with a secure random password
        $random_pass = bin2hex(random_bytes(16));
        $hashed = password_hash($random_pass, PASSWORD_BCRYPT);

        $ins = mysqli_prepare($conn, "INSERT INTO customers (name, email, password) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($ins, "sss", $name, $email, $hashed);
        mysqli_stmt_execute($ins);
        
        $user = [
            'customer_id' => mysqli_insert_id($conn),
            'name' => $name,
            'email' => $email
        ];
        mysqli_stmt_close($ins);
    }
    mysqli_stmt_close($stmt);

    // Security: Regenerate session ID to prevent fixation
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['customer_id'];
    $_SESSION['username'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = "customer";
    $_SESSION['last_activity'] = time();
    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];

    header("Location: " . SITE_URL . "/customer/dashboard.php");
    exit;
} else {
    header("Location: " . SITE_URL . "/auth/login/index.php?error=Google authentication was cancelled.");
    exit;
}
?>
