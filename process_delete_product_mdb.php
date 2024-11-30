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
    $product_id = $_POST['product_id'];

    try {
        $product_id = (int) $product_id;
        $product = $productCollection->findOne(['product_id' => $product_id]);

        if ($product) {
            $delete = $productCollection->deleteOne(['product_id' => $product_id]);
        } else {
            echo "<p>Product not found.</p>";
        }          
        
        // Redirect back to the products page with a deleted message
        header("Location: manage_products_mdb.php?status=deleted");
        exit();
    } catch (Exception $e) {
        // Rollback the transaction if something goes wrong
        mysqli_rollback($connection);
        echo "Failed to delete product: " . $e->getMessage();
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

