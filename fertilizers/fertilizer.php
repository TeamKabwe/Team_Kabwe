<?php
session_start();
require_once('../lib/database.php');

// Check if user is logged in
if (!isset($_SESSION['is_authenticated']) || !$_SESSION['is_authenticated']) {
    header("Location: ../auth/login.php");
    exit();
}

// Function to get available fertilizers
function getAvailableFertilizers($conn) {
    $query = "SELECT * FROM fertilizers WHERE availability != 'Out of Stock' ORDER BY name";
    
    $result = $conn->query($query);
    
    if ($result) {
        $fertilizers = [];
        while ($row = $result->fetch_assoc()) {
            $fertilizers[] = $row;
        }
        return $fertilizers;
    } else {
        handleDatabaseError($conn, $query);
        return [];
    }
}

// Function to apply for fertilizer subsidy
function applyForSubsidy($conn, $user_id, $fertilizer_id, $quantity, $crop_type, $delivery_address) {
    $user_id = (int)$user_id;
    $fertilizer_id = (int)$fertilizer_id;
    $quantity = (int)$quantity;
    $crop_type = sanitize($conn, $crop_type);
    $delivery_address = sanitize($conn, $delivery_address);
    
    $application_id = generateSubsidyId();
    
    $query = "INSERT INTO fertilizer_applications (application_id, user_id, fertilizer_id, quantity, crop_type, delivery_address, status, created_at) 
              VALUES ('$application_id', $user_id, $fertilizer_id, $quantity, '$crop_type', '$delivery_address', 'pending', NOW())";
    
    if ($conn->query($query)) {
        return $application_id;
    } else {
        handleDatabaseError($conn, $query);
        return false;
    }
}

// Function to get user subsidy applications
function getUserSubsidyApplications($conn, $user_id, $limit = 10, $offset = 0) {
    $user_id = (int)$user_id;
    
    $query = "SELECT a.*, f.name as fertilizer_name, f.type as fertilizer_type 
              FROM fertilizer_applications a
              JOIN fertilizers f ON a.fertilizer_id = f.id
              WHERE a.user_id = $user_id 
              ORDER BY a.created_at DESC
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

// Function to get fertilizer deliveries
function getUserDeliveries($conn, $user_id, $limit = 10, $offset = 0) {
    $user_id = (int)$user_id;
    
    $query = "SELECT d.*, f.name as fertilizer_name, f.type as fertilizer_type 
              FROM fertilizer_deliveries d
              JOIN fertilizers f ON d.fertilizer_id = f.id
              WHERE d.user_id = $user_id 
              ORDER BY d.created_at DESC
              LIMIT $limit OFFSET $offset";
    
    $result = $conn->query($query);
    
    if ($result) {
        $deliveries = [];
        while ($row = $result->fetch_assoc()) {
            $deliveries[] = $row;
        }
        return $deliveries;
    } else {
        handleDatabaseError($conn, $query);
        return [];
    }
}

// Helper function to generate subsidy application ID
function generateSubsidyId() {
    return 'SUB-' . date('Ymd') . '-' . substr(uniqid(), -6);
}
?>

