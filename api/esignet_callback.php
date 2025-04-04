<?php
session_start();
require_once('../lib/database.php');
require_once('../lib/esignet/esignet_client.php');
require_once('../lib/utils/logger.php');

// Log callback received
Logger::info("eSignet callback received: " . json_encode($_GET));

// Check if code and state are present
if (!isset($_GET['code']) || !isset($_GET['state'])) {
    Logger::error("Invalid eSignet callback: Missing parameters");
    die("Invalid request: Missing parameters");
}

// Verify state to prevent CSRF
if (!isset($_SESSION[ESIGNET_STATE_TOKEN_NAME]) || $_SESSION[ESIGNET_STATE_TOKEN_NAME] !== $_GET['state']) {
    Logger::error("Invalid eSignet callback: State mismatch");
    die("Invalid state parameter");
}

// Get the nonce from session
$nonce = isset($_SESSION[ESIGNET_SESSION_PREFIX . 'nonce']) ? $_SESSION[ESIGNET_SESSION_PREFIX . 'nonce'] : null;

// Clear the state and nonce from session
unset($_SESSION[ESIGNET_STATE_TOKEN_NAME]);
unset($_SESSION[ESIGNET_SESSION_PREFIX . 'nonce']);

// Exchange authorization code for tokens
$code = $_GET['code'];
$tokenResponse = EsignetClient::exchangeCodeForTokens($code);

if (!$tokenResponse['success']) {
    Logger::error("Failed to exchange code for tokens: " . json_encode($tokenResponse['error']));
    die("Authentication failed: Unable to exchange code for tokens");
}

// Validate ID token
$idToken = $tokenResponse['data']['id_token'];
$validationResult = EsignetClient::validateIdToken($idToken, $nonce);

if (!$validationResult['success']) {
    Logger::error("ID token validation failed: " . json_encode($validationResult['error']));
    die("Authentication failed: ID token validation failed");
}

// Get user info using access token
$accessToken = $tokenResponse['data']['access_token'];
$userInfoResponse = EsignetClient::getUserInfo($accessToken);

if (!$userInfoResponse['success']) {
    Logger::error("Failed to get user info: " . json_encode($userInfoResponse['error']));
    die("Authentication failed: Unable to get user information");
}

$user_info = $userInfoResponse['data'];

// Check if user exists in our database
$email = sanitize($conn, isset($user_info['email']) ? $user_info['email'] : '');
$sub = sanitize($conn, $user_info['sub']);

if (empty($email) && isset($user_info['phone_number'])) {
    // If email is not available, try using phone number as identifier
    $query = "SELECT id, farmer_id, verified FROM users WHERE phone = '" . sanitize($conn, $user_info['phone_number']) . "'";
} else {
    $query = "SELECT id, farmer_id, verified FROM users WHERE email = '$email' OR esignet_id = '$sub'";
}

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    // User exists, update eSignet info
    $user = $result->fetch_assoc();
    $user_id = $user['id'];
    $farmer_id = $user['farmer_id'];
    $verified = $user['verified'];
    
    // Update user's eSignet info
    $query = "UPDATE users SET 
              esignet_id = '$sub', 
              auth_method = 'esignet', 
              last_login = NOW()";
    
    // Update email if available
    if (!empty($email)) {
        $query .= ", email = '$email'";
    }
    
    // Update phone if available
    if (isset($user_info['phone_number'])) {
        $phone = sanitize($conn, $user_info['phone_number']);
        $query .= ", phone = '$phone'";
    }
    
    // Update name if available
    if (isset($user_info['name'])) {
        $name = sanitize($conn, $user_info['name']);
        $query .= ", name = '$name'";
    }
    
    $query .= " WHERE id = $user_id";
    
    if (!$conn->query($query)) {
        Logger::error("Failed to update user eSignet info: " . $conn->error);
    }
} else {
    // User doesn't exist, create new account
    $name = sanitize($conn, isset($user_info['name']) ? $user_info['name'] : 'SADC User');
    $phone = sanitize($conn, isset($user_info['phone_number']) ? $user_info['phone_number'] : '');
    $farmer_id = isset($user_info['farmer_id']) ? $user_info['farmer_id'] : "SADC-" . rand(100000, 999999);
    $verified = isset($user_info['verified']) ? ($user_info['verified'] ? 1 : 0) : 0;
    
    $query = "INSERT INTO users (
              name, 
              email, 
              phone,
              esignet_id, 
              farmer_id, 
              verified, 
              auth_method, 
              created_at
              ) VALUES (
              '$name', 
              '$email', 
              '$phone',
              '$sub', 
              '$farmer_id', 
              $verified, 
              'esignet', 
              NOW()
              )";
    
    if ($conn->query($query)) {
        $user_id = $conn->insert_id;
        Logger::info("Created new user via eSignet: " . $user_id);
    } else {
        Logger::error("Failed to create user: " . $conn->error);
        die("Failed to create user: " . $conn->error);
    }
}

// Store tokens in session
$_SESSION[ESIGNET_SESSION_PREFIX . 'access_token'] = $accessToken;
$_SESSION[ESIGNET_SESSION_PREFIX . 'id_token'] = $idToken;
$_SESSION[ESIGNET_SESSION_PREFIX . 'refresh_token'] = $tokenResponse['data']['refresh_token'];
$_SESSION[ESIGNET_SESSION_PREFIX . 'token_expiry'] = time() + $tokenResponse['data']['expires_in'];

// Set session variables
$_SESSION['user_id'] = $user_id;
$_SESSION['name'] = isset($user_info['name']) ? $user_info['name'] : 'SADC User';
$_SESSION['email'] = $email;
$_SESSION['phone'] = isset($user_info['phone_number']) ? $user_info['phone_number'] : '';
$_SESSION['farmer_id'] = $farmer_id;
$_SESSION['verified'] = $verified;
$_SESSION['is_authenticated'] = true;
$_SESSION['auth_method'] = 'esignet';

// Log successful authentication
Logger::info("eSignet authentication successful for user: " . $user_id);

// Check if biometric verification is required
if (BIOMETRIC_ENABLED && (!$verified || isset($_GET['verify_biometric']))) {
    // Redirect to biometric verification
    header("Location: ../auth/biometric_verification.php");
    exit();
}

// Redirect to dashboard
header("Location: ../dashboard/index.php");
exit();
?>

