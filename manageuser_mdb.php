<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
session_start();

require_once 'session_config.php';
require 'vendor/autoload.php'; // Include Composer's autoloader for MongoDB

// Check if the user is logged in and if they are an admin or superadmin
// This is a simplified check; your actual implementation might be different
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

// Flag to determine if the current user is a superadmin
$isSuperAdmin = isset($_SESSION['isSuperAdmin']) && $_SESSION['isSuperAdmin'];

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

// Define the users collection
$usersCollection = $db->users;
$users = $usersCollection->find();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
</head>

<body>
    <?php
    include "inc/head.inc.php";
    include "inc/header.inc.php";
    include "inc/nav.inc.php";
    ?>
    <div role="main" class="row justify-content-center">
        <div class="col-md-10">
            <?php if (isset($_SESSION['successMsg'])): ?>
                <div class="alert alert-success" role="alert">
                    <?= $_SESSION['successMsg'] ?>
                </div>
                <?php unset($_SESSION['successMsg']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['errorMsg'])): ?>
                <div class="alert alert-danger" role="alert">
                    <?= $_SESSION['errorMsg'] ?>
                </div>
                <?php unset($_SESSION['errorMsg']); ?>
            <?php endif; ?>

            <h2 class="text-center">Manage Users</h2>
            <div class="table-responsive">
                <table class="table table-bordered mx-auto">
                    <thead>
                        <tr>
                            <th>Member ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <?php if ($isSuperAdmin): ?>
                                <th>Admin</th>
                            <?php endif; ?>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['user_id']) ?></td>
                                <td id="fname_<?= $user['user_id'] ?>"><?= htmlspecialchars($user['name']['first']) ?></td>
                                <td id="lname_<?= $user['user_id'] ?>"><?= htmlspecialchars($user['name']['last']) ?></td>
                                <td id="email_<?= $user['user_id'] ?>"><?= htmlspecialchars($user['email']) ?></td>
                                <?php if ($isSuperAdmin): ?>
                                    <td id="is_admin_<?= $user['user_id'] ?>"><?= isset($user['is_admin']) && $user['is_admin'] ? 'Yes' : 'No' ?></td>
                                <?php endif; ?>
                                <td id="actions_<?= $user['user_id'] ?>">
                                    <button type="button" onclick="editRow('<?= $user['user_id'] ?>')" class="btn btn-primary">Edit</button>
                                    <form action="deleteuser.php" method="post" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display: inline-block;">
                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['user_id']) ?>">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="text-center">
                    <a href="adminpage.php" class="btn btn-secondary mr-2">Back to Admin</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>