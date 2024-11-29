<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'vendor/autoload.php'; // Include Composer autoloader for MongoDB

use MongoDB\Client;
use Exception;
use MongoDB\Driver\ServerApi;
$config = parse_ini_file('/var/www/private/db-config.ini');
$uri = $config['mongodb_uri'];
$client = new Client($uri);

// Access the database and collection
$db = $client->selectDatabase('somethingqlo'); // Replace with your database name
$ordersCollection = $db->selectCollection('orders');

$orders = [
    [
        'order_id' => 1,
        'user_id' => 1,
        'total_amount' => 44.80,
        'order_date' => '2024-11-21',
        'products' => [
            ['product_id' => 1, 'name' => 'AIRism Cotton Crew Neck Short Sleeve T-shirt', 'color' => 'Black', 'size' => 'S', 'quantity' => 1],
            ['product_id' => 4, 'name' => 'Stretch Slim Fit Shorts', 'color' => 'White', 'size' => 'M', 'quantity' => 1],
        ],
        'payment' => ['method' => 'MasterCard', 'status' => 'Completed']
    ],
    [
        'order_id' => 2,
        'user_id' => 2,
        'total_amount' => 84.90,
        'order_date' => '2024-11-22',
        'products' => [
            ['product_id' => 3, 'name' => 'Ultra Stretch Jeans', 'color' => 'Red', 'size' => 'L', 'quantity' => 1],
            ['product_id' => 6, 'name' => 'Warhol Sweatpants', 'color' => 'Blue', 'size' => 'M', 'quantity' => 1],
        ],
        'payment' => ['method' => 'American Express', 'status' => 'Completed']
    ],
    [
        'order_id' => 3,
        'user_id' => 3,
        'total_amount' => 25.00,
        'order_date' => '2024-11-23',
        'products' => [
            ['product_id' => 6, 'name' => 'Warhol Sweatpants', 'color' => 'Blue', 'size' => 'L', 'quantity' => 1],
        ],
        'payment' => ['method' => 'American Express', 'status' => 'Completed']
    ],
    [
        'order_id' => 4,
        'user_id' => 4,
        'total_amount' => 25.00,
        'order_date' => '2024-11-24',
        'products' => [
            ['product_id' => 6, 'name' => 'Warhol Sweatpants', 'color' => 'Black', 'size' => 'S', 'quantity' => 1],
        ],
        'payment' => ['method' => 'American Express', 'status' => 'Completed']
    ],
    [
        'order_id' => 5,
        'user_id' => 5,
        'total_amount' => 109.80,
        'order_date' => '2024-11-25',
        'products' => [
            ['product_id' => 2, 'name' => 'Dry Sweat Jacket', 'color' => 'Red', 'size' => 'M', 'quantity' => 1],
            ['product_id' => 3, 'name' => 'Ultra Stretch Jeans', 'color' => 'Red', 'size' => 'M', 'quantity' => 1],
        ],
        'payment' => ['method' => 'American Express', 'status' => 'Completed']
    ],
];

// Insert each user into the MongoDB collection
foreach ($orders as $order) {
    $ordersCollection->insertOne($order);
}

echo "orders have been successfully inserted into the MongoDB collection!";
?>