<?php
session_start();
require_once('../lib/database.php');
require_once('../lib/mosip/mosip.php');
require_once('../lib/esignet/esignet.php');

// Check if user is logged in
if (!isset($_SESSION['is_authenticated']) || !$_SESSION['is_authenticated']) {
    header("Location: ../auth/login.php");
    exit();
}

// Test MOSIP integration
$mosipStatus = false;
$mosipError = null;

try {
    $response = authenticateMosip();
    $mosipStatus = $response['success'];
    if (!$mosipStatus && isset($response['error'])) {
        $mosipError = $response['error'];
    }
} catch (Exception $e) {
    $mosipError = $e->getMessage();
}

// Test eSignet integration
$esignetStatus = false;
$esignetError = null;

try {
    $authData = getEsignetAuthUrl();
    $esignetStatus = !empty($authData['url']) && !empty($authData['state']);
    if (!$esignetStatus) {
        $esignetError = "Failed to generate eSignet authorization URL";
    }
} catch (Exception $e) {
    $esignetError = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integration Status - SADC Digital Farmer ID</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .status-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .status-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .status-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .status-indicator {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .status-success {
            background-color: var(--success-color);
        }
        
        .status-error {
            background-color: var(--error-color);
        }
        
        .status-details {
            margin-top: 15px;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: var(--border-radius);
        }
        
        .status-details h4 {
            margin-bottom: 10px;
        }
        
        .status-details pre {
            background-color: #f0f0f0;
            padding: 10px;
            border-radius: var(--border-radius);
            overflow-x: auto;
        }
        
        .test-button {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: var(--border-radius);
            padding: 10px 15px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
        }
        
        .test-button:hover {
            background-color: var(--primary-dark);
        }
    </style>
</head>
<body>
    <div class="status-container">
        <h1>Integration Status</h1>
        <p>Check the status of MOSIP and eSignet integrations</p>
        
        <div class="status-card">
            <div class="status-header">
                <div class="status-indicator <?php echo $mosipStatus ? 'status-success' : 'status-error'; ?>"></div>
                <h2>MOSIP Integration</h2>
            </div>
            
            <p>
                <?php if ($mosipStatus): ?>
                    MOSIP integration is working properly.
                <?php else: ?>
                    MOSIP integration is not working.
                    <?php if ($mosipError): ?>
                        <br>Error: <?php echo htmlspecialchars($mosipError); ?>
                    <?php endif; ?>
                <?php endif; ?>
            </p>
            
            <div class="status-details">
                <h4>MOSIP Configuration</h4>
                <ul>
                    <li><strong>API URL:</strong> <?php echo htmlspecialchars(MOSIP_API_URL); ?></li>
                    <li><strong>Client ID:</strong> <?php echo htmlspecialchars(MOSIP_CLIENT_ID); ?></li>
                    <li><strong>Partner ID:</strong> <?php echo htmlspecialchars(MOSIP_PARTNER_ID); ?></li>
                </ul>
                
                <h4>Test MOSIP Integration</h4>
                <form method="post" action="test-mosip.php">
                    <button type="submit" class="test-button">Test MOSIP Authentication</button>
                </form>
            </div>
        </div>
        
        <div class="status-card">
            <div class="status-header">
                <div class="status-indicator <?php echo $esignetStatus ? 'status-success' : 'status-error'; ?>"></div>
                <h2>eSignet Integration</h2>
            </div>
            
            <p>
                <?php if ($esignetStatus): ?>
                    eSignet integration is working properly.
                <?php else: ?>
                    eSignet integration is not working.
                    <?php if ($esignetError): ?>
                        <br>Error: <?php echo htmlspecialchars($esignetError); ?>
                    <?php endif; ?>
                <?php endif; ?>
            </p>
            
            <div class="status-details">
                <h4>eSignet Configuration</h4>
                <ul>
                    <li><strong>Auth URL:</strong> <?php echo htmlspecialchars(ESIGNET_AUTH_URL); ?></li>
                    <li><strong>Token URL:</strong> <?php echo htmlspecialchars(ESIGNET_TOKEN_URL); ?></li>
                    <li><strong>Client ID:</strong> <?php echo htmlspecialchars(ESIGNET_CLIENT_ID); ?></li>
                    <li><strong>Scope:</strong> <?php echo htmlspecialchars(ESIGNET_SCOPE); ?></li>
                </ul>
                
                <h4>Test eSignet Integration</h4>
                <a href="../auth/esignet_login.php?test=true" class="test-button">Test eSignet Login</a>
            </div>
        </div>
        
        <div class="button-group">
            <a href="../dashboard/index.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>

