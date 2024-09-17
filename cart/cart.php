<?php
require_once 'session_config.php';

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        // Fetch product details from the database
        // Display product details along with quantity
    }
} else {
    echo "Your cart is empty.";
}
?>