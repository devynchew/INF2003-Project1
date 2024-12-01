<?php
require 'vendor/autoload.php'; // Include Composer's autoloader for MongoDB

use Exception;
use MongoDB\Client;
use MongoDB\Driver\ServerApi;

session_start();

// Load configuration
$config = parse_ini_file('/var/www/private/db-config.ini');
$uri = $config['mongodb_uri'];

// Specify Stable API version 1
$apiVersion = new ServerApi(ServerApi::V1);

// Connect to MongoDB
$client = new MongoDB\Client($uri, [], ['serverApi' => $apiVersion]);
$db = $client->selectDatabase('somethingqlo');

// Define collections
$usersCollection = $db->users;
$ordersCollection = $db->orders;
$productsCollection = $db->products;

// Check if the user is logged in and if they are an admin or superadmin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

$isSuperAdmin = isset($_SESSION['isSuperAdmin']) && $_SESSION['isSuperAdmin'];

// Fetch user data  
$users = $usersCollection->find([], ['projection' => ['user_id' => 1, 'fname' => 1, 'lname' => 1, 'email' => 1, 'is_admin' => 1]]);

// Dropdown for month
$months = [
    "01" => "January", "02" => "February", "03" => "March", 
    "04" => "April", "05" => "May", "06" => "June", 
    "07" => "July", "08" => "August", "09" => "September", 
    "10" => "October", "11" => "November", "12" => "December"
];

$selectedMonth = isset($_POST['month']) ? $_POST['month'] : 'all';
// MongoDB aggregation for sizes and genders
$sizeCounts = [];
$genderCounts = [];

// Aggregate data by size
$sizeAggregation = [
    ['$unwind' => '$products'],
    ['$group' => [
        '_id' => '$products.size',
        'count' => ['$sum' => 1]
    ]],
    ['$sort' => ['count' => -1]]
];

$sizeResults = $ordersCollection->aggregate($sizeAggregation);

$sizes = [];
$sizeCounts = [];
foreach ($sizeResults as $result) {
    $sizes[] = $result['_id'];
    $sizeCounts[] = $result['count'];
}

// Aggregate data by gender
$genderAggregation = [
    ['$unwind' => '$products'],
    ['$lookup' => [
        'from' => 'products',
        'localField' => 'products.product_id',
        'foreignField' => 'product_id',
        'as' => 'product_info'
    ]],
    ['$unwind' => '$product_info'],
    ['$group' => [
        '_id' => '$product_info.gender',
        'count' => ['$sum' => 1]
    ]],
    ['$sort' => ['count' => -1]]
];

$genderResults = $ordersCollection->aggregate($genderAggregation);

$genders = [];
$genderCounts = [];
foreach ($genderResults as $result) {
    $genders[] = $result['_id'];
    $genderCounts[] = $result['count'];
}

// Encode the data to JSON format to pass it to JavaScript
$sizesJson = json_encode($sizes);
$sizeCountsJson = json_encode($sizeCounts);
$gendersJson = json_encode($genders);
$genderCountsJson = json_encode($genderCounts);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Insights</title>
</head>
<body>
    <?php
        include "inc/head.inc.php";
        include "inc/header.inc.php";
        include "inc/nav.inc.php";
    ?>

    <div role="main" class="row justify-content-center">
        <div class="col-md-10"> 

            <h2 class="text-center">Customer Insights</h2> 

            <div class="charts-container">
                <div class="card chart a" style="padding:20px;">
                    <h5 class="card-title">Size Order Count</h5>
                    <form method="post" action="">
                        <label for="month">Select Month:</label>
                        <select name="month" id="month" onchange="this.form.submit()">
                            <option value="all" <?= ($selectedMonth == 'all') ? 'selected' : '' ?>>All Months</option>
                            <?php foreach ($months as $value => $label): ?>
                                <option value="<?= $value ?>" <?= ($value == $selectedMonth) ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <canvas id="sizeChart" width="400" height="450"></canvas>
                </div>
                
                <!-- Gender Chart -->
                <div class="card chart b"  style="padding:20px;">
                    <h5 class="card-title">Gender Order Count</h5>
                    <canvas id="genderChart" width="400" height="450"></canvas>
                </div>

            </div>
            <div class="text-center">
                    <a href="adminpage.php" class="btn btn-secondary mr-2">Back to Admin</a>
                </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Data from PHP
        var sizes = <?php echo $sizesJson; ?>;
        var sizeCounts = <?php echo $sizeCountsJson; ?>;
        var genders = <?php echo $gendersJson; ?>;
        var genderCounts = <?php echo $genderCountsJson; ?>;

        // Get the canvas context
        var sizectx = document.getElementById('sizeChart').getContext('2d');
        var genderctx = document.getElementById('genderChart').getContext('2d');

        // Size Chart
        var sizeChart = new Chart(sizectx, {
            type: 'bar',
            data: {
                labels: sizes,
                datasets: [{
                    label: 'Number of Orders',
                    data: sizeCounts,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Gender Chart
        var genderChart = new Chart(genderctx, {
            type: 'pie',
            data: {
                labels: genders,
                datasets: [{
                    label: 'Order Proportion',
                    data: genderCounts,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.2)', 'rgba(75, 192, 192, 0.2)', 'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)', 'rgba(75, 192, 192, 1)', 'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
            }
        });

    </script>

    <?php
        $conn->close();
    ?>
</body>
</html>
