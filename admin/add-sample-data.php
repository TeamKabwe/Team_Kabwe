<?php
session_start();
require_once('../lib/database.php');

// Check if user is logged in and is admin
if (!isset($_SESSION['is_authenticated']) || !$_SESSION['is_authenticated']) {
    header("Location: ../auth/login.php");
    exit();
}

// Function to add sample users
function addSampleUsers($conn) {
    // Check if users already exist
    $query = "SELECT COUNT(*) as count FROM users";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        return "Users already exist in the database.";
    }
    
    // Sample users data
    $users = [
        [
            'name' => 'John Mulenga',
            'email' => 'john.mulenga@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'farmer_id' => 'SADC-123456',
            'verified' => 1,
            'verification_date' => '2023-10-15 00:00:00'
        ],
        [
            'name' => 'Sarah Nkosi',
            'email' => 'sarah.nkosi@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'farmer_id' => 'SADC-789012',
            'verified' => 1,
            'verification_date' => '2023-09-20 00:00:00'
        ],
        [
            'name' => 'Maria Moyo',
            'email' => 'maria.moyo@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'farmer_id' => 'SADC-456789',
            'verified' => 1,
            'verification_date' => '2023-11-05 00:00:00'
        ]
    ];
    
    // Insert users
    $success_count = 0;
    foreach ($users as $user) {
        $query = "INSERT INTO users (name, email, password, farmer_id, verified, verification_date, created_at) 
                  VALUES ('{$user['name']}', '{$user['email']}', '{$user['password']}', '{$user['farmer_id']}', 
                          {$user['verified']}, '{$user['verification_date']}', NOW())";
        
        if ($conn->query($query)) {
            $success_count++;
        } else {
            error_log("Failed to add user {$user['name']}: " . $conn->error);
        }
    }
    
    return "Added $success_count sample users to the database.";
}

// Function to add sample listings
function addSampleListings($conn) {
    // Check if listings already exist
    $query = "SELECT COUNT(*) as count FROM marketplace_listings";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        return "Listings already exist in the database.";
    }
    
    // Get user IDs
    $query = "SELECT id, name FROM users LIMIT 3";
    $result = $conn->query($query);
    
    if (!$result || $result->num_rows === 0) {
        return "No users found. Please add users first.";
    }
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    // Sample listings data
    $listings = [
        [
            'title' => 'Organic Maize',
            'description' => 'High-quality organic maize grown without pesticides. Perfect for milling or animal feed.',
            'category' => 'Grains',
            'price' => 320.00,
            'currency' => 'USD',
            'quantity' => 50.00,
            'unit' => 'tonne',
            'region' => 'Zambia',
            'location' => 'Lusaka'
        ],
        [
            'title' => 'Fresh Chili Peppers',
            'description' => 'Spicy fresh chili peppers. Great for cooking or making hot sauce.',
            'category' => 'Vegetables',
            'price' => 2.50,
            'currency' => 'USD',
            'quantity' => 500.00,
            'unit' => 'kg',
            'region' => 'South Africa',
            'location' => 'Cape Town'
        ],
        [
            'title' => 'Soya Beans',
            'description' => 'High-protein soya beans. Ideal for oil production or animal feed.',
            'category' => 'Legumes',
            'price' => 450.00,
            'currency' => 'USD',
            'quantity' => 25.00,
            'unit' => 'tonne',
            'region' => 'Tanzania',
            'location' => 'Dar es Salaam'
        ]
    ];
    
    // Insert listings
    $success_count = 0;
    foreach ($listings as $index => $listing) {
        $user_id = $users[$index % count($users)]['id'];
        
        $query = "INSERT INTO marketplace_listings (user_id, title, description, category, price, currency, quantity, unit, region, location, status, created_at) 
                  VALUES ($user_id, '{$listing['title']}', '{$listing['description']}', '{$listing['category']}', 
                          {$listing['price']}, '{$listing['currency']}', {$listing['quantity']}, '{$listing['unit']}', 
                          '{$listing['region']}', '{$listing['location']}', 'active', NOW())";
        
        if ($conn->query($query)) {
            $listing_id = $conn->insert_id;
            $success_count++;
            
            // Add sample image
            $image_path = "../assets/images/" . strtolower(str_replace(' ', '-', $listing['title'])) . ".jpg";
            $query = "INSERT INTO listing_images (listing_id, image_path, sort_order) 
                      VALUES ($listing_id, '$image_path', 0)";
            $conn->query($query);
        } else {
            error_log("Failed to add listing {$listing['title']}: " . $conn->error);
        }
    }
    
    return "Added $success_count sample listings to the database.";
}

// Add sample data
$user_message = addSampleUsers($conn);
$listing_message = addSampleListings($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Sample Data - SADC Digital Farmer ID</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>SADC Digital Farmer ID - Add Sample Data</h1>
            <p>Add sample data to the database for testing</p>
        </header>
        
        <main>
            <div class="alert alert-success">
                <h3>Sample Data Added</h3>
                <p><?php echo $user_message; ?></p>
                <p><?php echo $listing_message; ?></p>
            </div>
            
            <div class="button-group">
                <a href="db-status.php" class="btn btn-primary">Check Database Status</a>
                <a href="../marketplace/index.php" class="btn btn-outline">Go to Marketplace</a>
            </div>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> SADC Digital Farmer ID Platform. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>

