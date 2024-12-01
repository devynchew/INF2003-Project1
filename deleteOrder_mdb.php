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

// Function to lock
function lock($db, $resource, $lockTimeout = 10, $retryInterval = 100, $maxRetries = 50) {
    $expiresAt = new MongoDB\BSON\UTCDateTime((time() + $lockTimeout) * 1000);

    for ($i = 0; $i < $maxRetries; $i++) {
        try {
            $db->locks->insertOne([
                'resource' => $resource,
                'locked_at' => new MongoDB\BSON\UTCDateTime(),
                'expires_at' => $expiresAt
            ]);
            return true; 
        } catch (MongoDB\Driver\Exception\Exception $e) {

            $existingLock = $db->locks->findOne(['resource' => $resource]);
            if ($existingLock && isset($existingLock['expires_at']) && $existingLock['expires_at'] < new MongoDB\BSON\UTCDateTime()) {

                $db->locks->deleteOne(['resource' => $resource]);
                continue; 
            }
        }

        usleep($retryInterval * 1000); 
    }

    return false; 
}
// Function to release lock
function releaseLock($db, $resource) {
    $db->locks->deleteOne(['resource' => $resource]);
}


$db = getMongoDBConnection();

$ordersCollection = $db->orders;

if (isset($_POST['order_id']) && !empty($_POST['order_id'])) {
    $orderID = $_POST['order_id'];

    if (lock($db, $orderID)) {
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
        finally {
            // Release the lock
            releaseLock($db, $orderID);
        }
    } else {
        // Lock acquisition failed
        $_SESSION['errorMsg'] = "Unable to acquire lock for order ID. Please try again.";
    }
} else {
    $_SESSION['errorMsg'] = "Invalid order ID.";
}

header("Location: manageorders_mdb.php");
exit;
?>
