<?php
// Initial setup and requirements
require_once 'session_config.php';
require 'vendor/autoload.php';

use Exception;
use MongoDB\Client;
use MongoDB\Driver\ServerApi;

// MongoDB connection setup
$config = parse_ini_file('/var/www/private/db-config.ini');
$uri = $config['mongodb_uri'];
$apiVersion = new ServerApi(ServerApi::V1);

try {
    $client = new MongoDB\Client($uri, [], ['serverApi' => $apiVersion]);
    $db = $client->selectDatabase('somethingqlo');
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Debug info render function
function renderDebugInfo($db) {
    $debugInfo = "";
    $debugInfo .= "<div class='alert alert-info'>Connected to database: " . $db->getDatabaseName() . "</div>";
    
    $count = $db->orders->countDocuments();
    $debugInfo .= "<div class='alert alert-info'>Number of documents in orders collection: " . $count . "</div>";
    
    $debugInfo .= "<div class='alert alert-info'>First few documents in collection:<pre>";
    $cursor = $db->orders->find([], ['limit' => 3]);
    foreach ($cursor as $doc) {
        $debugInfo .= print_r($doc, true);
    }
    $debugInfo .= "</pre></div>";
    
    return $debugInfo;
}
?>

<!DOCTYPE html>
<html lang="en">

<body>
    <?php
    // Session check
    session_start();
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        header("Location: login.php");
        exit;
    }
    
    // Include headers
    include "inc/head.inc.php";
    include "inc/header.inc.php";
    include "inc/nav.inc.php";
    ?>

    <main class="row justify-content-center">
        <div class="col-md-10">
            <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Report generated successfully.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php elseif (isset($_GET['status']) && $_GET['status'] == 'error'): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> Failed to generate report. Please try again.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <h2 class="text-center mt-4">Payment Method Insights</h2>

            <!-- Debug Toggle Button -->
            <div class="text-right mb-3">
                <button class="btn btn-secondary" type="button" data-toggle="collapse" data-target="#debugInfo" aria-expanded="false" aria-controls="debugInfo">
                    Toggle Debug Info
                </button>
            </div>

            <!-- Debug Information Section -->
            <div class="collapse" id="debugInfo">
                <?php echo renderDebugInfo($db); ?>
            </div>

            <?php
            try {
                // Initialize data arrays
                $labels = [];
                $data = [];
                $table_rows = "";

                // Main aggregation pipeline
                $pipeline = [
                    [
                        '$group' => [
                            '_id' => '$payment.method',
                            'transaction_count' => ['$sum' => 1],
                            'total_amount' => ['$sum' => '$total_amount']
                        ]
                    ],
                    [
                        '$sort' => ['_id' => 1]
                    ]
                ];

                // Apply payment method filter if selected
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['payment_method']) && $_POST['payment_method'] != 'all') {
                    array_unshift($pipeline, [
                        '$match' => ['payment.method' => $_POST['payment_method']]
                    ]);
                }

                // Execute aggregation
                $result = $db->orders->aggregate($pipeline);
                $resultArray = iterator_to_array($result);

                // Process results
                foreach ($resultArray as $row) {
                    $payment_method = $row->_id;
                    $transaction_count = $row->transaction_count;
                    $total_amount = number_format($row->total_amount, 2);
                    
                    $labels[] = $payment_method;
                    $data[] = $row->total_amount;
                    
                    $table_rows .= "<tr>
                                    <td>{$payment_method}</td>
                                    <td>{$transaction_count}</td>
                                    <td>\${$total_amount}</td>
                                    <td>
                                        <form method='POST' action=''>
                                            <input type='hidden' name='view_details' value='{$payment_method}'>
                                            <button type='submit' class='btn btn-info'>View Details</button>
                                        </form>
                                    </td>
                                </tr>";
                }

                // Prepare chart data
                $labels_json = json_encode($labels);
                $data_json = json_encode($data);
            ?>
                <!-- Filter Form -->
                <form method="POST" action="" class="mb-4">
                    <div class="text-center">
                        <label for="dropdown1">Payment Method:</label>
                        <select id="dropdown1" name="payment_method">
                            <option value="all">All</option>
                            <option value="American Express">American Express</option>
                            <option value="Visa">Visa</option>
                            <option value="Mastercard">Mastercard</option>
                        </select>
                        <input type="submit" value="Filter" class="btn btn-primary">
                    </div>
                </form>

                <!-- Summary Table -->
                <h3 class="text-center mt-4">Transactions Summary</h3>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Payment Method</th>
                                <th>Transaction Count</th>
                                <th>Total Amount</th>
                                <th>View More Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?= $table_rows; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Chart -->
                <div class="table-responsive mt-4">
                    <canvas id="myChart" width="300" height="150"></canvas>
                </div>

                <?php
                // Handle Details View
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['view_details'])) {
                    $payment_method = $_POST['view_details'];

                    $details_pipeline = [
                        [
                            '$match' => [
                                'payment.method' => $payment_method
                            ]
                        ],
                        [
                            '$lookup' => [
                                'from' => 'users',
                                'localField' => 'user_id',
                                'foreignField' => '_id',
                                'as' => 'user'
                            ]
                        ],
                        ['$unwind' => '$user'],
                        [
                            '$sort' => ['order_date' => -1]
                        ]
                    ];

                    $details_result = $db->orders->aggregate($details_pipeline);
                    $detailsArray = iterator_to_array($details_result);
                    
                    if (!empty($detailsArray)) {
                        echo "<h3 class='text-center mt-4'>Transaction Details for Payment Method: {$payment_method}</h3>
                              <div class='table-responsive'>
                                <table class='table table-striped table-bordered'>
                                    <thead>
                                        <tr>
                                            <th>User Email</th>
                                            <th>Name</th>
                                            <th>Order ID</th>
                                            <th>Total Amount</th>
                                            <th>Order Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>";

                        foreach ($detailsArray as $detail) {
                            $full_name = $detail->user->fname . ' ' . $detail->user->lname;
                            $total_amount = number_format($detail->total_amount, 2);
                            echo "<tr>
                                    <td>{$detail->user->email}</td>
                                    <td>{$full_name}</td>
                                    <td>{$detail->order_id}</td>
                                    <td>\${$total_amount}</td>
                                    <td>{$detail->order_date}</td>
                                    <td>{$detail->payment->status}</td>
                                </tr>";
                        }

                        echo "</tbody></table></div>";
                    } else {
                        echo "<p class='text-center mt-4'>No transaction details found for the selected payment method.</p>";
                    }
                }
                ?>

                <!-- Back Button -->
                <div class="mt-4 mb-4">
                    <a href="adminpage.php" class="btn btn-secondary">Back to Admin</a>
                </div>

                <!-- Chart Script -->
                <script>
                    var ctx = document.getElementById('myChart').getContext('2d');
                    var chart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: <?php echo $labels_json; ?>,
                            datasets: [{
                                label: 'Total Amount',
                                data: <?php echo $data_json; ?>,
                                backgroundColor: [
                                    'rgba(54, 162, 235, 0.6)',
                                    'rgba(255, 99, 132, 0.6)',
                                    'rgba(255, 206, 86, 0.6)',
                                    'rgba(75, 192, 192, 0.6)'
                                ],
                                borderColor: [
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(255, 99, 132, 1)',
                                    'rgba(255, 206, 86, 1)',
                                    'rgba(75, 192, 192, 1)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 20
                                    }
                                }
                            }
                        }
                    });
                </script>

            <?php
            } catch (Exception $e) {
                echo "<div class='alert alert-danger' role='alert'>An error occurred: " . $e->getMessage() . "</div>";
            }
            ?>
        </div>
    </main>
</body>
</html>