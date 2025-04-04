<?php
session_start();
require_once('../lib/database.php');
require_once('../config/esignet_config.php');

// Check if already logged in
if (isset($_SESSION['is_authenticated']) && $_SESSION['is_authenticated']) {
    header("Location: ../dashboard/index.php");
    exit();
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize($conn, $_POST['email']);
    $password = $_POST['password'];
    $error = "";
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error = "Email and password are required";
    } else {
        // Check if user exists
        $query = "SELECT id, name, email, password, farmer_id, verified FROM users WHERE email = '$email'";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['farmer_id'] = $user['farmer_id'];
                $_SESSION['verified'] = $user['verified'];
                $_SESSION['is_authenticated'] = true;
                $_SESSION['auth_method'] = 'password';
                
                // Update last login
                $query = "UPDATE users SET last_login = NOW() WHERE id = {$user['id']}";
                $conn->query($query);
                
                // Redirect to dashboard
                header("Location: ../dashboard/index.php");
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "User not found";
        }
    }
}

// Check for registration success message
$registration_success = isset($_SESSION['registration_success']) && $_SESSION['registration_success'];
if ($registration_success) {
    unset($_SESSION['registration_success']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SADC Digital Farmer ID - Login</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            background-color: var(--background-color);
        }
        
        .login-form {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        .login-form h1 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .login-form p {
            text-align: center;
            color: var(--text-light);
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 16px;
        }
        
        .login-button {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .login-button:hover {
            background-color: var(--primary-dark);
        }
        
        .esignet-button {
            width: 100%;
            padding: 12px;
            background-color: var(--white);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
        }
        
        .esignet-button:hover {
            background-color: #f8f8f8;
        }
        
        .esignet-button img {
            margin-right: 10px;
            width: 20px;
            height: 20px;
        }
        
        .separator {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 20px 0;
        }
        
        .separator::before,
        .separator::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid var(--border-color);
        }
        
        .separator span {
            padding: 0 10px;
            color: var(--text-light);
            font-size: 14px;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h1>SADC Digital Farmer ID</h1>
            <p>Enter your credentials to access your account</p>
            
            <?php if (isset($error) && !empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($registration_success): ?>
                <div class="alert alert-success">Registration successful! You can now log in.</div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="login-button">Login</button>
                
                <div class="separator">
                    <span>or continue with</span>
                </div>
                
                <a href="esignet_login.php" class="esignet-button">
                    <img src="../assets/images/esignet-logo.svg" alt="eSignet logo"> Login with eSignet
                </a>
            </form>
            
            <p class="register-link">
                Don't have an account? <a href="register.php">Register</a>
            </p>
        </div>
    </div>
</body>
</html>

