<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'session_config.php';

if (isset($_POST['product_id']) && isset($_POST['quantity']) && isset($_POST['colors']) && isset($_POST['sizes'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $color = $_POST['colors'];
    $size = $_POST['sizes'];

    // Generate a unique key for each combination of product_id, color, and size
    $itemKey = $product_id . '-' . $color . '-' . $size;

    // Check if the exact combination (product + color + size) already exists in the cart
    if (isset($_SESSION['cart'][$itemKey])) {
        // If it exists, update the quantity
        $_SESSION['cart'][$itemKey]['quantity'] += $quantity;
    } else {
        // If it doesn't exist, add it as a new item in the cart
        $_SESSION['cart'][$itemKey] = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'color' => $color,
            'size' => $size,
        ];
    }
}

header("Location: cart.php");
exit();
