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
$usersCollection = $db->users;

$orders = $ordersCollection->find();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders</title>
    <!-- Include Bootstrap CSS -->
    <link href="path/to/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php
        include "inc/head.inc.php";
        include "inc/header.inc.php";
        include "inc/nav.inc.php";
    ?>
    <div role="main" class="container">
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
        <h2>Order Management</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Email</th>
                    <th>Order Date</th>
                    <th>Quantity</th>
                    <th>Total Amount</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders): ?>
                <?php foreach ($orders as $order):
                    $user = $usersCollection->findOne(['user_id' => $order['user_id']]);
                ?>
                        <tr>
                            <td><?= $order['order_id'] ?></td>
                            <td><?= $user['email'] ?></td>
                            <td><?= $order['order_date'] ?></td>
                            <td><?= count($order['products']) ?></td>
                            <td><?= $order['total_amount'] ?></td>
                            <td>
                                <?php if ($row['orderStatus'] == 'Request for Refund'): ?>
                                    <form action="approveRefund.php" method="post" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                <?php endif; ?>
                                <form action="deleteOrder_mdb.php" method="post" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this order?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">No orders found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="text-center">
            <a href="adminpage.php" class="btn btn-secondary mr-2">Back to Admin</a>
        </div>
    </div>
</body>
</html>

