<?php
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

try {
    // Connect to MongoDB
    $client = new MongoDB\Client($uri, [], ['serverApi' => $apiVersion]);
    $db = $client->selectDatabase('somethingqlo');

    $isSuperAdmin = isset($_SESSION['isSuperAdmin']) && $_SESSION['isSuperAdmin'];

    // Handle POST request for updating user data
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['member_id'])) {
        $memberId = (int) sanitize_input($_POST['member_id']);
        $fname = sanitize_input($_POST['fname']);
        $lname = sanitize_input($_POST['lname']);
        $email = sanitize_input($_POST['email']);
        $is_admin = ($isSuperAdmin && isset($_POST['is_admin'])) ? true : false;

        // Create the update document
        $updateFields = [
            'name.first' => $fname,
            'name.last' => $lname,
            'email' => $email
        ];

        // Only update the is_admin field if the user is a superadmin
        if ($isSuperAdmin) {
            $updateFields['is_admin'] = $is_admin;
        }

        // Update the user in the MongoDB collection
        try {
            $updateResult = $collection->updateOne(
                ['user_id' => $memberId],
                ['$set' => $updateFields]
            );

            if ($updateResult->getModifiedCount() > 0) {
                unset($_SESSION['errorMsg']);
                $_SESSION['successMsg'] = "Success!";
            } else {
                $_SESSION['errorMsg'] = "No changes were made.";
            }
        } catch (Exception $e) {
            $_SESSION['errorMsg'] = "Failed to update user. Error: " . $e->getMessage();
        }
    } else {
        $_SESSION['errorMsg'] = "Invalid request.";
    }

    // Redirect back to manageuser.php
    header("Location: manageuser.php");
    exit;
} catch (MongoDB\Driver\Exception\Exception $e) {
    $errorMsg = "MongoDB connection error: " . $e->getMessage();
    $success = false;
}



function sanitize_input($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}
