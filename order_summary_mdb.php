<?php
require_once 'session_config.php';
require 'vendor/autoload.php'; // Include Composer's autoloader for MongoDB

use Exception;
use MongoDB\Client;
use MongoDB\Driver\ServerApi;

// Check if order summary data exists
if (!isset($_SESSION['order_summary'])) {
    header('Location: cart_mdb.php'); // Redirect to cart if no order summary data is found
    exit;
}

// Retrieve order summary data from session
$orderSummary = $_SESSION['order_summary'];
$items = $orderSummary['items'];
$totalPrice = $orderSummary['totalPrice'];
$orderID = $orderSummary['orderID'];
$firstName = $orderSummary['firstName'];
$lastName = $orderSummary['lastName'];
$email = $orderSummary['email'];

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
$productsCollection = $db->products;

$order = $ordersCollection->findOne(['order_id' => (int) $orderID]);

if ($order === null) {
    // Handle case when no order is found
    echo "Order not found.";
    exit;
}

$products = $order['products'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    include "inc/head.inc.php";
    ?>
    <meta charset="UTF-8">
    <title>Order Summary</title>
</head>

<body>
    <?php

    include "inc/header.inc.php";
    include "inc/nav.inc.php";
    ?>
    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="container">
                <!-- Title -->
                <div class="d-flex justify-content-between align-items-center py-3">
                    <h2 class="h5 mb-1"><a href="#" class="text-muted"></a>Order Summary</h2>
                </div>

                <!-- Main content -->
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Details -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="mb-3 d-flex justify-content-between">
                                    <h3 class="h3">Order Details</h3>
                                    <p>Order ID: &num;<?= htmlspecialchars($orderID) ?><br>
                                        Order Date: <?= htmlspecialchars($order['order_date'])?><br>
                                </div>
                                <table class="table table-borderless">
                                    <thead>
                                        <tr>
                                            <td>Product</td>
                                            <td>Price</td>
                                            <td>Quantity</td>
                                            <td>Subtotal</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($products as $product) : 
                                            $productDetails = $productsCollection->findOne(['product_id' => $product['product_id']]);
                                        ?>
                                            <tr>
                                                <td>
                                                    <?= htmlspecialchars($product['name']) ?>
                                                </td>
                                                <td>&dollar;<?= $productDetails['price']?></td>
                                                <td><?= $product['quantity'] ?></td>
                                                <td>&dollar;<?= $productDetails['price'] * $product['quantity'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3">Total Paid</td>
                                            <td>&dollar;<?= number_format($totalPrice, 2) ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                    </div>
                    <div class="col-lg-4">
                        <!-- Message from the store -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h3 class="h5">Thank You!</h3>
                                <p>We hope that you enjoy your purchase. </p>
                            </div>
                        </div>
                        <div class="buttons">
                            <a href="product.php" class="cart-back-btn">Back to Products</a>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>