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
$usersCollection = $db->selectCollection('users');

$users = [
    [
        'user_id' => 1,
        'email' => 'john@gmail.com',
        'password' => '$2y$10$SHq1gIOCtusm1id681cDWerYHbj6lOYcDsV4L08y6fs.a5Oz.ja2q',
        'name' => ['first' => 'John', 'last' => 'Tan'],
        'address' => 'Block 542B Boon Lay #10-139',
        'is_admin' => True,
        'clicks' => ["101" => 5, "102" => 3]
    ],
    [
        'user_id' => 2,
        'email' => 'adam@gmail.com',
        'password' => '$2y$10$QwSzgSU5zAkK9VmeLTpha.D0HQMV0EEqSGBVPOhPESOhEL7SNpVY2',
        'name' => ['first' => 'Adam', 'last' => 'Goh'],
        'address' => 'Block 937C Tampines #02-441',
        'is_admin' => True,
        'clicks' => ["101" => 1]
    ],
    [
        'user_id' => 3,
        'email' => 'jeannie@gmail.com',
        'password' => '$2y$10$cifmnuK0AeKLtQ7ru.yc7eaHTriAVg3rpk.su/rFdU/2hWlCjxgju',
        'name' => ['first' => 'Jeannie', 'last' => 'Leee'],
        'address' => 'Block 234A Pasir Ris #12-341',
        'is_admin' => False,
        'clicks' => []
    ],
    [
        'user_id' => 4,
        'email' => 'fiona@gmail.com',
        'password' => '$2y$10$1k31zXSWl9U4unQ2EzW3uOrBzPD5mNxcfbCJuauv/F/RqaiNsl4fO',
        'name' => ['first' => 'Fiona', 'last' => 'Chany'],
        'address' => 'Block 123B Bukit Panjang #03-123',
        'is_admin' => False,
        'clicks' => []
    ],
    [
        'user_id' => 5,
        'email' => 'lily@gmail.com',
        'password' => '$2y$10$AmSuyncZ8PyKQKpYVmcBiu1z1DmIISLkWwLkDY85G8gohwHiv.Sni',
        'name' => ['first' => 'Lily', 'last' => 'Choo'],
        'address' => 'Block 177A Ang Mo Kio #11-466',
        'is_admin' => False,
        'clicks' => []
    ],
];

// Insert each user into the MongoDB collection
foreach ($users as $user) {
    $usersCollection->insertOne($user);
}

echo "Users have been successfully inserted into the MongoDB collection!";
?>