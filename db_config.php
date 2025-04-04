<?php
$servername = "localhost";
$username = "root";
$password = "root"; // Leave empty if using XAMPP default settings
$database = "sadc"; // Replace with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
echo "Connected successfully!";

$result = $conn->query("SELECT * FROM users");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "User: " . $row["username"] . "<br>";
    }
} else {
    echo "No users found.";
}
?>
