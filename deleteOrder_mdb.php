<?php
require 'vendor/autoload.php'; // Include Composer's autoloader for MongoDB

use Exception;
use MongoDB\Client;
use MongoDB\Driver\ServerApi;

session_start();
// Load database configuration
$config = parse_ini_file('/var/www/private/db-config.ini');
$uri = $config['mongodb_uri'];

// Specify Stable API version 1
$apiVersion = new ServerApi(ServerApi::V1);

// Establish database connection
function getMongoDBConnection()
{
    $config = parse_ini_file('/var/www/private/db-config.ini');
    $uri = $config['mongodb_uri'];

    // Specify Stable API version 1
    $apiVersion = new ServerApi(ServerApi::V1);

    try {
        $client = new MongoDB\Client($uri, [], ['serverApi' => $apiVersion]);
        return $client->selectDatabase('somethingqlo');    
    }
    catch (Exception $e) {
        $errorMsg = "Connection failed: " . $e->getMessage();
        $success = false;
    }
}

$db = getMongoDBConnection();

$ordersCollection = $db->orders;

if (isset($_POST['order_id']) && !empty($_POST['order_id'])) {
    $orderID = $_POST['order_id'];

    try {
        $result = $ordersCollection->deleteOne(['order_id' => (int)$orderID]);

        if ($result->getDeletedCount() > 0) {
            $_SESSION['successMsg'] = "Order deleted successfully.";
        } else {
            $_SESSION['errorMsg'] = "No order found with that ID.";
        }
    } catch (Exception $e) {
        $_SESSION['errorMsg'] = "Error deleting the order: " . $e->getMessage();
    }
} else {
    $_SESSION['errorMsg'] = "Invalid order ID.";
}

header("Location: manageorders_mdb.php");
exit;
?>
