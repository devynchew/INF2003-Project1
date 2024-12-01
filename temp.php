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
$apiVersion = new ServerApi(ServerApi::V1);

// Connect to MongoDB
$client = new MongoDB\Client($uri, [], ['serverApi' => $apiVersion]);
$db = $client->selectDatabase('somethingqlo'); 

// Define the users collection
$userCollection = $db->users;

$pipeline = [
    ['$group' => ['_id' => null, 'maxUserId' => ['$max' => '$user_id']]]
];

$result = $userCollection->aggregate($pipeline)->toArray();

if (!empty($result)) {
    echo "Maximum user_id: " . $result[0]['maxUserId'] . "\n";
} else {
    echo "No documents found.\n";
}



// insert into users
$user = $userCollection->insertOne([
                                'user_id' => $result[0]['maxUserId']+1,
                                'name.first' => 'Ruixuan3',
                                'name.last' => 'Lee', 
                                'email' => 'hlrx@gmail.com',
                                'password' => 'godspeed',
                                'address' => 'Ang Mo Kio Ave 4 Blk 123 #05-01',
                                'is_admin' => true,
                                ]);
?>