<?php
session_start();
require_once('../lib/database.php');
require_once('../lib/mosip/mosip.php');

// Check if user is logged in
if (!isset($_SESSION['is_authenticated']) || !$_SESSION['is_authenticated']) {
    header("Location: ../auth/login.php");
    exit();
}

// Test MOSIP authentication
$authResult = authenticateMosip();

// Test farmer identity retrieval
$identityResult = null;
if ($authResult['success'] && isset($_SESSION['farmer_id'])) {
    $identityResult = getFarmerIdentity($_SESSION['farmer_id']);
}

// Test verification
$verificationResult = null;
if ($authResult['success'] && isset($_SESSION['farmer_id'])) {
    $verificationResult = verifyFarmerIdentity($_SESSION['farmer_id']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test MOSIP Integration - SADC Digital Farmer ID</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .test-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .test-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .test-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .test-indicator {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .test-success {
            background-color: var(--success-color);
        }
        
        .test-error {
            background-color: var(--error-color);
        }
        
        .test-details {
            margin-top: 15px;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: var(--border-radius);
        }
        
        .test-details h4 {
            margin-bottom: 10px;
        }
        
        .test-details pre {
            background-color: #f0f0f0;
            padding: 10px;
            border-radius: var(--border-radius);
            overflow-x: auto;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>Test MOSIP Integration</h1>
        <p>Results of MOSIP integration tests</p>
        
        <div class="test-card">
            <div class="test-header">
                <div class="test-indicator <?php echo $authResult['success'] ? 'test-success' : 'test-error'; ?>"></div>
                <h2>MOSIP Authentication</h2>
            </div>
            
            <p>
                <?php if ($authResult['success']): ?>
                    Authentication with MOSIP was successful.
                <?php else: ?>
                    Authentication with MOSIP failed.
                <?php endif; ?>
            </p>
            
            <div class="test-details">
                <h4>Response</h4>
                <pre><?php echo json_encode($authResult, JSON_PRETTY_PRINT); ?></pre>
            </div>
        </div>
        
        <?php if ($identityResult): ?>
            <div class="test-card">
                <div class="test-header">
                    <div class="test-indicator <?php echo $identityResult['success'] ? 'test-success' : 'test-error'; ?>"></div>
                    <h2>Farmer Identity Retrieval</h2>
                </div>
                
                <p>
                    <?php if ($identityResult['success']): ?>
                        Farmer identity retrieval was successful.
                    <?php else: ?>
                        Farmer identity retrieval failed.
                    <?php endif; ?>
                </p>
                
                <div class="test-details">
                    <h4>Response</h4>
                    <pre><?php echo json_encode($identityResult, JSON_PRETTY_PRINT); ?></pre>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($verificationResult): ?>
            <div class="test-card">
                <div class="test-header">
                    <div class="test-indicator <?php echo $verificationResult['success'] ? 'test-success' : 'test-error'; ?>"></div>
                    <h2>Farmer Identity Verification</h2>
                </div>
                
                <p>
                    <?php if ($verificationResult['success']): ?>
                        Farmer identity verification was successful.
                    <?php else: ?>
                        Farmer identity verification failed.
                    <?php endif; ?>
                </p>
                
                <div class="test-details">
                    <h4>Response</h4>
                    <pre><?php echo json_encode($verificationResult, JSON_PRETTY_PRINT); ?></pre>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="button-group">
            <a href="integration-status.php" class="btn btn-primary">Back to Integration Status</a>
            <a href="../dashboard/index.php" class="btn btn-outline">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>

