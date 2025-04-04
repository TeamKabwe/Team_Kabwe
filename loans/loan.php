<?php
session_start();
require_once('../lib/database.php');

// Check if user is logged in
if (!isset($_SESSION['is_authenticated']) || !$_SESSION['is_authenticated']) {
    header("Location: ../auth/login.php");
    exit();
}

// Function to create a loan application
function createLoanApplication($conn, $user_id, $amount, $purpose, $farm_size, $farm_size_unit, $details = '') {
    $user_id = (int)$user_id;
    $amount = (float)$amount;
    $purpose = sanitize($conn, $purpose);
    $farm_size = (float)$farm_size;
    $farm_size_unit = sanitize($conn, $farm_size_unit);
    $details = sanitize($conn, $details);
    
    $loan_id = generateLoanId();
    
    $query = "INSERT INTO loan_applications (loan_id, user_id, amount, purpose, farm_size, farm_size_unit, details, status, created_at) 
              VALUES ('$loan_id', $user_id, $amount, '$purpose', $farm_size, '$farm_size_unit', '$details', 'pending', NOW())";
    
    if ($conn->query($query)) {
        return $loan_id;
    } else {
        handleDatabaseError($conn, $query);
        return false;
    }
}

// Function to get loan application details
function getLoanApplication($conn, $loan_id) {
    $loan_id = sanitize($conn, $loan_id);
    
    $query = "SELECT l.*, u.name, u.farmer_id 
              FROM loan_applications l
              JOIN users u ON l.user_id = u.id
              WHERE l.loan_id = '$loan_id'";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return false;
    }
}

// Function to get user loan applications
function getUserLoanApplications($conn, $user_id, $limit = 10, $offset = 0) {
    $user_id = (int)$user_id;
    
    $query = "SELECT * FROM loan_applications 
              WHERE user_id = $user_id 
              ORDER BY created_at DESC
              LIMIT $limit OFFSET $offset";
    
    $result = $conn->query($query);
    
    if ($result) {
        $applications = [];
        while ($row = $result->fetch_assoc()) {
            $applications[] = $row;
        }
        return $applications;
    } else {
        handleDatabaseError($conn, $query);
        return [];
    }
}

// Function to update loan application status
function updateLoanStatus($conn, $loan_id, $status, $notes = '') {
    $loan_id = sanitize($conn, $loan_id);
    $status = sanitize($conn, $status);
    $notes = sanitize($conn, $notes);
    
    $query = "UPDATE loan_applications 
              SET status = '$status', admin_notes = '$notes', updated_at = NOW() 
              WHERE loan_id = '$loan_id'";
    
    if ($conn->query($query)) {
        return true;
    } else {
        handleDatabaseError($conn, $query);
        return false;
    }
}

// Helper function to generate loan ID
function generateLoanId() {
    return 'LOAN-' . date('Ymd') . '-' . substr(uniqid(), -6);
}
?>

