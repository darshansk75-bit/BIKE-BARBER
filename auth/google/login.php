<?php
/**
 * Google Login Initializer
 * PATH: /auth/google/login.php
 * Forces Google to ALWAYS prompt user to select account & authenticate.
 */
require_once __DIR__ . '/../../includes/session_auth.php';
require_once __DIR__ . '/../../vendor/autoload.php';

guest_guard();

$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);

$client->addScope("email");
$client->addScope("profile");

// SECURITY: Always force Google to show account picker & re-authenticate
$client->setApprovalPrompt('force');
$client->setAccessType('offline');

// CSRF Protection: Generate and store a state token
$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;
$client->setState($state);

header("Location: " . $client->createAuthUrl());
exit;
?>
