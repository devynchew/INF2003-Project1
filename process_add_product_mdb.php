<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}
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


    // Retrieve form data
    $productURL = $_POST['newProductURL'];
    $productName = $_POST['newProductName'];
    $productDescription = $_POST['newProductDescription'];
    $productPrice = $_POST['newProductPrice'];
    $productCategory = $_POST['newProductCategory'];
    $productColors = $_POST['newProductColors'];
    $productSizes = $_POST['newProductSizes'];
    $productGender = $_POST['newProductGender'];
    $productStock = $_POST['newProductStock'];
    
    $pipeline = [
        ['$group' => ['_id' => null, 'maxProductId' => ['$max' => '$product_id']]]
    ];

    $result = $productCollection->aggregate($pipeline)->toArray();
    
    $colorStringArray = [];
    foreach ($productColors as $color) {
        $colorStringArray[] = $color;
    }

    $sizeStringArray = [];
    foreach ($productSizes as $size) {
        $sizeStringArray[] = $size;
    }
    

    $product = [
        "product_id" => $result[0]['maxProductId']+1, 
        "name" => $productName,
        "description" => $productDescription,
        "category" => ['name'=> $productCategory, 'description'=> ''],
        "gender" => $productGender,
        "image_url" => $productURL,
        "price" => $productPrice,
        "colors" => $colorStringArray,
        "sizes" => $sizeStringArray,
        "stock" => $productStock
    ];


    // Insert product into the products table
    $productCollection->insertOne($product);


    header("Location: manage_products_mdb.php?status=success");

?>
