<?php
session_start();
require_once('../lib/esignet/esignet_client.php');
require_once('../lib/utils/logger.php');

// Check if user was logged in with eSignet
$logoutUrl = null;
if (isset($_SESSION['auth_method']) && $_SESSION['auth_method'] === 'esignet' && isset($_SESSION[ESIGNET_SESSION_PREFIX . 'id_token'])) {
    $idToken = $_SESSION[ESIGNET_SESSION_PREFIX . 'id_token'];
    $logoutUrl = EsignetClient::getLogoutUrl($idToken);
    Logger::info("eSignet logout initiated");
}

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to eSignet logout if applicable, otherwise to login page
if ($logoutUrl) {
    header("Location: " . $logoutUrl);
} else {
    header("Location: login.php");
}
exit();
?>

