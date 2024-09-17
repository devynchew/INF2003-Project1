<?php
require_once 'session_config.php';

// Check if order summary data exists
if (!isset($_SESSION['order_summary'])) {
    header('Location: cart.php'); // Redirect to cart if no order summary data is found
    exit;
}


// Retrieve order summary data from session
$orderSummary = $_SESSION['order_summary'];
$items = $orderSummary['items'];
$subtotal = $orderSummary['subtotal'];
$orderID = $orderSummary['orderID'];
$firstName = $orderSummary['firstName'];
$lastName = $orderSummary['lastName'];
$email = $orderSummary['email'];

// Establish database connection
function getDatabaseConnection()
{
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        die("Failed to read database config file.");
    }

    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

$conn = getDatabaseConnection();

// Fetch order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE orderID = ?");
$stmt->bind_param('i', $orderID);
$stmt->execute();
$orderDetails = $stmt->get_result()->fetch_assoc();

// Fetch payment details
// Fetch order details
$stmt = $conn->prepare("SELECT * FROM payment WHERE orderID = ?");
$stmt->bind_param('i', $orderID);
$stmt->execute();
$paymentDetails = $stmt->get_result()->fetch_assoc();

// Close the statement and connection
$stmt->close();
$conn->close();


$addressParts = explode(',', $orderDetails['shippingAddress']);

$address = $addressParts[0] ?? '';
$countryCode = $addressParts[1] ?? '';
$zip = $addressParts[2] ?? '';
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
                                        Order Date and Time: <?= htmlspecialchars($orderDetails['orderDate']) ?><br>
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
                                        <?php foreach ($items as $item) : ?>
                                            <tr>
                                                <td>
                                                    <?= htmlspecialchars($item['name']) ?>
                                                </td>
                                                <td>&dollar;<?= number_format($item['price'], 2) ?></td>
                                                <td><?= $item['quantity'] ?></td>
                                                <td>&dollar;<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3">Total Paid</td>
                                            <td>&dollar;<?= number_format($subtotal, 2) ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <!-- Payment -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <h3 class="h5">Payment Method</h3>
                                        <p><?= htmlspecialchars($paymentDetails['paymentMethod']) ?><br>
                                            Total: &dollar;<?= number_format($subtotal, 2) ?> <span class="badge bg-success rounded-pill">PAID</span></p>
                                    </div>
                                    <div class="col-lg-6">
                                        <h3 class="h5">Billing address</h3>
                                        <address>
                                            <strong><?= htmlspecialchars($firstName) . ' ' . htmlspecialchars($lastName) ?></strong><br>
                                            <?= htmlspecialchars($address) ?><br>
                                            <?= htmlspecialchars($countryCode) ?>, <?= htmlspecialchars($zip) ?><br>
                                            <p>Email: <?= htmlspecialchars($email) ?></p>
                                        </address>
                                    </div>
                                </div>
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
                        <div class="card mb-4">
                            <!-- Shipping information -->
                            <div class="card-body">
                                <h3 class="h5">Shipping Information</h3>
                                <strong>Order status:</strong>
                                <p><?= htmlspecialchars($orderDetails['orderStatus']) ?></p>
                                <hr>
                                <h3 class="h5">Address</h3>
                                <address>
                                    <strong><?= htmlspecialchars($firstName) . ' ' . htmlspecialchars($lastName) ?></strong><br>
                                    <?= htmlspecialchars($address) ?><br>
                                    <?= htmlspecialchars($countryCode) ?>, <?= htmlspecialchars($zip) ?><br>
                                    <p>Email: <?= htmlspecialchars($email) ?></p>
                                </address>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>