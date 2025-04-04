<?php
session_start();
require_once('../lib/database.php');
require_once('../lib/mosip/mosip.php');

// Check if user is logged in
if (!isset($_SESSION['is_authenticated']) || !$_SESSION['is_authenticated']) {
    header("Location: ../auth/login.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];
$email = $_SESSION['email'];
$farmer_id = $_SESSION['farmer_id'];
$verified = $_SESSION['verified'];

// Fetch farmer identity data from MOSIP
$farmerData = null;
$mosipError = null;

try {
    $response = getFarmerIdentity($farmer_id);
    if ($response['success']) {
        $farmerData = $response['data'];
    } else {
        $mosipError = "Failed to fetch farmer data from MOSIP";
    }
} catch (Exception $e) {
    $mosipError = "Error: " . $e->getMessage();
}

// Mock recent activity data
$recentActivity = [
    [
        'type' => 'loan',
        'title' => 'Loan Application Submitted',
        'description' => 'For maize production',
        'date' => date('Y-m-d H:i:s', strtotime('-2 days')),
    ],
    [
        'type' => 'fertilizer',
        'title' => 'Fertilizer Voucher Claimed',
        'description' => '50kg NPK fertilizer',
        'date' => date('Y-m-d H:i:s', strtotime('-1 week')),
    ],
    [
        'type' => 'verification',
        'title' => 'Digital ID Verified',
        'description' => 'Via MOSIP',
        'date' => date('Y-m-d H:i:s', strtotime('-2 weeks')),
    ],
];

// Mock alerts data
$alerts = [
    [
        'type' => 'warning',
        'title' => 'Weather Alert: Heavy rainfall expected',
        'description' => 'Take necessary precautions for your crops',
    ],
    [
        'type' => 'success',
        'title' => 'Subsidy Program Available',
        'description' => 'New government subsidy for small-scale farmers',
    ],
];

// Mock recommendations data
$recommendations = [
    [
        'title' => 'Crop Rotation',
        'description' => 'Consider rotating to legumes next season to improve soil fertility',
    ],
    [
        'title  => 'Consider rotating to legumes next season to improve soil fertility',
    ],
    [
        'title' => 'Market Opportunity',
        'description' => 'Soybean prices are trending upward in your region',
    ],
    [
        'title' => 'Training Workshop',
        'description' => 'Upcoming workshop on sustainable farming practices in your area',
    ],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SADC Digital Farmer ID</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .dashboard-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        
        .sidebar {
            background-color: var(--white);
            border-right: 1px solid var(--border-color);
            padding: 20px 0;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 20px;
        }
        
        .sidebar-header h2 {
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .sidebar-header p {
            color: var(--text-light);
            font-size: 14px;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 10px 20px;
            color: var(--text-color);
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .sidebar-menu a:hover {
            background-color: #f5f5f5;
        }
        
        .sidebar-menu a.active {
            background-color: #f0f0f0;
            border-left: 3px solid var(--primary-color);
            font-weight: 500;
        }
        
        .main-content {
            padding: 20px;
            background-color: var(--background-color);
        }
        
        .dashboard-header {
            margin-bottom: 20px;
        }
        
        .dashboard-header h1 {
            font-size: 24px;
            color: var(--text-color);
            margin-bottom: 5px;
        }
        
        .dashboard-header p {
            color: var(--text-light);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
        }
        
        .stat-card h3 {
            font-size: 16px;
            color: var(--text-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .stat-card h3 .icon {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        .stat-card .stat-value {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-card .stat-meta {
            font-size: 14px;
            color: var(--text-light);
        }
        
        .tabs {
            margin-bottom: 20px;
        }
        
        .tab-buttons {
            display: flex;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 20px;
        }
        
        .tab-button {
            padding: 10px 20px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-weight: 500;
        }
        
        .tab-button.active {
            border-bottom-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .activity-list {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
        }
        
        .activity-item {
            padding-bottom: 15px;
            margin-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .activity-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .activity-item h4 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .activity-item p {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 5px;
        }
        
        .activity-item .activity-date {
            font-size: 12px;
            color: var(--text-light);
        }
        
        .alert-item {
            padding: 15px;
            margin-bottom: 15px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: flex-start;
        }
        
        .alert-item .alert-icon {
            margin-right: 15px;
            font-size: 20px;
        }
        
        .alert-item.warning {
            background-color: #fff8e1;
            color: #f57c00;
        }
        
        .alert-item.success {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        
        .recommendation-item {
            padding-bottom: 15px;
            margin-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .recommendation-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .recommendation-item h4 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .recommendation-item p {
            font-size: 14px;
            color: var(--text-light);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>SADC Farmer ID</h2>
                <p><?php echo htmlspecialchars($name); ?></p>
                <p><?php echo htmlspecialchars($farmer_id); ?></p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="index.php" class="active">Dashboard</a></li>
                <li><a href="loans.php">Apply for Loan</a></li>
                <li><a href="fertilizers.php">Fertilizer Access</a></li>
                <li><a href="markets.php">Market Prices</a></li>
                <li><a href="profile.php">Farmer Profile</a></li>
                <li><a href="settings.php">Settings</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </aside>
        
        <main class="main-content">
            <div class="dashboard-header">
                <h1>Welcome, <?php echo htmlspecialchars($name); ?></h1>
                <p>Your Farming Summary</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><span class="icon">🆔</span> Digital ID</h3>
                    <div class="stat-value">
                        <?php if ($verified): ?>
                            <span class="badge success">Verified</span>
                        <?php else: ?>
                            <span class="badge warning">Not Verified</span>
                        <?php endif; ?>
                    </div>
                    <div class="stat-meta">
                        <?php if ($farmerData && isset($farmerData['verificationDate'])): ?>
                            Verified on <?php echo htmlspecialchars($farmerData['verificationDate']); ?>
                        <?php else: ?>
                            Verification pending
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="stat-card">
                    <h3><span class="icon">💰</span> Loan Status</h3>
                    <div class="stat-value">
                        <span class="badge warning">Pending</span>
                    </div>
                    <div class="stat-meta">Application #12345</div>
                </div>
                
                <div class="stat-card">
                    <h3><span class="icon">📊</span> Market Alerts</h3>
                    <div class="stat-value">3</div>
                    <div class="stat-meta">+2 since last week</div>
                </div>
                
                <div class="stat-card">
                    <h3><span class="icon">🌱</span> Fertilizer Voucher</h3>
                    <div class="stat-value">Available</div>
                    <div class="stat-meta">Expires in 30 days</div>
                </div>
            </div>
            
            <div class="tabs">
                <div class="tab-buttons">
                    <button class="tab-button active" data-tab="activity">Recent Activity</button>
                    <button class="tab-button" data-tab="alerts">Alerts</button>
                    <button class="tab-button" data-tab="recommendations">Recommendations</button>
                </div>
                
                <div class="tab-content active" id="activity-tab">
                    <div class="activity-list">
                        <?php foreach ($recentActivity as $activity): ?>
                            <div class="activity-item">
                                <h4><?php echo htmlspecialchars($activity['title']); ?></h4>
                                <p><?php echo htmlspecialchars($activity['description']); ?></p>
                                <div class="activity-date">
                                    <?php echo date('F j, Y', strtotime($activity['date'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="tab-content" id="alerts-tab">
                    <div class="activity-list">
                        <?php foreach ($alerts as $alert): ?>
                            <div class="alert-item <?php echo $alert['type']; ?>">
                                <div class="alert-icon">
                                    <?php echo $alert['type'] === 'warning' ? '⚠️' : '✅'; ?>
                                </div>
                                <div>
                                    <h4><?php echo htmlspecialchars($alert['title']); ?></h4>
                                    <p><?php echo htmlspecialchars($alert['description']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="tab-content" id="recommendations-tab">
                    <div class="activity-list">
                        <?php foreach ($recommendations as $recommendation): ?>
                            <div class="recommendation-item">
                                <h4><?php echo htmlspecialchars($recommendation['title']); ?></h4>
                                <p><?php echo htmlspecialchars($recommendation['description']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Tab functionality
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons and contents
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Add active class to clicked button
                button.classList.add('active');
                
                // Show corresponding content
                const tabId = button.getAttribute('data-tab');
                document.getElementById(`${tabId}-tab`).classList.add('active');
            });
        });
    </script>
</body>
</html>

