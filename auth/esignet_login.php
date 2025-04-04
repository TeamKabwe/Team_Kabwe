<?php
session_start();
require_once('../lib/database.php');
require_once('../lib/esignet/esignet_client.php');
require_once('../lib/utils/logger.php');

// Check if already logged in
if (isset($_SESSION['is_authenticated']) && $_SESSION['is_authenticated']) {
    header("Location: ../dashboard/index.php");
    exit();
}

// Log eSignet login attempt
Logger::info("eSignet login initiated");

// Get eSignet authorization URL
$authData = EsignetClient::getAuthorizationUrl();

// Store state and nonce in session for verification in callback
$_SESSION[ESIGNET_STATE_TOKEN_NAME] = $authData['state'];
$_SESSION[ESIGNET_SESSION_PREFIX . 'nonce'] = $authData['nonce'];

// For testing purposes, we'll provide a simulation option
if (isset($_GET['test']) && $_GET['test'] === 'true') {
    // Simulate successful authorization and redirect to callback
    $code = bin2hex(random_bytes(16));
    $callback_url = ESIGNET_REDIRECT_URI . "?code=" . $code . "&state=" . $authData['state'];
    header("Location: " . $callback_url);
    exit();
}

// Redirect to eSignet
header("Location: " . $authData['url']);
exit();
?>

