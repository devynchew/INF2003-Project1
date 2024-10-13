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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = mysqli_real_escape_string($connection, $_POST['product_id']);

    try {
        // Delete associated product colors
        $delete_product_colors_sql = "DELETE FROM productcolors WHERE product_id = '$product_id'";
        mysqli_query($connection, $delete_product_colors_sql);

        // Delete associated product sizes
        $delete_product_sizes_sql = "DELETE FROM productsizes WHERE product_id = '$product_id'";
        mysqli_query($connection, $delete_product_sizes_sql);

        // Delete associated ordersproduct entries
        $delete_orders_product_sql = "DELETE FROM ordersproduct WHERE product_id = '$product_id'";
        mysqli_query($connection, $delete_orders_product_sql);

        // Finally, delete the product itself
        $delete_product_sql = "DELETE FROM products WHERE product_id = '$product_id'";
        mysqli_query($connection, $delete_product_sql);

        // Commit transaction
        mysqli_commit($connection);

        // Redirect with success status
        header("Location: manage_products.php?status=deleted");
        
    } catch (Exception $e) {
        // Rollback transaction in case of error
        mysqli_rollback($connection);

        // Redirect with error status
        header("Location: manage_products.php?status=error");
    }
    
} else {
    header("Location: manage_products.php?status=error");
}

mysqli_close($connection);
?>
