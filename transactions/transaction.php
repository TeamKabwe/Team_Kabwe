<?php
session_start();
require_once('../lib/database.php');

// Check if user is logged in
if (!isset($_SESSION['is_authenticated']) || !$_SESSION['is_authenticated']) {
    header("Location: ../auth/login.php");
    exit();
}

// Function to create a new transaction
function createTransaction($conn, $buyer_id, $seller_id, $product_id, $quantity, $price_per_unit, $total_amount, $status = 'pending') {
    $transaction_id = generateTransactionId();
    
    $query = "INSERT INTO transactions (transaction_id, buyer_id, seller_id, product_id, quantity, price_per_unit, total_amount, status, created_at) 
              VALUES ('$transaction_id', $buyer_id, $seller_id, $product_id, $quantity, $price_per_unit, $total_amount, '$status', NOW())";
    
    if ($conn->query($query)) {
        return $transaction_id;
    } else {
        handleDatabaseError($conn, $query);
        return false;
    }
}

// Function to update transaction status
function updateTransactionStatus($conn, $transaction_id, $status) {
    $transaction_id = sanitize($conn, $transaction_id);
    $status = sanitize($conn, $status);
    
    $query = "UPDATE transactions SET status = '$status', updated_at = NOW() WHERE transaction_id = '$transaction_id'";
    
    if ($conn->query($query)) {
        return true;
    } else {
        handleDatabaseError($conn, $query);
        return false;
    }
}

// Function to get transaction details
function getTransaction($conn, $transaction_id) {
    $transaction_id = sanitize($conn, $transaction_id);
    
    $query = "SELECT t.*, 
              b.name as buyer_name, b.farmer_id as buyer_farmer_id,
              s.name as seller_name, s.farmer_id as seller_farmer_id,
              p.name as product_name, p.unit
              FROM transactions t
              JOIN users b ON t.buyer_id = b.id
              JOIN users s ON t.seller_id = s.id
              JOIN products p ON t.product_id = p.id
              WHERE t.transaction_id = '$transaction_id'";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return false;
    }
}

// Function to get user transactions
function getUserTransactions($conn, $user_id, $role = 'all', $limit = 10, $offset = 0) {
    $user_id = (int)$user_id;
    
    $where_clause = "";
    if ($role === 'buyer') {
        $where_clause = "WHERE t.buyer_id = $user_id";
    } elseif ($role === 'seller') {
        $where_clause = "WHERE t.seller_id = $user_id";
    } else {
        $where_clause = "WHERE t.buyer_id = $user_id OR t.seller_id = $user_id";
    }
    
    $query = "SELECT t.*, 
              b.name as buyer_name, b.farmer_id as buyer_farmer_id,
              s.name as seller_name, s.farmer_id as seller_farmer_id,
              p.name as product_name, p.unit
              FROM transactions t
              JOIN users b ON t.buyer_id = b.id
              JOIN users s ON t.seller_id = s.id
              JOIN products p ON t.product_id = p.id
              $where_clause
              ORDER BY t.created_at DESC
              LIMIT $limit OFFSET $offset";
    
    $result = $conn->query($query);
    
    if ($result) {
        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
        return $transactions;
    } else {
        handleDatabaseError($conn, $query);
        return [];
    }
}

// Helper function to generate transaction ID
function generateTransactionId() {
    return 'TRX-' . date('Ymd') . '-' . substr(uniqid(), -6);
}
?>

