<?php
session_start();
require_once('../lib/database.php');

// Check if user is logged in
if (!isset($_SESSION['is_authenticated']) || !$_SESSION['is_authenticated']) {
    header("Location: ../auth/login.php");
    exit();
}

// Function to get marketplace listings
function getMarketplaceListings($conn, $category = null, $region = null, $search = null, $limit = 12, $offset = 0) {
    $where_clauses = ["l.status = 'active'"];
    
    if ($category && $category !== 'All Categories') {
        $category = sanitize($conn, $category);
        $where_clauses[] = "l.category = '$category'";
    }
    
    if ($region && $region !== 'All Regions') {
        $region = sanitize($conn, $region);
        $where_clauses[] = "l.region = '$region'";
    }
    
    if ($search) {
        $search = sanitize($conn, $search);
        $where_clauses[] = "(l.title LIKE '%$search%' OR l.description LIKE '%$search%')";
    }
    
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
    
    $query = "SELECT l.*, u.name as seller_name, u.farmer_id, u.verified 
              FROM marketplace_listings l
              JOIN users u ON l.user_id = u.id
              $where_sql
              ORDER BY l.created_at DESC
              LIMIT $limit OFFSET $offset";
    
    $result = $conn->query($query);
    
    if ($result) {
        $listings = [];
        while ($row = $result->fetch_assoc()) {
            $listings[] = $row;
        }
        return $listings;
    } else {
        handleDatabaseError($conn, $query);
        return [];
    }
}

// Function to get listing details
function getListingDetails($conn, $listing_id) {
    $listing_id = (int)$listing_id;
    
    $query = "SELECT l.*, u.name as seller_name, u.farmer_id, u.verified, u.email as seller_email
              FROM marketplace_listings l
              JOIN users u ON l.user_id = u.id
              WHERE l.id = $listing_id";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return false;
    }
}

// Function to create a new listing
function createListing($conn, $user_id, $data) {
    $user_id = (int)$user_id;
    
    $title = sanitize($conn, $data['title']);
    $description = sanitize($conn, $data['description']);
    $category = sanitize($conn, $data['category']);
    $price = (float)$data['price'];
    $currency = sanitize($conn, $data['currency']);
    $quantity = (float)$data['quantity'];
    $unit = sanitize($conn, $data['unit']);
    $region = sanitize($conn, $data['region']);
    $location = sanitize($conn, $data['location']);
    
    $query = "INSERT INTO marketplace_listings (user_id, title, description, category, price, currency, quantity, unit, region, location, status, created_at) 
              VALUES ($user_id, '$title', '$description', '$category', $price, '$currency', $quantity, '$unit', '$region', '$location', 'active', NOW())";
    
    if ($conn->query($query)) {
        $listing_id = $conn->insert_id;
        
        // Handle image uploads if any
        if (isset($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $index => $image_path) {
                $image_path = sanitize($conn, $image_path);
                $query = "INSERT INTO listing_images (listing_id, image_path, sort_order) 
                          VALUES ($listing_id, '$image_path', $index)";
                $conn->query($query);
            }
        }
        
        return $listing_id;
    } else {
        handleDatabaseError($conn, $query);
        return false;
    }
}

// Function to update a listing
function updateListing($conn, $listing_id, $user_id, $data) {
    $listing_id = (int)$listing_id;
    $user_id = (int)$user_id;
    
    // Check if user owns the listing
    $query = "SELECT id FROM marketplace_listings WHERE id = $listing_id AND user_id = $user_id";
    $result = $conn->query($query);
    
    if (!$result || $result->num_rows === 0) {
        return false;
    }
    
    $fields = [];
    foreach ($data as $key => $value) {
        if ($key !== 'images') {
            if (is_numeric($value)) {
                $fields[] = "$key = $value";
            } else {
                $value = sanitize($conn, $value);
                $fields[] = "$key = '$value'";
            }
        }
    }
    
    $fields[] = "updated_at = NOW()";
    $fields_str = implode(", ", $fields);
    
    $query = "UPDATE marketplace_listings SET $fields_str WHERE id = $listing_id";
    
    if ($conn->query($query)) {
        // Handle image uploads if any
        if (isset($data['images']) && is_array($data['images'])) {
            // Delete existing images
            $conn->query("DELETE FROM listing_images WHERE listing_id = $listing_id");
            
            // Add new images
            foreach ($data['images'] as $index => $image_path) {
                $image_path = sanitize($conn, $image_path);
                $query = "INSERT INTO listing_images (listing_id, image_path, sort_order) 
                          VALUES ($listing_id, '$image_path', $index)";
                $conn->query($query);
            }
        }
        
        return true;
    } else {
        handleDatabaseError($conn, $query);
        return false;
    }
}

// Function to delete a listing
function deleteListing($conn, $listing_id, $user_id) {
    $listing_id = (int)$listing_id;
    $user_id = (int)$user_id;
    
    // Check if user owns the listing
    $query = "SELECT id FROM marketplace_listings WHERE id = $listing_id AND user_id = $user_id";
    $result = $conn->query($query);
    
    if (!$result || $result->num_rows === 0) {
        return false;
    }
    
    // Set listing status to deleted
    $query = "UPDATE marketplace_listings SET status = 'deleted', updated_at = NOW() WHERE id = $listing_id";
    
    if ($conn->query($query)) {
        return true;
    } else {
        handleDatabaseError($conn, $query);
        return false;
    }
}

// Function to get user listings
function getUserListings($conn, $user_id, $status = 'active', $limit = 10, $offset = 0) {
    $user_id = (int)$user_id;
    $status = sanitize($conn, $status);
    
    $query = "SELECT * FROM marketplace_listings 
              WHERE user_id = $user_id AND status = '$status' 
              ORDER BY created_at DESC
              LIMIT $limit OFFSET $offset";
    
    $result = $conn->query($query);
    
    if ($result) {
        $listings = [];
        while ($row = $result->fetch_assoc()) {
            $listings[] = $row;
        }
        return $listings;
    } else {
        handleDatabaseError($conn, $query);
        return [];
    }
}

// Function to get listing images
function getListingImages($conn, $listing_id) {
    $listing_id = (int)$listing_id;
    
    $query = "SELECT * FROM listing_images 
              WHERE listing_id = $listing_id 
              ORDER BY sort_order";
    
    $result = $conn->query($query);
    
    if ($result) {
        $images = [];
        while ($row = $result->fetch_assoc()) {
            $images[] = $row;
        }
        return $images;
    } else {
        handleDatabaseError($conn, $query);
        return [];
    }
}

// Function to get available categories
function getCategories($conn) {
    $query = "SELECT DISTINCT category FROM marketplace_listings WHERE status = 'active' ORDER BY category";
    
    $result = $conn->query($query);
    
    if ($result) {
        $categories = ['All Categories'];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['category'];
        }
        return $categories;
    } else {
        handleDatabaseError($conn, $query);
        return ['All Categories'];
    }
}

// Function to get available regions
function getRegions($conn) {
    $query = "SELECT DISTINCT region FROM marketplace_listings WHERE status = 'active' ORDER BY region";
    
    $result = $conn->query($query);
    
    if ($result) {
        $regions = ['All Regions'];
        while ($row = $result->fetch_assoc()) {
            $regions[] = $row['region'];
        }
        return $regions;
    } else {
        handleDatabaseError($conn, $query);
        return ['All Regions'];
    }
}
?>

