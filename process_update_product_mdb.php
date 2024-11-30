<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
require_once 'session_config.php';
require 'vendor/autoload.php'; // Include Composer's autoloader for MongoDB

use Exception;
use MongoDB\Client;
use MongoDB\Driver\ServerApi;

// Load configuration
$config = parse_ini_file('/var/www/private/db-config.ini');
$uri = $config['mongodb_uri'];

// Specify Stable API version 1
$apiVersion = new ServerApi(ServerApi::V1);

// Connect to MongoDB
$client = new MongoDB\Client($uri, [], ['serverApi' => $apiVersion]);
$db = $client->selectDatabase('somethingqlo'); 

// Define the products collection
$productCollection = $db->products;

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get product details from POST request
    $product_id = $_POST['productId'];
    $product_name = $_POST['productName'];
    $product_description = $_POST['productDescription'];
    $product_price = $_POST['productPrice'];
    $product_category = $_POST['product_category'];
    $product_colors = isset($_POST['productColors']) ? $_POST['productColors'] : [];
    $product_sizes = isset($_POST['productSizes']) ? $_POST['productSizes'] : [];
    $product_gender = $_POST['productGender'];

    try {
        $product_id = (int) $product_id;
        $product = $productCollection->findOne(['product_id' => $product_id]);

        if ($product) {
            $update = $productCollection->updateOne(
                ['product_id' => $product_id], 
                ['$set' => [
                    'name' => $product_name, 
                    'description' => $product_description,  
                    'price' => $product_price,
                    'gender' => $product_gender,
                    'category.name' => $product_category,
                    'colors' => $product_colors,
                    'sizes' => $product_sizes
                ]]);
        } else {
            echo "<p>Product not found.</p>";
        }          
        
        // Redirect back to the products page with a success message
        header("Location: manage_products_mdb.php?status=success");
        exit();
    } catch (Exception $e) {
        // Rollback the transaction if something goes wrong
        mysqli_rollback($connection);
        echo "Failed to update product: " . $e->getMessage();
        // Redirect back to the products page with an error message
        header("Location: manage_products_mdb.php?status=error");
        exit();
    }
} else {
    // If the request method is not POST, redirect back to the products page
    header("Location: manage_products_mdb.php");
    exit();
}


?>

