<?php
require_once(_DIR_ . '/../lib/database.php'); // Adjusted path

$sql = "CREATE TABLE IF NOT EXISTS users (
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

if ($conn->query($sql) === TRUE) {
    echo "Table users created successfully.";
} else {
    echo "Query failed: " . $conn->error;
}

$conn->close();
?>