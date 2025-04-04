<?php
/**
 * eSignet Configuration
 * This file contains all the configuration parameters for eSignet integration
 */

// eSignet Domain URLs
define('ESIGNET_DOMAIN_URL', 'http://localhost:8088');
define('SIGNUP_DOMAIN_URL', 'http://localhost:8089');
define('ESIGNET_MOCK_DOMAIN_URL', 'http://localhost:8082');

// OAuth2 Configuration
define('ESIGNET_CLIENT_ID', 'sadc-farmer-app');
define('ESIGNET_CLIENT_SECRET', 'your_client_secret'); // Replace with your actual client secret
define('ESIGNET_REDIRECT_URI', 'http://' . $_SERVER['HTTP_HOST'] . '/auth/esignet_callback.php');
define('ESIGNET_SCOPE', 'openid profile email address phone resident_service');

// eSignet Endpoints
define('ESIGNET_AUTH_ENDPOINT', ESIGNET_DOMAIN_URL . '/authorize');
define('ESIGNET_TOKEN_ENDPOINT', ESIGNET_DOMAIN_URL . '/oauth/token');
define('ESIGNET_USERINFO_ENDPOINT', ESIGNET_DOMAIN_URL . '/oauth/userinfo');
define('ESIGNET_LOGOUT_ENDPOINT', ESIGNET_DOMAIN_URL . '/logout');

// Biometric Device Configuration
define('BIOMETRIC_ENABLED', true);
define('BIOMETRIC_DEVICE_ID', '4');
define('BIOMETRIC_DEVICE_CODE', 'b692b595-3523-slap-99fc-bd76e35f290f');
define('BIOMETRIC_SERVICE_VERSION', '0.9.5');
define('BIOMETRIC_CERTIFICATION', 'L0');

// Security Configuration
define('ESIGNET_CSRF_TOKEN_NAME', 'esignet_csrf_token');
define('ESIGNET_STATE_TOKEN_NAME', 'esignet_state');

// Session Configuration
define('ESIGNET_SESSION_PREFIX', 'esignet_');
define('ESIGNET_TOKEN_EXPIRY_BUFFER', 300); // 5 minutes buffer before token expiry

// Logging Configuration
define('ESIGNET_LOG_ENABLED', true);
define('ESIGNET_LOG_LEVEL', 'DEBUG'); // DEBUG, INFO, WARNING, ERROR
?>

