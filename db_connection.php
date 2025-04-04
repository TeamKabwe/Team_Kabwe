<?php
// Database configuration
$db_host = 'localhost';
$db_name = 'sadc';
$db_user = 'root';
$db_pass = '';

// Create database connection
try {
   $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
   // Set the PDO error mode to exception
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   // Set default fetch mode to associative array
   $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
   
   // Return connection
   return $pdo;
} catch(PDOException $e) {
   // Log error (in a production environment, you would log this instead of displaying)
   error_log('Database Connection Error: ' . $e->getMessage());
   
   // If this file is included directly, output error
   if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
       header('Content-Type: application/json');
       echo json_encode([
           'status' => 'error',
           'message' => 'Database connection failed. Please try again later.'
       ]);
   }
   
   // Return false to indicate connection failure
   return false;
}
?>

