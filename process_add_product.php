<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

$config = parse_ini_file('/var/www/private/db-config.ini');
$servername = $config['servername'];
$username = $config['username'];
$password = $config['password'];
$dbname = $config['dbname'];

$connection = mysqli_connect($servername, $username, $password, $dbname);

if (!$connection) {
    header("Location: manage_products.php?status=error");
    exit;
}

// Retrieve form data
$productName = mysqli_real_escape_string($connection, $_POST['newProductName']);
$productDescription = mysqli_real_escape_string($connection, $_POST['newProductDescription']);
$productPrice = mysqli_real_escape_string($connection, $_POST['newProductPrice']);
$productCategory = mysqli_real_escape_string($connection, $_POST['newProductCategory']);
$productColors = $_POST['newProductColors'];
$productSizes = $_POST['newProductSizes'];
$productGender = mysqli_real_escape_string($connection, $_POST['newProductGender']);

// Insert product into the products table
$product_sql = "INSERT INTO products (name, description, price, category_id, gender) VALUES ('$productName', '$productDescription', '$productPrice', '$productCategory', '$productGender')";
if (mysqli_query($connection, $product_sql)) {
    $productId = mysqli_insert_id($connection);

    // Insert colors
    foreach ($productColors as $colorId) {
        $color_sql = "INSERT INTO productcolors (product_id, color_id) VALUES ('$productId', '$colorId')";
        mysqli_query($connection, $color_sql);
    }

    // Insert sizes
    foreach ($productSizes as $sizeId) {
        $size_sql = "INSERT INTO productsizes (product_id, size_id) VALUES ('$productId', '$sizeId')";
        mysqli_query($connection, $size_sql);
    }

    header("Location: manage_products.php?status=success");
} else {
    header("Location: manage_products.php?status=error");
}

mysqli_close($connection);
?>
