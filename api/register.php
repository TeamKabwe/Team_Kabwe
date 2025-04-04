<?php
session_start();
require_once('../lib/database.php');

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitize($conn, $_POST['name']);
    $email = sanitize($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $error = "";
    
    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } else {
        // Check if email already exists
        $query = "SELECT id FROM users WHERE email = '$email'";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            $error = "Email already exists";
        } else {
            // Generate farmer ID
            $farmer_id = "SADC-" . rand(100000, 999999);
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $query = "INSERT INTO users (name, email, password, farmer_id, verified, created_at) 
                      VALUES ('$name', '$email', '$hashed_password', '$farmer_id', 0, NOW())";
            
            if ($conn->query($query)) {
                // Set session variables
                $_SESSION['registration_success'] = true;
                
                // Redirect to login page
                header("Location: login.php");
                exit();
            } else {
                $error = "Registration failed: " . $conn->error;
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
    <title>SADC Digital Farmer ID - Register</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h1>SADC Digital Farmer ID</h1>
            <p>Create a new account</p>
            
            <?php if (isset($error) && !empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
            
            <p class="login-link">
                Already have an account? <a href="login.php">Login</a>
            </p>
        </div>
    </div>
</body>
</html>

