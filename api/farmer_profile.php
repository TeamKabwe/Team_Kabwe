<?php
session_start();
require_once('../lib/database.php');

// Check if user is logged in
if (!isset($_SESSION['is_authenticated']) || !$_SESSION['is_authenticated']) {
    header("Location: ../auth/login.php");
    exit();
}

// Function to get farmer profile
function getFarmerProfile($conn, $user_id) {
    $user_id = (int)$user_id;
    
    $query = "SELECT u.*, p.* 
              FROM users u
              LEFT JOIN farmer_profiles p ON u.id = p.user_id
              WHERE u.id = $user_id";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return false;
    }
}

// Function to update farmer profile
function updateFarmerProfile($conn, $user_id, $data) {
    $user_id = (int)$user_id;
    
    // Check if profile exists
    $query = "SELECT id FROM farmer_profiles WHERE user_id = $user_id";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        // Update existing profile
        $profile = $result->fetch_assoc();
        $profile_id = $profile['id'];
        
        $fields = [];
        foreach ($data as $key => $value) {
            $value = sanitize($conn, $value);
            $fields[] = "$key = '$value'";
        }
        
        $fields_str = implode(", ", $fields);
        
        $query = "UPDATE farmer_profiles SET $fields_str, updated_at = NOW() WHERE id = $profile_id";
    } else {
        // Create new profile
        $fields = ['user_id' => $user_id];
        $values = [$user_id];
        
        foreach ($data as $key => $value) {
            $fields[] = $key;
            $values[] = "'" . sanitize($conn, $value) . "'";
        }
        
        $fields_str = implode(", ", $fields);
        $values_str = implode(", ", $values);
        
        $query = "INSERT INTO farmer_profiles ($fields_str, created_at) VALUES ($values_str, NOW())";
    }
    
    if ($conn->query($query)) {
        return true;
    } else {
        handleDatabaseError($conn, $query);
        return false;
    }
}

// Function to update contact information
function updateContactInfo($conn, $user_id, $phone, $email, $address) {
    $user_id = (int)$user_id;
    $phone = sanitize($conn, $phone);
    $email = sanitize($conn, $email);
    
    // Update email in users table
    $query = "UPDATE users SET email = '$email' WHERE id = $user_id";
    $conn->query($query);
    
    // Update phone and address in farmer_profiles
    $data = [
        'phone' => $phone,
        'address' => $address
    ];
    
    return updateFarmerProfile($conn, $user_id, $data);
}

// Function to verify farmer identity with MOSIP
function verifyFarmerIdentity($conn, $user_id, $biometric_data = null) {
    $user_id = (int)$user_id;
    
    // In a real implementation, this would call the MOSIP API
    // For demo purposes, we'll simulate a successful verification
    
    // Update user's verification status
    $query = "UPDATE users SET verified = 1, verification_date = NOW() WHERE id = $user_id";
    
    if ($conn->query($query)) {
        return [
            'verified' => true,
            'score' => 98.5,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    } else {
        handleDatabaseError($conn, $query);
        return false;
    }
}

// Function to get farmer activity
function getFarmerActivity($conn, $user_id, $limit = 10, $offset = 0) {
    $user_id = (int)$user_id;
    
    $query = "SELECT 'profile_update' as type, 'Profile Updated' as title, updated_at as date
              FROM farmer_profiles
              WHERE user_id = $user_id
              UNION
              SELECT 'login' as type, 'Login via ' || COALESCE(auth_method, 'password') as title, last_login as date
              FROM users
              WHERE id = $user_id
              UNION
              SELECT 'transaction' as type, 'Transaction ' || transaction_id as title, created_at as date
              FROM transactions
              WHERE buyer_id = $user_id OR seller_id = $user_id
              ORDER BY date DESC
              LIMIT $limit OFFSET $offset";
    
    $result = $conn->query($query);
    
    if ($result) {
        $activities = [];
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        return $activities;
    } else {
        handleDatabaseError($conn, $query);
        return [];
    }
}
?>

