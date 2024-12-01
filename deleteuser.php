<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once 'session_config.php';
require 'vendor/autoload.php'; // Include Composer's autoloader for MongoDB

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

use Exception;
use MongoDB\Client;
use MongoDB\Driver\ServerApi;

// Load configuration
$config = parse_ini_file('/var/www/private/db-config.ini');
$uri = $config['mongodb_uri'];

// Specify Stable API version 1
$apiVersion = new ServerApi(ServerApi::V1);

$isSuperAdmin = isset($_SESSION['isSuperAdmin']) && $_SESSION['isSuperAdmin'];

// Connect to MongoDB
$client = new MongoDB\Client($uri, [], ['serverApi' => $apiVersion]);
$db = $client->selectDatabase('somethingqlo');
$usersCollection = $db->users;

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
    // Sanitize the input to prevent XSS attacks
    $memberId = sanitize_input($_POST['user_id']);

    try {
        // Convert memberId to an integer if necessary
        $memberId = intval($memberId);

        // Attempt to delete the user
        $deleteResult = $usersCollection->deleteOne(['user_id' => $memberId]);

        // Check if a document was actually deleted
        if ($deleteResult->getDeletedCount() > 0) {
            $_SESSION['successMsg'] = "User deleted successfully.";
        } else {
            $_SESSION['errorMsg'] = "User not found or already deleted.";
        }
    } catch (Exception $e) {
        // Handle errors during the execution of the delete operation
        $_SESSION['errorMsg'] = "Error deleting user: " . $e->getMessage();
    }
} else {
    // Handle cases where the necessary data was not posted
    $_SESSION['errorMsg'] = "Invalid request.";
}

// Redirect back to the manage user page
header("Location: manageuser_mdb.php");
exit;
?>