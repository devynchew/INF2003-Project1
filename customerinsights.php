<?php
session_start();

$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Check if the user is logged in and if they are an admin or superadmin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

$isSuperAdmin = isset($_SESSION['isSuperAdmin']) && $_SESSION['isSuperAdmin'];

$sql = "SELECT user_id, fname, lname, email" . ($isSuperAdmin ? ", is_admin" : "") . " FROM users";
$result = $conn->query($sql);
if (!$result) {
    die("Error fetching data: " . $conn->error);
}

// Dropdown for month
$months = [
    "01" => "January", "02" => "February", "03" => "March", 
    "04" => "April", "05" => "May", "06" => "June", 
    "07" => "July", "08" => "August", "09" => "September", 
    "10" => "October", "11" => "November", "12" => "December"
];

$selectedMonth = isset($_POST['month']) ? $_POST['month'] : 'all';

if ($selectedMonth == 'all') {
    // SQL query for all months
    $sizesql = "SELECT s.name, COUNT(s.name) AS order_count
                 FROM ordersproduct o
                 JOIN sizes s ON o.size_id = s.size_id
                 GROUP BY s.name;";
} else {
    // SQL query for selected month
    $sizesql = "SELECT s.name, COUNT(s.name) AS order_count
                 FROM orders o
                 JOIN ordersproduct op ON o.order_id = op.order_id
                 JOIN sizes s ON op.size_id = s.size_id
                 WHERE MONTH(o.order_date) = ?  
                 GROUP BY s.name;";
}

$stmt = $conn->prepare($sizesql);
if ($selectedMonth != 'all') {
    $stmt->bind_param("s", $selectedMonth);  
}
$stmt->execute();
$sizeresult = $stmt->get_result();

$sizes = [];
$sizeCounts = [];

if ($sizeresult->num_rows > 0) {
    while ($row = $sizeresult->fetch_assoc()) {
        $sizes[] = $row['name'];
        $sizeCounts[] = $row['order_count'];
    }
}

// SQL query for gender
$genderSql = "SELECT p.gender, COUNT(p.gender) AS order_count
              FROM ordersproduct o
              JOIN products p ON o.product_id = p.product_id
              GROUP BY p.gender;";
$genderResult = $conn->query($genderSql);

$genders = [];
$genderCounts = [];

if ($genderResult->num_rows > 0) {
    while ($row = $genderResult->fetch_assoc()) {
        $genders[] = $row['gender'];
        $genderCounts[] = $row['order_count'];
    }
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
