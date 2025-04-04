<?php
session_start();
require_once('../lib/database.php');
require_once('../lib/biometric/biometric_client.php');
require_once('../lib/utils/logger.php');

// Check if user is authenticated via eSignet
if (!isset($_SESSION['is_authenticated']) || $_SESSION['auth_method'] !== 'esignet') {
    header("Location: login.php");
    exit();
}

$error = null;
$success = null;

// Handle biometric verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_biometric'])) {
    // Initialize biometric device
    $initResult = BiometricClient::initializeDevice();
    
    if (!$initResult['success']) {
        $error = "Failed to initialize biometric device: " . $initResult['error'];
    } else {
        // Capture biometric data
        $captureResult = BiometricClient::captureBiometric('finger');
        
        if (!$captureResult['success']) {
            $error = "Failed to capture biometric data: " . $captureResult['error'];
        } else {
            // Verify biometric data
            $verifyResult = BiometricClient::verifyBiometric(
                $captureResult['data'],
                $_SESSION['user_id']
            );
            
            if (!$verifyResult['success']) {
                $error = "Biometric verification failed: " . $verifyResult['error'];
            } else {
                // Update user's verification status
                $user_id = $_SESSION['user_id'];
                $query = "UPDATE users SET verified = 1, verification_date = NOW() WHERE id = $user_id";
                
                if ($conn->query($query)) {
                    $_SESSION['verified'] = 1;
                    $success = "Biometric verification successful!";
                    
                    // Redirect to dashboard after a short delay
                    header("Refresh: 2; URL=../dashboard/index.php");
                } else {
                    $error = "Failed to update verification status: " . $conn->error;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biometric Verification - SADC Digital Farmer ID</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .verification-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        .verification-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .verification-header h1 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .verification-content {
            margin-bottom: 30px;
        }
        
        .biometric-icon {
            display: block;
            width: 100px;
            height: 100px;
            margin: 0 auto 20px;
            color: var(--primary-color);
        }
        
        .verification-actions {
            text-align: center;
        }
        
        .verification-button {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: var(--border-radius);
            padding: 12px 24px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .verification-button:hover {
            background-color: var(--primary-dark);
        }
        
        .skip-link {
            display: block;
            margin-top: 20px;
            color: var(--text-light);
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="verification-header">
            <h1>Biometric Verification</h1>
            <p>Please complete the biometric verification to access your account</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="verification-content">
            <svg class="biometric-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 11c0 3.866-3.134 7-7 7-1.572 0-3.024-.518-4.192-1.392"></path>
                <path d="M5 11V5a7 7 0 0 1 7-7h0a7 7 0 0 1 7 7v6"></path>
                <path d="M12 15c0 3.866-3.134 7-7 7-3.866 0-7-3.134-7-7v-4"></path>
                <path d="M19 15v4c0 3.866-3.134 7-7 7h0"></path>
                <path d="M12 19c0 3.866-3.134 7-7 7h0"></path>
            </svg>
            
            <p>
                To verify your identity, we need to capture your fingerprint using the biometric device.
                This helps ensure the security of your SADC Digital Farmer ID account.
            </p>
            
            <p>
                Please follow these steps:
            </p>
            
            <ol>
                <li>Click the "Start Verification" button below</li>
                <li>Place your right thumb on the fingerprint scanner when prompted</li>
                <li>Hold your finger steady until the scan is complete</li>
                <li>Wait for the verification result</li>
            </ol>
        </div>
        
        <div class="verification-actions">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <button type="submit" name="verify_biometric" class="verification-button">
                    Start Verification
                </button>
            </form>
            
            <a href="../dashboard/index.php" class="skip-link">Skip for now</a>
        </div>
    </div>
</body>
</html>

