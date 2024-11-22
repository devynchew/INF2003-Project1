<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php'; // For MongoDB library if using Composer
use Exception;
use MongoDB\Client;
use MongoDB\Driver\ServerApi;
// Replace the placeholder with your Atlas connection string
$uri = 'mongodb+srv://2301823:UsagiParkdecagram4@cluster0.ugdqd.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0';
// Specify Stable API version 1
$apiVersion = new ServerApi(ServerApi::V1);
// Create a new client and connect to the server
$client = new MongoDB\Client($uri, [], ['serverApi' => $apiVersion]);
try {
    // Send a ping to confirm a successful connection
    $client->selectDatabase('admin')->command(['ping' => 1]);
    echo "Pinged your deployment. You successfully connected to MongoDB!\n";
} catch (Exception $e) {
    printf($e->getMessage());
}