<?php
/**
 * Security & Session Management
 * Centralized session configuration and authentication guards.
 */

date_default_timezone_set('Asia/Kolkata');

error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 0); // Turn off display to prevent header issues

// 1. Session Configuration (Strict Security)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', 2592000); // 30 days
    ini_set('session.gc_maxlifetime', 2592000);     // 30 days
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_domain', 'localhost');
    ini_set('session.cookie_secure', 0);
    session_start();
}

// 2. Constants & DB Requirements (Relative to root if needed)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/constants.php';

// 3. Session Timeout (30 days)
if (!defined('SESSION_TIMEOUT')) define('SESSION_TIMEOUT', 2592000);
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    header('Location: ' . SITE_URL . '/auth/login/index.php?error=Session expired. Please login again.');
    exit;
}
$_SESSION['last_activity'] = time();

/**
 * Authentication Guard
 * @param string $required_role 'admin' or 'customer'
 */
function auth_guard($required_role = null) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . SITE_URL . '/auth/login/index.php');
        exit;
    }

    if ($required_role && $_SESSION['role'] !== $required_role) {
        $redirect = ($_SESSION['role'] === 'admin') ? '/admin/dashboard.php' : '/customer/dashboard.php';
        header('Location: ' . SITE_URL . $redirect);
        exit;
    }
}

/**
 * Guest Guard (Redirect logged-in users away from login/register)
 */
function guest_guard() {
    if (isset($_SESSION['user_id'])) {
        $redirect = ($_SESSION['role'] === 'admin') ? '/admin/dashboard.php' : '/customer/dashboard.php';
        header('Location: ' . SITE_URL . $redirect);
        exit;
    }
}
?>
