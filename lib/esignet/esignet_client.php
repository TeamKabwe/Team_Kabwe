<?php
require_once(__DIR__ . '/../../config/esignet_config.php');
require_once(__DIR__ . '/../utils/logger.php');

/**
 * eSignet Client
 * This class handles all interactions with the eSignet OAuth2 server
 */
class EsignetClient {
    
    /**
     * Generate authorization URL for eSignet login
     * 
     * @param array $additionalParams Additional parameters to include in the authorization request
     * @return array Authorization URL and state
     */
    public static function getAuthorizationUrl($additionalParams = []) {
        // Generate a random state for CSRF protection
        $state = bin2hex(random_bytes(16));
        
        // Generate a random nonce for replay protection
        $nonce = bin2hex(random_bytes(16));
        
        // Build the authorization URL
        $params = [
            'client_id' => ESIGNET_CLIENT_ID,
            'redirect_uri' => ESIGNET_REDIRECT_URI,
            'response_type' => 'code',
            'scope' => ESIGNET_SCOPE,
            'state' => $state,
            'nonce' => $nonce
        ];
        
        // Add additional parameters if provided
        if (!empty($additionalParams)) {
            $params = array_merge($params, $additionalParams);
        }
        
        $authUrl = ESIGNET_AUTH_ENDPOINT . '?' . http_build_query($params);
        
        Logger::debug("Generated eSignet authorization URL: " . $authUrl);
        
        return [
            'url' => $authUrl,
            'state' => $state,
            'nonce' => $nonce
        ];
    }
    
    /**
     * Exchange authorization code for tokens
     * 
     * @param string $code Authorization code received from eSignet
     * @return array Token response or error
     */
    public static function exchangeCodeForTokens($code) {
        Logger::debug("Exchanging authorization code for tokens");
        
        $postData = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => ESIGNET_REDIRECT_URI,
            'client_id' => ESIGNET_CLIENT_ID,
            'client_secret' => ESIGNET_CLIENT_SECRET
        ];
        
        return self::makeTokenRequest($postData);
    }
    
    /**
     * Refresh access token using refresh token
     * 
     * @param string $refreshToken Refresh token
     * @return array New tokens or error
     */
    public static function refreshAccessToken($refreshToken) {
        Logger::debug("Refreshing access token");
        
        $postData = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => ESIGNET_CLIENT_ID,
            'client_secret' => ESIGNET_CLIENT_SECRET
        ];
        
        return self::makeTokenRequest($postData);
    }
    
    /**
     * Get user information using access token
     * 
     * @param string $accessToken Access token
     * @return array User information or error
     */
    public static function getUserInfo($accessToken) {
        Logger::debug("Fetching user information");
        
        $ch = curl_init(ESIGNET_USERINFO_ENDPOINT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            Logger::error("Error fetching user info: " . $error);
            return [
                'success' => false,
                'error' => 'Connection error: ' . $error
            ];
        }
        
        if ($httpCode != 200) {
            Logger::error("Error fetching user info. HTTP Code: " . $httpCode . ", Response: " . $response);
            return [
                'success' => false,
                'error' => 'HTTP Error: ' . $httpCode,
                'response' => $response
            ];
        }
        
        $userInfo = json_decode($response, true);
        if (!$userInfo) {
            Logger::error("Invalid JSON response from userinfo endpoint: " . $response);
            return [
                'success' => false,
                'error' => 'Invalid JSON response',
                'response' => $response
            ];
        }
        
        Logger::debug("User info fetched successfully");
        return [
            'success' => true,
            'data' => $userInfo
        ];
    }
    
    /**
     * Get logout URL
     * 
     * @param string $idToken ID token
     * @param string $postLogoutRedirectUri Redirect URI after logout
     * @return string Logout URL
     */
    public static function getLogoutUrl($idToken, $postLogoutRedirectUri = null) {
        if (!$postLogoutRedirectUri) {
            $postLogoutRedirectUri = 'http://' . $_SERVER['HTTP_HOST'] . '/auth/login.php';
        }
        
        $params = [
            'id_token_hint' => $idToken,
            'post_logout_redirect_uri' => $postLogoutRedirectUri
        ];
        
        $logoutUrl = ESIGNET_LOGOUT_ENDPOINT . '?' . http_build_query($params);
        
        Logger::debug("Generated eSignet logout URL: " . $logoutUrl);
        
        return $logoutUrl;
    }
    
    /**
     * Validate ID token
     * 
     * @param string $idToken ID token to validate
     * @param string $nonce Nonce used during authorization
     * @return array Validation result
     */
    public static function validateIdToken($idToken, $nonce) {
        Logger::debug("Validating ID token");
        
        // Split the token into header, payload, and signature
        $tokenParts = explode('.', $idToken);
        if (count($tokenParts) != 3) {
            Logger::error("Invalid token format");
            return [
                'success' => false,
                'error' => 'Invalid token format'
            ];
        }
        
        // Decode the payload
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1])), true);
        if (!$payload) {
            Logger::error("Invalid token payload");
            return [
                'success' => false,
                'error' => 'Invalid token payload'
            ];
        }
        
        // Validate token expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            Logger::error("Token has expired");
            return [
                'success' => false,
                'error' => 'Token has expired'
            ];
        }
        
        // Validate issuer
        if (!isset($payload['iss']) || strpos($payload['iss'], ESIGNET_DOMAIN_URL) !== 0) {
            Logger::error("Invalid token issuer: " . ($payload['iss'] ?? 'not set'));
            return [
                'success' => false,
                'error' => 'Invalid token issuer'
            ];
        }
        
        // Validate audience
        if (!isset($payload['aud']) || $payload['aud'] != ESIGNET_CLIENT_ID) {
            Logger::error("Invalid token audience: " . ($payload['aud'] ?? 'not set'));
            return [
                'success' => false,
                'error' => 'Invalid token audience'
            ];
        }
        
        // Validate nonce if provided
        if ($nonce && (!isset($payload['nonce']) || $payload['nonce'] != $nonce)) {
            Logger::error("Invalid token nonce: " . ($payload['nonce'] ?? 'not set'));
            return [
                'success' => false,
                'error' => 'Invalid token nonce'
            ];
        }
        
        Logger::debug("ID token validated successfully");
        return [
            'success' => true,
            'data' => $payload
        ];
    }
    
    /**
     * Make a token request to the token endpoint
     * 
     * @param array $postData Data to send in the request
     * @return array Response or error
     */
    private static function makeTokenRequest($postData) {
        $ch = curl_init(ESIGNET_TOKEN_ENDPOINT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            Logger::error("Error making token request: " . $error);
            return [
                'success' => false,
                'error' => 'Connection error: ' . $error
            ];
        }
        
        if ($httpCode != 200) {
            Logger::error("Error making token request. HTTP Code: " . $httpCode . ", Response: " . $response);
            return [
                'success' => false,
                'error' => 'HTTP Error: ' . $httpCode,
                'response' => $response
            ];
        }
        
        $tokenResponse = json_decode($response, true);
        if (!$tokenResponse) {
            Logger::error("Invalid JSON response from token endpoint: " . $response);
            return [
                'success' => false,
                'error' => 'Invalid JSON response',
                'response' => $response
            ];
        }
        
        Logger::debug("Token request successful");
        return [
            'success' => true,
            'data' => $tokenResponse
        ];
    }
}
?>

