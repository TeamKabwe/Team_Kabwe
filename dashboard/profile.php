<?php
session_start();
require_once('../lib/database.php');
require_once('../lib/utils/session_check.php');
require_once('../lib/utils/logger.php');
require_once('../lib/mosip/mosip.php');

// Check if user is logged in
if (!isset($_SESSION['is_authenticated']) || !$_SESSION['is_authenticated']) {
    header("Location: ../auth/login.php");
    exit();
}

// Check and refresh eSignet tokens if needed
checkAndRefreshSession();

// Get user data
$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];
$email = $_SESSION['email'];
$farmer_id = $_SESSION['farmer_id'];
$verified = $_SESSION['verified'];

// Fetch farmer identity data from MOSIP
$farmerData = null;
$mosipError = null;

try {
    $response = getFarmerIdentity($farmer_id);
    if ($response['success']) {
        $farmerData = $response['data'];
    } else {
        $mosipError = "Failed to fetch farmer data from MOSIP: " . $response['error'];
        Logger::error($mosipError);
    }
} catch (Exception $e) {
    $mosipError = "Error: " . $e->getMessage();
    Logger::error($mosipError);
}

// Handle profile update
$updateSuccess = false;
$updateError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $phone = sanitize($conn, $_POST['phone']);
    $address_street = sanitize($conn, $_POST['address_street']);
    $address_city = sanitize($conn, $_POST['address_city']);
    $address_region = sanitize($conn, $_POST['address_region']);
    $address_country = sanitize($conn, $_POST['address_country']);
    
    // Update user in database
    $query = "UPDATE users SET 
              phone = '$phone', 
              address_street = '$address_street',
              address_city = '$address_city',
              address_region = '$address_region',
              address_country = '$address_country',
              updated_at = NOW()
              WHERE id = $user_id";
    
    if ($conn->query($query)) {
        $updateSuccess = true;
        
        // Update session data
        $_SESSION['phone'] = $phone;
        
        // Update MOSIP data if verified
        if ($verified) {
            $updateData = [
                'contactInfo' => [
                    'phone' => $phone,
                    'address' => [
                        'street' => $address_street,
                        'city' => $address_city,
                        'region' => $address_region,
                        'country' => $address_country,
                    ]
                ]
            ];
            
            try {
                $response = updateFarmerInfo($farmer_id, $updateData);
                if (!$response['success']) {
                    $updateError = "Profile updated in local database but failed to update MOSIP: " . $response['error'];
                    Logger::error($updateError);
                }
            } catch (Exception $e) {
                $updateError = "Profile updated in local database but failed to update MOSIP: " . $e->getMessage();
                Logger::error($updateError);
            }
        }
    } else {
        $updateError = "Failed to update profile: " . $conn->error;
        Logger::error($updateError);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Profile - SADC Digital Farmer ID</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .dashboard-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        
        .sidebar {
            background-color: var(--white);
            border-right: 1px solid var(--border-color);
            padding: 20px 0;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 20px;
        }
        
        .sidebar-header h2 {
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .sidebar-header p {
            color: var(--text-light);
            font-size: 14px;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 10px 20px;
            color: var(--text-color);
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .sidebar-menu a:hover {
            background-color: #f5f5f5;
        }
        
        .sidebar-menu a.active {
            background-color: #f0f0f0;
            border-left: 3px solid var(--primary-color);
            font-weight: 500;
        }
        
        .main-content {
            padding: 20px;
            background-color: var(--background-color);
        }
        
        .profile-header {
            margin-bottom: 20px;
        }
        
        .profile-header h1 {
            font-size: 24px;
            color: var(--text-color);
            margin-bottom: 5px;
        }
        
        .profile-header p {
            color: var(--text-light);
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        
        .profile-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 20px;
        }
        
        .profile-card-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .profile-card-header h2 {
            font-size: 18px;
            color: var(--text-color);
            margin: 0;
        }
        
        .profile-card-content {
            padding: 20px;
        }
        
        .profile-field {
            margin-bottom: 15px;
        }
        
        .profile-field label {
            display: block;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .profile-field input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
        }
        
        .profile-field p {
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: var(--border-radius);
        }
        
        .profile-actions {
            padding: 20px;
            border-top: 1px solid var(--border-color);
            text-align: right;
        }
        
        .profile-button {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: var(--border-radius);
            padding: 10px 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .profile-button:hover {
            background-color: var(--primary-dark);
        }
        
        .verification-status {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 30px 20px;
            text-align: center;
        }
        
        .verification-icon {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
        }
        
        .verification-icon.verified {
            color: var(--success-color);
        }
        
        .verification-icon.not-verified {
            color: var(--warning-color);
        }
        
        .verification-status h3 {
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .verification-status p {
            color: var(--text-light);
            margin-bottom: 20px;
        }
        
        .verification-button {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: var(--border-radius);
            padding: 10px 20px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }
        
        .verification-button:hover {
            background-color: var(--primary-dark);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>SADC Farmer ID</h2>
                <p><?php echo htmlspecialchars($name); ?></p>
                <p><?php echo htmlspecialchars($farmer_id); ?></p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="loans.php">Apply for Loan</a></li>
                <li><a href="fertilizers.php">Fertilizer Access</a></li>
                <li><a href="markets.php">Market Prices</a></li>
                <li><a href="profile.php" class="active">Farmer Profile</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>
        
        <main class="main-content">
            <div class="profile-header">
                <h1>Farmer Profile</h1>
                <p>View and manage your profile information</p>
            </div>
            
            <?php if ($updateSuccess): ?>
                <div class="alert alert-success">Profile updated successfully!</div>
            <?php endif; ?>
            
            <?php if ($updateError): ?>
                <div class="alert alert-warning"><?php echo $updateError; ?></div>
            <?php endif; ?>
            
            <?php if ($mosipError): ?>
                <div class="alert alert-warning"><?php echo $mosipError; ?></div>
            <?php endif; ?>
            
            <div class="profile-grid">
                <div>
                    <div class="profile-card">
                        <div class="profile-card-header">
                            <h2>Personal Information</h2>
                        </div>
                        <div class="profile-card-content">
                            <div class="profile-field">
                                <label>Full Name</label>
                                <p><?php echo htmlspecialchars($name); ?></p>
                            </div>
                            
                            <div class="profile-field">
                                <label>Farmer ID</label>
                                <p><?php echo htmlspecialchars($farmer_id); ?></p>
                            </div>
                            
                            <?php if ($farmerData && isset($farmerData['personalInfo'])): ?>
                                <div class="profile-field">
                                    <label>Date of Birth</label>
                                    <p><?php echo htmlspecialchars($farmerData['personalInfo']['dateOfBirth']); ?></p>
                                </div>
                                
                                <div class="profile-field">
                                    <label>Gender</label>
                                    <p><?php echo htmlspecialchars($farmerData['personalInfo']['gender']); ?></p>
                                </div>
                                
                                <div class="profile-field">
                                    <label>Nationality</label>
                                    <p><?php echo htmlspecialchars($farmerData['personalInfo']['nationality']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="profile-card">
                        <div class="profile-card-header">
                            <h2>Contact Information</h2>
                        </div>
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="profile-card-content">
                                <div class="profile-field">
                                    <label for="email">Email</label>
                                    <p><?php echo htmlspecialchars($email); ?></p>
                                </div>
                                
                                <div class="profile-field">
                                    <label for="phone">Phone Number</label>
                                    <input type="text" id="phone" name="phone" value="<?php echo isset($_SESSION['phone']) ? htmlspecialchars($_SESSION['phone']) : (isset($farmerData['contactInfo']['phone']) ? htmlspecialchars($farmerData['contactInfo']['phone']) : ''); ?>">
                                </div>
                                
                                <div class="profile-field">
                                    <label for="address_street">Street Address</label>
                                    <input type="text" id="address_street" name="address_street" value="<?php echo isset($farmerData['contactInfo']['address']['street']) ? htmlspecialchars($farmerData['contactInfo']['address']['street']) : ''; ?>">
                                </div>
                                
                                <div class="profile-field">
                                    <label for="address_city">City/Town</label>
                                    <input type="text" id="address_city" name="address_city" value="<?php echo isset($farmerData['contactInfo']['address']['city']) ? htmlspecialchars($farmerData['contactInfo']['address']['city']) : ''; ?>">
                                </div>
                                
                                <div class="profile-field">
                                    <label for="address_region">Region/Province</label>
                                    <input type="text" id="address_region" name="address_region" value="<?php echo isset($farmerData['contactInfo']['address']['region']) ? htmlspecialchars($farmerData['contactInfo']['address']['region']) : ''; ?>">
                                </div>
                                
                                <div class="profile-field">
                                    <label for="address_country">Country</label>
                                    <input type="text" id="address_country" name="address_country" value="<?php echo isset($farmerData['contactInfo']['address']['country']) ? htmlspecialchars($farmerData['contactInfo']['address']['country']) : ''; ?>">
                                </div>
                            </div>
                            <div class="profile-actions">
                                <button type="submit" name="update_profile" class="profile-button">Update Profile</button>
                            </div>
                        </form>
                    </div>
                    
                    <?php if ($farmerData && isset($farmerData['farmInfo'])): ?>
                        <div class="profile-card">
                            <div class="profile-card-header">
                                <h2>Farm Information</h2>
                            </div>
                            <div class="profile-card-content">
                                <div class="profile-field">
                                    <label>Farm Size</label>
                                    <p><?php echo htmlspecialchars($farmerData['farmInfo']['farmSize']); ?></p>
                                </div>
                                
                                <div class="profile-field">
                                    <label>Crops</label>
                                    <p>
                                        <?php 
                                        if (isset($farmerData['farmInfo']['crops']) && is_array($farmerData['farmInfo']['crops'])) {
                                            echo htmlspecialchars(implode(', ', $farmerData['farmInfo']['crops']));
                                        }
                                        ?>
                                    </p>
                                </div>
                                
                                <div class="profile-field">
                                    <label>Livestock</label>
                                    <p>
                                        <?php 
                                        if (isset($farmerData['farmInfo']['livestock']) && is_array($farmerData['farmInfo']['livestock'])) {
                                            echo htmlspecialchars(implode(', ', $farmerData['farmInfo']['livestock']));
                                        }
                                        ?>
                                    </p>
                                </div>
                                
                                <div class="profile-field">
                                    <label>Registration Date</label>
                                    <p><?php echo htmlspecialchars($farmerData['farmInfo']['registrationDate']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div>
                    <div class="profile-card">
                        <div class="profile-card-header">
                            <h2>ID Status</h2>
                        </div>
                        <div class="verification-status">
                            <?php if ($verified): ?>
                                <svg class="verification-icon verified" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <h3>Verified</h3>
                                <p>Your digital identity has been verified through MOSIP</p>
                                <?php if (isset($farmerData['verificationDate'])): ?>
                                    <p>Verified on: <?php echo htmlspecialchars($farmerData['verificationDate']); ?></p>
                                <?php endif; ?>
                            <?php else: ?>
                                <svg class="verification-icon not-verified" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                                <h3>Not Verified</h3>
                                <p>Your digital identity needs verification</p>
                                <a href="../auth/biometric_verification.php" class="verification-button">Verify Now</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="profile-card">
                        <div class="profile-card-header">
                            <h2>Account Activity</h2>
                        </div>
                        <div class="profile-card-content">
                            <div class="profile-field">
                                <label>Last Login</label>
                                <p><?php echo date('F j, Y, g:i a'); ?></p>
                            </div>
                            
                            <div class="profile-field">
                                <label>Authentication Method</label>
                                <p><?php echo ucfirst($_SESSION['auth_method']); ?></p>
                            </div>
                            
                            <div class="profile-field">
                                <label>Account Created</label>
                                <p>
                                    <?php 
                                    // Get account creation date from database
                                    $query = "SELECT created_at FROM users WHERE id = " . $_SESSION['user_id'];
                                    $result = $conn->query($query);
                                    if ($result && $result->num_rows > 0) {
                                        $row = $result->fetch_assoc();
                                        echo date('F j, Y', strtotime($row['created_at']));
                                    } else {
                                        echo "Unknown";
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Add any client-side functionality here
    </script>
</body>
</html>

