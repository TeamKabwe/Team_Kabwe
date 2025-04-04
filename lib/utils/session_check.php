<?php
require_once(__DIR__ . '/../../config/esignet_config.php');
require_once(__DIR__ . '/logger.php');
require_once(__DIR__ . '/../esignet/esignet_client.php');

/**
 * Check if the user's session is valid and refresh tokens if needed
 * 
 * @return bool True if session is valid, false otherwise
 */
function checkAndRefreshSession() {
    // Check if user is authenticated
    if (!isset($_SESSION['is_authenticated']) || !$_SESSION['is_authenticated']) {
        return false;
    }
    
    // If not authenticated via eSignet, no token refresh needed
    if (!isset($_SESSION['auth_method']) || $_SESSION['auth_method'] !== 'esignet') {
        return true;
    }
    
    // Check if tokens exist
    if (!isset($_SESSION[ESIGNET_SESSION_PREFIX . 'access_token']) || 
        !isset($_SESSION[ESIGNET_SESSION_PREFIX . 'refresh_token']) || 
        !isset($_SESSION[ESIGNET_SESSION_PREFIX . 'token_expiry'])) {
        Logger::warning("eSignet tokens missing from session");
        return false;
    }
    
    // Check if token is about to expire
    $tokenExpiry = $_SESSION[ESIGNET_SESSION_PREFIX . 'token_expiry'];
    $currentTime = time();
    
    // If token is still valid, no refresh needed
    if ($tokenExpiry > $currentTime + ESIGNET_TOKEN_EXPIRY_BUFFER) {
        return true;
    }
    
    // Token is about to expire, refresh it
    Logger::info("Refreshing eSignet tokens");
    $refreshToken = $_SESSION[ESIGNET_SESSION_PREFIX . 'refresh_token'];
    $refreshResponse = EsignetClient::refreshAccessToken($refreshToken);
    
    if (!$refreshResponse['success']) {
        Logger::error("Failed to refresh tokens: " . json_encode($refreshResponse['error']));
        return false;
    }
    
    // Update tokens in session
    $_SESSION[ESIGNET_SESSION_PREFIX . 'access_token'] = $refreshResponse['data']['access_token'];
    $_SESSION[ESIGNET_SESSION_PREFIX . 'token_expiry'] = time() + $refreshResponse['data']['expires_in'];
    
    // Update refresh token if provided
    if (isset($refreshResponse['data']['refresh_token'])) {
        $_SESSION[ESIGNET_SESSION_PREFIX . 'refresh_token'] = $refreshResponse['data']['refresh_token'];
    }
    
    Logger::info("eSignet tokens refreshed successfully");
    return true;
}

/**
 * Get the current user's access token
 * 
 * @return string|null Access token or null if not available
 */
function getAccessToken() {
    if (!checkAndRefreshSession()) {
        return null;
    }
    
    return isset($_SESSION[ESIGNET_SESSION_PREFIX . 'access_token']) ? 
        $_SESSION[ESIGNET_SESSION_PREFIX . 'access_token'] : null;
}

/**
 * Fetch user profile from eSignet
 * 
 * @return array|null User profile or null if not available
 */
function fetchUserProfile() {
    $accessToken = getAccessToken();
    if (!$accessToken) {
        return null;
    }
    
    $userInfoResponse = EsignetClient::getUserInfo($accessToken);
    if (!$userInfoResponse['success']) {
        Logger::error("Failed to fetch user profile: " . json_encode($userInfoResponse['error']));
        return null;
    }
    
    return $userInfoResponse['data'];
}
?>

