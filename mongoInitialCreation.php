<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php'; // For MongoDB library if using Composer
use Exception;
use MongoDB\Client;
use MongoDB\Driver\ServerApi;

// Load configuration
$config = parse_ini_file('/var/www/private/db-config.ini');
$uri = $config['mongodb_uri'];

// Specify Stable API version 1
$apiVersion = new ServerApi(ServerApi::V1);

// Create a new client and connect to the server
$client = new MongoDB\Client($uri, [], ['serverApi' => $apiVersion]);

try {
    // Send a ping to confirm a successful connection
    $client->selectDatabase('admin')->command(['ping' => 1]);
    echo "Pinged your deployment. You successfully connected to MongoDB!\n";

    // Select the database and collection
    $db = $client->selectDatabase('somethingqlo'); // Replace with your database name
// 1. Create and Insert for "users" Collection
    $usersCollection = $db->selectCollection('users');
    $usersCollection->insertOne([
        "user_id" => 1,
        "email" => "john@example.com",
        "password" => "hashed_password",
        "name" => ["first" => "John", "last" => "Doe"],
        "address" => "123 Main St",
        "is_admin" => false,
        "clicks" => ["101" => 5, "102" => 3]
    ]);

    // 2. Create and Insert for "products" Collection
    $productsCollection = $db->selectCollection('products');
    $productsCollection->insertOne([
        "product_id" => 101,
        "name" => "T-Shirt",
        "description" => "Comfortable cotton t-shirt",
        "category" => ["name" => "Clothing", "description" => "Men's and Women's clothing"],
        "gender" => "Unisex",
        "image_url" => "https://example.com/tshirt.jpg",
        "price" => 19.99,
        "colors" => ["Red", "Blue"],
        "sizes" => ["S", "M", "L"]
    ]);

    // 3. Create and Insert for "orders" Collection
    $ordersCollection = $db->selectCollection('orders');
    $ordersCollection->insertOne([
        "order_id" => 5001,
        "user_id" => 1,
        "total_amount" => 59.98,
        "order_date" => "2024-11-27",
        "products" => [
            ["product_id" => 101, "name" => "T-Shirt", "color" => "Red", "size" => "M", "quantity" => 2],
            ["product_id" => 102, "name" => "Jeans", "color" => "Black", "size" => "L", "quantity" => 1]
        ],
        "payment" => ["method" => "Visa", "status" => "Completed"]
    ]);

    // Success message
    echo "Collections and initial documents created successfully!";

} catch (Exception $e) {
    printf("An error occurred: %s\n", $e->getMessage());
}