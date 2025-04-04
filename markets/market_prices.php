<?php
session_start();
require_once('../lib/database.php');

// Check if user is logged in
if (!isset($_SESSION['is_authenticated']) || !$_SESSION['is_authenticated']) {
    header("Location: ../auth/login.php");
    exit();
}

// Function to get current market prices
function getMarketPrices($conn, $crop = null, $region = null) {
    $where_clauses = [];
    
    if ($crop && $crop !== 'All') {
        $crop = sanitize($conn, $crop);
        $where_clauses[] = "crop = '$crop'";
    }
    
    if ($region && $region !== 'All') {
        $region = sanitize($conn, $region);
        $where_clauses[] = "region = '$region'";
    }
    
    $where_sql = "";
    if (!empty($where_clauses)) {
        $where_sql = "WHERE " . implode(" AND ", $where_clauses);
    }
    
    $query = "SELECT * FROM market_prices $where_sql ORDER BY date DESC, crop ASC";
    
    $result = $conn->query($query);
    
    if ($result) {
        $prices = [];
        while ($row = $result->fetch_assoc()) {
            $prices[] = $row;
        }
        return $prices;
    } else {
        handleDatabaseError($conn, $query);
        return [];
    }
}

// Function to get available crops
function getAvailableCrops($conn) {
    $query = "SELECT DISTINCT crop FROM market_prices ORDER BY crop";
    
    $result = $conn->query($query);
    
    if ($result) {
        $crops = ['All'];
        while ($row = $result->fetch_assoc()) {
            $crops[] = $row['crop'];
        }
        return $crops;
    } else {
        handleDatabaseError($conn, $query);
        return ['All'];
    }
}

// Function to get available regions
function getAvailableRegions($conn) {
    $query = "SELECT DISTINCT region FROM market_prices ORDER BY region";
    
    $result = $conn->query($query);
    
    if ($result) {
        $regions = ['All'];
        while ($row = $result->fetch_assoc()) {
            $regions[] = $row['region'];
        }
        return $regions;
    } else {
        handleDatabaseError($conn, $query);
        return ['All'];
    }
}

// Function to get price trends
function getPriceTrends($conn, $crop, $days = 30) {
    $crop = sanitize($conn, $crop);
    $days = (int)$days;
    
    $query = "SELECT date, AVG(price) as avg_price 
              FROM market_prices 
              WHERE crop = '$crop' 
              AND date >= DATE_SUB(CURDATE(), INTERVAL $days DAY) 
              GROUP BY date 
              ORDER BY date";
    
    $result = $conn->query($query);
    
    if ($result) {
        $trends = [];
        while ($row = $result->fetch_assoc()) {
            $trends[] = $row;
        }
        return $trends;
    } else {
        handleDatabaseError($conn, $query);
        return [];
    }
}

// Function to get market insights
function getMarketInsights($conn, $limit = 3) {
    $limit = (int)$limit;
    
    $query = "SELECT * FROM market_insights ORDER BY created_at DESC LIMIT $limit";
    
    $result = $conn->query($query);
    
    if ($result) {
        $insights = [];
        while ($row = $result->fetch_assoc()) {
            $insights[] = $row;
        }
        return $insights;
    } else {
        handleDatabaseError($conn, $query);
        return [];
    }
}
?>

