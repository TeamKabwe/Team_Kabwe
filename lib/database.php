<?php
// Database connection settings
// Database connection settings
$host = "localhost";
$username = "root";
$password = ""; // Leave blank unless you've set a password in phpMyAdmin
$dbname = "sadc"; // Make sure this is the database you created

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8mb4");
// Function to sanitize input data
function sanitize($conn, $data) {
    if (is_array($data)) {
        return array_map(function($item) use ($conn) {
            return sanitize($conn, $item);
        }, $data);
    }
    return mysqli_real_escape_string($conn, trim($data));
}

// Function to handle database errors
function handleDatabaseError($conn, $query) {
    $error_message = "Query failed: " . $conn->error . " in query: " . $query;
    error_log($error_message);
    die($error_message);
}

// Function to test database connection
function testDatabaseConnection() {
    global $conn;
    
    try {
        if ($conn->ping()) {
            return true;
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log("Database ping failed: " . $e->getMessage());
        return false;
    }
}

// Create database tables if they don't exist
function setupDatabase() {
    global $conn;
    
    // Users table
    $query = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255),
        farmer_id VARCHAR(50) NOT NULL UNIQUE,
        esignet_id VARCHAR(255),
        verified TINYINT(1) DEFAULT 0,
        verification_date DATETIME,
        auth_method VARCHAR(50),
        last_login DATETIME,
        created_at DATETIME NOT NULL,
        updated_at DATETIME
    )";
    
    if (!$conn->query($query)) {
        handleDatabaseError($conn, $query);
    }
    
    // Marketplace listings table
    $query = "CREATE TABLE IF NOT EXISTS marketplace_listings (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        category VARCHAR(100) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        currency VARCHAR(10) NOT NULL DEFAULT 'USD',
        quantity DECIMAL(10,2) NOT NULL,
        unit VARCHAR(50) NOT NULL,
        region VARCHAR(100) NOT NULL,
        location VARCHAR(255) NOT NULL,
        status ENUM('active', 'sold', 'deleted') NOT NULL DEFAULT 'active',
        created_at DATETIME NOT NULL,
        updated_at DATETIME,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if (!$conn->query($query)) {
        handleDatabaseError($conn, $query);
    }
    
    // Listing images table
    $query = "CREATE TABLE IF NOT EXISTS listing_images (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        listing_id INT(11) NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        sort_order INT(11) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (listing_id) REFERENCES marketplace_listings(id) ON DELETE CASCADE
    )";
    
    if (!$conn->query($query)) {
        handleDatabaseError($conn, $query);
    }
    
    // Transactions table
    $query = "CREATE TABLE IF NOT EXISTS transactions (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        transaction_id VARCHAR(50) NOT NULL UNIQUE,
        buyer_id INT(11) NOT NULL,
        seller_id INT(11) NOT NULL,
        product_id INT(11) NOT NULL,
        quantity DECIMAL(10,2) NOT NULL,
        price_per_unit DECIMAL(10,2) NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'paid', 'delivered', 'completed', 'disputed', 'cancelled') NOT NULL DEFAULT 'pending',
        created_at DATETIME NOT NULL,
        updated_at DATETIME,
        FOREIGN KEY (buyer_id) REFERENCES users(id),
        FOREIGN KEY (seller_id) REFERENCES users(id),
        FOREIGN KEY (product_id) REFERENCES marketplace_listings(id)
    )";
    
    if (!$conn->query($query)) {
        handleDatabaseError($conn, $query);
    }
    
    // Add more tables as needed
    
    return true;
}

// Initialize database if needed
setupDatabase();
?>

