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
$collection = $db->products;

/*
// below is for duplicating collections
$pipeline = [
    ['$out' => 'products_noindex'] // Output to the new collection
];

try {
    $collection->aggregate($pipeline);
    echo "Collection duplicated successfully.\n";
} catch (Exception $e) {
    echo "Error duplicating collection: " . $e->getMessage() . "\n";
}*/
/*
// Create an index on the category.name field
$indexName = $collection->createIndex(
    ['category.name' => 1], // 1 for ascending order index
    ['name' => 'category_name_index'] // specify a name for the index
);

$results = $collection->find(['category.name' => 'Top']);
foreach ($results as $document) {
    print_r($document);
}*/
//echo "Index created: $indexName\n";
?>