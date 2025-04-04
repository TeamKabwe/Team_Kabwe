<?php
session_start();
require_once('../lib/database.php');
require_once('marketplace.php');
require_once('../transactions/transaction.php');

// Check if user is logged in
if (!isset($_SESSION['is_authenticated']) || !$_SESSION['is_authenticated']) {
    header("Location: ../auth/login.php");
    exit();
}

// Check if listing ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$listing_id = (int)$_GET['id'];
$listing = getListingDetails($conn, $listing_id);

// Check if listing exists
if (!$listing) {
    header("Location: index.php");
    exit();
}

// Get listing images
$images = getListingImages($conn, $listing_id);

// Handle purchase request
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase'])) {
    $quantity = (float)$_POST['quantity'];
    $buyer_id = $_SESSION['user_id'];
    $seller_id = $listing['user_id'];
    
    // Validate quantity
    if ($quantity <= 0) {
        $error_message = "Please enter a valid quantity.";
    } elseif ($quantity > $listing['quantity']) {
        $error_message = "Requested quantity exceeds available stock.";
    } else {
        // Calculate total amount
        $total_amount = $quantity * $listing['price'];
        
        // Create transaction
        $transaction_id = createTransaction(
            $conn, 
            $buyer_id, 
            $seller_id, 
            $listing_id, 
            $quantity, 
            $listing['price'], 
            $total_amount
        );
        
        if ($transaction_id) {
            // Update listing quantity
            $new_quantity = $listing['quantity'] - $quantity;
            $conn->query("UPDATE marketplace_listings SET quantity = $new_quantity WHERE id = $listing_id");
            
            $success_message = "Purchase initiated successfully! Transaction ID: $transaction_id";
        } else {
            $error_message = "Failed to create transaction. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($listing['title']); ?> - SADC Agricultural Marketplace</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .listing-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .listing-images {
            position: relative;
        }
        
        .main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: var(--border-radius);
            margin-bottom: 10px;
        }
        
        .thumbnail-container {
            display: flex;
            gap: 10px;
            overflow-x: auto;
        }
        
        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: var(--border-radius);
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .thumbnail.active {
            border-color: var(--primary-color);
        }
        
        .listing-info h1 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .listing-price {
            font-size: 24px;
            font-weight: bold;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }
        
        .listing-meta {
            margin-bottom: 20px;
        }
        
        .meta-item {
            display: flex;
            margin-bottom: 10px;
        }
        
        .meta-label {
            width: 120px;
            font-weight: bold;
        }
        
        .seller-info {
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }
        
        .seller-info h3 {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .verified-badge-small {
            display: inline-flex;
            width: 20px;
            height: 20px;
            background-color: var(--success-color);
            color: var(--white);
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            margin-left: 5px;
        }
        
        .purchase-form {
            margin-top: 20px;
            padding: 20px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
        }
        
        .purchase-form h3 {
            margin-bottom: 15px;
        }
        
        .quantity-input {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .quantity-input input {
            width: 100px;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            margin: 0 10px;
        }
        
        .total-price {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .listing-detail {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>SADC Agricultural Marketplace</h1>
            <p>Secure trading platform for verified farmers and buyers across Southern Africa.</p>
        </header>
        
        <nav class="marketplace-nav">
            <ul>
                <li class="active"><a href="index.php">Agricultural Products</a></li>
                <li><a href="livestock.php">Livestock</a></li>
                <li><a href="verified-farmers.php">Verified Farmers</a></li>
                <li><a href="my-listings.php">My Listings</a></li>
            </ul>
        </nav>
        
        <main>
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="listing-detail">
                <div class="listing-images">
                    <?php 
                    $main_image = !empty($images) ? $images[0]['image_path'] : '../assets/images/default-product.jpg';
                    ?>
                    <img src="<?php echo htmlspecialchars($main_image); ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>" class="main-image" id="main-image">
                    
                    <?php if (count($images) > 1): ?>
                        <div class="thumbnail-container">
                            <?php foreach ($images as $index => $image): ?>
                                <img 
                                    src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                    alt="Thumbnail <?php echo $index + 1; ?>" 
                                    class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                                    onclick="changeMainImage('<?php echo htmlspecialchars($image['image_path']); ?>', this)"
                                >
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="listing-info">
                    <h1><?php echo htmlspecialchars($listing['title']); ?></h1>
                    
                    <div class="listing-price">
                        <?php echo $listing['currency']; ?> <?php echo number_format($listing['price'], 2); ?> / <?php echo htmlspecialchars($listing['unit']); ?>
                    </div>
                    
                    <div class="listing-meta">
                        <div class="meta-item">
                            <div class="meta-label">Category:</div>
                            <div><?php echo htmlspecialchars($listing['category']); ?></div>
                        </div>
                        
                        <div class="meta-item">
                            <div class="meta-label">Available:</div>
                            <div><?php echo number_format($listing['quantity'], 2); ?> <?php echo htmlspecialchars($listing['unit']); ?></div>
                        </div>
                        
                        <div class="meta-item">
                            <div class="meta-label">Location:</div>
                            <div><?php echo htmlspecialchars($listing['location']); ?>, <?php echo htmlspecialchars($listing['region']); ?></div>
                        </div>
                        
                        <div class="meta-item">
                            <div class="meta-label">Listed on:</div>
                            <div><?php echo date('F j, Y', strtotime($listing['created_at'])); ?></div>
                        </div>
                    </div>
                    
                    <div class="seller-info">
                        <h3>
                            Seller Information
                            <?php if ($listing['verified']): ?>
                                <span class="verified-badge-small" title="Verified Farmer">✓</span>
                            <?php endif; ?>
                        </h3>
                        
                        <div class="meta-item">
                            <div class="meta-label">Name:</div>
                            <div><?php echo htmlspecialchars($listing['seller_name']); ?></div>
                        </div>
                        
                        <div class="meta-item">
                            <div class="meta-label">Farmer ID:</div>
                            <div><?php echo htmlspecialchars($listing['farmer_id']); ?></div>
                        </div>
                        
                        <div class="meta-item">
                            <div class="meta-label">Contact:</div>
                            <div><?php echo htmlspecialchars($listing['seller_email']); ?></div>
                        </div>
                    </div>
                    
                    <div class="listing-description">
                        <h3>Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($listing['description'])); ?></p>
                    </div>
                    
                    <?php if ($listing['user_id'] !== $_SESSION['user_id'] && $listing['quantity'] > 0): ?>
                        <div class="purchase-form">
                            <h3>Purchase this product</h3>
                            
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                                <div class="quantity-input">
                                    <label for="quantity">Quantity:</label>
                                    <input 
                                        type="number" 
                                        id="quantity" 
                                        name="quantity" 
                                        min="0.1" 
                                        max="<?php echo $listing['quantity']; ?>" 
                                        step="0.1" 
                                        value="1"
                                        oninput="updateTotalPrice()"
                                    >
                                    <span><?php echo htmlspecialchars($listing['unit']); ?></span>
                                </div>
                                
                                <div class="total-price" id="total-price">
                                    Total: <?php echo $listing['currency']; ?> <?php echo number_format($listing['price'], 2); ?>
                                </div>
                                
                                <button type="submit" name="purchase" class="btn btn-primary">Purchase Now</button>
                            </form>
                        </div>
                    <?php elseif ($listing['quantity'] <= 0): ?>
                        <div class="alert alert-warning">This product is currently out of stock.</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> SADC Digital Farmer ID Platform. All rights reserved.</p>
        </footer>
    </div>
    
    <script>
        function changeMainImage(src, thumbnail) {
            document.getElementById('main-image').src = src;
            
            // Update active thumbnail
            const thumbnails = document.querySelectorAll('.thumbnail');
            thumbnails.forEach(thumb => {
                thumb.classList.remove('active');
            });
            
            thumbnail.classList.add('active');
        }
        
        function updateTotalPrice() {
            const quantity = parseFloat(document.getElementById('quantity').value) || 0;
            const price = <?php echo $listing['price']; ?>;
            const currency = '<?php echo $listing['currency']; ?>';
            
            const total = quantity * price;
            document.getElementById('total-price').textContent = `Total: ${currency} ${total.toFixed(2)}`;
        }
    </script>
</body>
</html>

