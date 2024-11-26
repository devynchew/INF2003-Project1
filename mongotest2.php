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
    $database = $client->selectDatabase('somethingqlo'); // Replace with your database name
    $collection = $database->selectCollection('somethingqlo'); // Replace with your collection name

    // Define the user document
    $user = [
        'user_id' => 1, // Replace with a unique user ID
        'email' => 'john.doe@example.com',
        'password' => password_hash('securepassword', PASSWORD_BCRYPT), // Secure password hashing
        'fname' => 'John',
        'lname' => 'Doe',
        'address' => '123 Main St',
        'is_admin' => false
    ];

    // Insert the document into the collection
    $result = $collection->insertOne($user);

    // Print the result
    echo "Inserted user with ID: " . $result->getInsertedId() . "\n";

} catch (Exception $e) {
    printf("An error occurred: %s\n", $e->getMessage());
}