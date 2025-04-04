<?php
session_start();
require_once('../lib/database.php');
require_once('marketplace.php');

// Check if user is logged in
if (!isset($_SESSION['is_authenticated']) || !$_SESSION['is_authenticated']) {
    header("Location: ../auth/login.php");
    exit();
}

// Get filter parameters
$category = isset($_GET['category']) ? $_GET['category'] : 'All Categories';
$region = isset($_GET['region']) ? $_GET['region'] : 'All Regions';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$currency = isset($_GET['currency']) ? $_GET['currency'] : 'USD';

// Get listings
$listings = getMarketplaceListings($conn, $category, $region, $search);

// Get categories and regions for filters
$categories = getCategories($conn);
$regions = getRegions($conn);

// Check database connection
$db_connected = testDatabaseConnection();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SADC Agricultural Marketplace</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <?php if (!$db_connected): ?>
            <div class="alert alert-danger">
                Database connection error. Please check your database settings.
            </div>
        <?php endif; ?>
        
        <header>
            <h1>SADC Agricultural Marketplace</h1>
            <p>Secure trading platform for verified farmers and buyers across Southern Africa.</p>
            
            <div class="features">
                <div class="feature">
                    <div class="feature-icon">
                        <img src="../assets/images/verified-icon.svg" alt="Verified" class="icon-shield">
                    </div>
                    <div class="feature-content">
                        <h3>KYC-Verified Users</h3>
                        <p>Only users with verified SADC Digital IDs can trade</p>
                    </div>
                </div>
                
                <div class="feature">
                    <div class="feature-icon">
                        <img src="../assets/images/escrow-icon.svg" alt="Escrow" class="icon-lock">
                    </div>
                    <div class="feature-content">
                        <h3>Escrow System</h3>
                        <p>Secure transactions before delivery</p>
                    </div>
                </div>
                
                <div class="feature">
                    <div class="feature-icon">
                        <img src="../assets/images/rating-icon.svg" alt="Ratings" class="icon-star">
                    </div>
                    <div class="feature-content">
                        <h3>User Ratings & Reviews</h3>
                        <p>Build trust through verified feedback</p>
                    </div>
                </div>
            </div>
        </header>
        
        <div class="search-section">
            <form action="index.php" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search products, farmers, crops..." value="<?php echo htmlspecialchars($search); ?>">
                
                <button type="submit" class="search-button">Search</button>
                
                <select name="category" class="filter-select">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $cat === $category ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="region" class="filter-select">
                    <?php foreach ($regions as $reg): ?>
                        <option value="<?php echo htmlspecialchars($reg); ?>" <?php echo $reg === $region ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($reg); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="currency" class="filter-select">
                    <option value="USD" <?php echo $currency === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                    <option value="ZAR" <?php echo $currency === 'ZAR' ? 'selected' : ''; ?>>ZAR (R)</option>
                    <option value="ZMW" <?php echo $currency === 'ZMW' ? 'selected' : ''; ?>>ZMW (K)</option>
                </select>
                
                <a href="create-listing.php" class="add-listing-button">
                    <span>+</span> Add Listing
                </a>
            </form>
        </div>
        
        <nav class="marketplace-nav">
            <ul>
                <li class="active"><a href="index.php">Agricultural Products</a></li>
                <li><a href="livestock.php">Livestock</a></li>
                <li><a href="verified-farmers.php">Verified Farmers</a></li>
                <li><a href="my-listings.php">My Listings</a></li>
            </ul>
        </nav>
        
        <main>
            <section class="agricultural-products">
                <h2>Agricultural Products</h2>
                <p>Trade crops and agricultural products by kg or tonnes with verified farmers across SADC.</p>
                
                <div class="regional-demand">
                    <h3>Regional Demand</h3>
                    <div class="demand-grid">
                        <div class="demand-item">
                            <h4>🌽 Maize</h4>
                            <p>Zambia, Zimbabwe, Malawi</p>
                        </div>
                        <div class="demand-item">
                            <h4>🌶️ Chili</h4>
                            <p>South Africa, Botswana, Namibia</p>
                        </div>
                        <div class="demand-item">
                            <h4>🌱 Soya Beans</h4>
                            <p>Zambia, Tanzania, Angola</p>
                        </div>
                        <div class="demand-item">
                            <h4>🥜 Beans</h4>
                            <p>DRC, Mozambique, Lesotho</p>
                        </div>
                        <div class="demand-item">
                            <h4>🧄 Garlic</h4>
                            <p>South Africa, Angola, Namibia</p>
                        </div>
                    </div>
                </div>
                
                <div class="listings-grid">
                    <?php if (empty($listings)): ?>
                        <!-- Add sample listings for demonstration -->
                        <div class="listing-card">
                            <div class="listing-image">
                                <img src="../assets/images/organic-maize.jpg" alt="Organic Maize">
                                <div class="verified-badge" title="Verified Farmer">✓</div>
                            </div>
                            <div class="listing-content">
                                <h3>Organic Maize</h3>
                                <p class="listing-price">
                                    USD $320.00 / tonne
                                </p>
                                <p class="listing-quantity">
                                    Available: 50.00 tonnes
                                </p>
                                <p class="listing-location">
                                    📍 Lusaka, Zambia
                                </p>
                                <p class="listing-seller">
                                    Seller: John Mulenga (SADC-123456)
                                </p>
                                <a href="view-listing.php?id=1" class="view-listing-button">View Details</a>
                            </div>
                        </div>
                        
                        <div class="listing-card">
                            <div class="listing-image">
                                <img src="../assets/images/chili-peppers.jpg" alt="Fresh Chili Peppers">
                                <div class="verified-badge" title="Verified Farmer">✓</div>
                            </div>
                            <div class="listing-content">
                                <h3>Fresh Chili Peppers</h3>
                                <p class="listing-price">
                                    USD $2.50 / kg
                                </p>
                                <p class="listing-quantity">
                                    Available: 500.00 kg
                                </p>
                                <p class="listing-location">
                                    📍 Cape Town, South Africa
                                </p>
                                <p class="listing-seller">
                                    Seller: Sarah Nkosi (SADC-789012)
                                </p>
                                <a href="view-listing.php?id=2" class="view-listing-button">View Details</a>
                            </div>
                        </div>
                        
                        <div class="listing-card">
                            <div class="listing-image">
                                <img src="../assets/images/soya-beans.jpg" alt="Soya Beans">
                                <div class="verified-badge" title="Verified Farmer">✓</div>
                            </div>
                            <div class="listing-content">
                                <h3>Soya Beans</h3>
                                <p class="listing-price">
                                    USD $450.00 / tonne
                                </p>
                                <p class="listing-quantity">
                                    Available: 25.00 tonnes
                                </p>
                                <p class="listing-location">
                                    📍 Dar es Salaam, Tanzania
                                </p>
                                <p class="listing-seller">
                                    Seller: Maria Moyo (SADC-456789)
                                </p>
                                <a href="view-listing.php?id=3" class="view-listing-button">View Details</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($listings as $listing): ?>
                            <div class="listing-card">
                                <?php 
                                $images = getListingImages($conn, $listing['id']);
                                $image_url = !empty($images) ? $images[0]['image_path'] : '../assets/images/default-product.jpg';
                                ?>
                                <div class="listing-image">
                                    <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>">
                                    <?php if ($listing['verified']): ?>
                                        <div class="verified-badge" title="Verified Farmer">✓</div>
                                    <?php endif; ?>
                                </div>
                                <div class="listing-content">
                                    <h3><?php echo htmlspecialchars($listing['title']); ?></h3>
                                    <p class="listing-price">
                                        <?php echo $listing['currency']; ?> <?php echo number_format($listing['price'], 2); ?> / <?php echo htmlspecialchars($listing['unit']); ?>
                                    </p>
                                    <p class="listing-quantity">
                                        Available: <?php echo number_format($listing['quantity'], 2); ?> <?php echo htmlspecialchars($listing['unit']); ?>
                                    </p>
                                    <p class="listing-location">
                                        📍 <?php echo htmlspecialchars($listing['location']); ?>, <?php echo htmlspecialchars($listing['region']); ?>
                                    </p>
                                    <p class="listing-seller">
                                        Seller: <?php echo htmlspecialchars($listing['seller_name']); ?> (<?php echo htmlspecialchars($listing['farmer_id']); ?>)
                                    </p>
                                    <a href="view-listing.php?id=<?php echo $listing['id']; ?>" class="view-listing-button">View Details</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> SADC Digital Farmer ID Platform. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>

