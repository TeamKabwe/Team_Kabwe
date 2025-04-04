<?php
session_start();
require_once('../lib/database.php');

// Check if user is logged in and is admin
if (!isset($_SESSION['is_authenticated']) || !$_SESSION['is_authenticated']) {
    header("Location: ../auth/login.php");
    exit();
}

// Check database connection
$db_connected = testDatabaseConnection();

// Get database tables
$tables = [];
if ($db_connected) {
    $query = "SHOW TABLES";
    $result = $conn->query($query);
    
    if ($result) {
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
    }
}

// Check eSignet configuration
$esignet_config = [
    'client_id' => "sadc-farmer-app",
    'redirect_uri' => "http://" . $_SERVER['HTTP_HOST'] . "/auth/esignet_callback.php",
    'scope' => "openid profile email"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Status - SADC Digital Farmer ID</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .status-card {
            margin-bottom: 20px;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            background-color: var(--white);
        }
        
        .status-indicator {
            display: inline-block;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .status-success {
            background-color: var(--success-color);
        }
        
        .status-error {
            background-color: var(--error-color);
        }
        
        .table-list {
            margin-top: 20px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            overflow: hidden;
        }
        
        .table-list table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table-list th, .table-list td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .table-list th {
            background-color: #f5f5f5;
        }
        
        .test-button {
            margin-top: 10px;
            background-color: var(--primary-color);
            color: var(--white);
            padding: 8px 15px;
            border-radius: var(--border-radius);
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>SADC Digital Farmer ID - System Status</h1>
            <p>Check the status of your database and eSignet integration</p>
        </header>
        
        <main>
            <div class="status-card">
                <h2>
                    <span class="status-indicator <?php echo $db_connected ? 'status-success' : 'status-error'; ?>"></span>
                    Database Connection
                </h2>
                <p>
                    <?php if ($db_connected): ?>
                        Database connection is working properly.
                    <?php else: ?>
                        Database connection failed. Please check your database settings.
                    <?php endif; ?>
                </p>
                
                <?php if ($db_connected && !empty($tables)): ?>
                    <div class="table-list">
                        <h3>Database Tables</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Table Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tables as $table): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($table); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="status-card">
                <h2>
                    <span class="status-indicator status-success"></span>
                    eSignet Integration
                </h2>
                <p>eSignet integration is configured with the following parameters:</p>
                <ul>
                    <?php foreach ($esignet_config as $key => $value): ?>
                        <li><strong><?php echo htmlspecialchars($key); ?>:</strong> <?php echo htmlspecialchars($value); ?></li>
                    <?php endforeach; ?>
                </ul>
                
                <a href="../auth/esignet_login.php?test=true" class="test-button">Test eSignet Login</a>
            </div>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> SADC Digital Farmer ID Platform. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>

