<?php
// config/database.php

$host = 'localhost';
$db_name = 'graha_clouth';
$username = 'root'; // Adjust as per your local server config
$password = ''; // Adjust as per your local server config

try {
    $conn = new PDO("mysql:host={$host};dbname={$db_name};charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to format price to IDR
function format_rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

// Function to check if a user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Global settings
$whatsapp_number = '6281234567890'; // Replace with actual number
$instagram_account = '@grahaclouth'; // Replace with actual account

/**
 * Helper to get real product image or fallback curated image for a category
 * Dynamically resolves latest product image in category or high quality category fallback
 */
function getCategoryRealImage($categoryName, $dbSampleImage = null, $isAdmin = false) {
    if (!empty($dbSampleImage)) {
        $img = trim($dbSampleImage);
        if (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0) {
            return $img;
        }
        $cleanImg = ltrim($img, '/');
        if ($isAdmin) {
            return (strpos($cleanImg, '../') === 0) ? $cleanImg : '../' . $cleanImg;
        }
        return $cleanImg;
    }

    $name = strtolower(trim($categoryName));
    
    // Curated high quality real clothing & fashion product photography fallback
    if (strpos($name, 'kaos') !== false || strpos($name, 't-shirt') !== false || strpos($name, 'tshirt') !== false || strpos($name, 'tee') !== false) {
        return 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600&h=800&fit=crop&q=80'; // Real T-Shirt
    } elseif (strpos($name, 'kemeja') !== false || strpos($name, 'batik') !== false || strpos($name, 'formal') !== false) {
        return 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=600&h=800&fit=crop&q=80'; // Real Shirt
    } elseif (strpos($name, 'hoodie') !== false || strpos($name, 'sweater') !== false || strpos($name, 'sweatshirt') !== false) {
        return 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600&h=800&fit=crop&q=80'; // Real Hoodie
    } elseif (strpos($name, 'jaket') !== false || strpos($name, 'jacket') !== false || strpos($name, 'outerwear') !== false || strpos($name, 'coat') !== false) {
        return 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=600&h=800&fit=crop&q=80'; // Real Jacket
    } elseif (strpos($name, 'celana') !== false || strpos($name, 'pants') !== false || strpos($name, 'jeans') !== false || strpos($name, 'trousers') !== false || strpos($name, 'chinos') !== false) {
        return 'https://images.unsplash.com/photo-1624378439575-d1ead6bb2414?w=600&h=800&fit=crop&q=80'; // Real Pants
    } elseif (strpos($name, 'aksesoris') !== false || strpos($name, 'accessories') !== false || strpos($name, 'perhiasan') !== false) {
        return 'https://images.unsplash.com/photo-1523293182086-7651a899d37f?w=600&h=800&fit=crop&q=80'; // Real Accessories
    } elseif (strpos($name, 'sepatu') !== false || strpos($name, 'shoes') !== false || strpos($name, 'sneakers') !== false || strpos($name, 'sandal') !== false) {
        return 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&h=800&fit=crop&q=80'; // Shoes
    } elseif (strpos($name, 'topi') !== false || strpos($name, 'hat') !== false || strpos($name, 'cap') !== false) {
        return 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=600&h=800&fit=crop&q=80'; // Cap
    } elseif (strpos($name, 'tas') !== false || strpos($name, 'bag') !== false || strpos($name, 'backpack') !== false || strpos($name, 'ransel') !== false) {
        return 'https://images.unsplash.com/photo-1597484661643-2f5fef640df1?w=600&h=800&fit=crop&q=80'; // Bag
    } elseif (strpos($name, 'dress') !== false || strpos($name, 'gaun') !== false || strpos($name, 'rok') !== false || strpos($name, 'wanita') !== false) {
        return 'https://images.unsplash.com/photo-1492707892479-7bc8d5a4ee93?w=600&h=800&fit=crop&q=80'; // Dress
    }
    
    return 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=600&h=800&fit=crop&q=80';
}

function getCategoryCoverImage($categoryName, $dbSampleImage = null, $isAdmin = false) {
    return getCategoryRealImage($categoryName, $dbSampleImage, $isAdmin);
}
?>
