<?php
session_start();

$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Check if the user is logged in and if they are an admin or superadmin
// This is a simplified check; your actual implementation might be different
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

// Flag to determine if the current user is a superadmin
$isSuperAdmin = isset($_SESSION['isSuperAdmin']) && $_SESSION['isSuperAdmin'];

// Prepare the SQL query based on user role
$sql = "SELECT user_id, fname, lname, email" . ($isSuperAdmin ? ", is_admin" : "") . " FROM users";

$result = $conn->query($sql);

// Check for errors - This is basic error checking; you might want to handle errors more gracefully
if (!$result) {
    die("Error fetching data: " . $conn->error);
}
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

        // Sample PHP data
        $data = [10, 20, 30, 40, 50];
        $labels = ["January", "February", "March", "April", "May"];

        // Convert data to JSON
        $data_json = json_encode($data);
        $labels_json = json_encode($labels);
    ?>


    <div role="main" class="row justify-content-center">
        <div class="col-md-10"> <!-- Adjusted for a maximum width -->
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
                <h2 class="text-center">Customer Insights</h2> <!-- Centered Text -->
                <div class="table-responsive"> <!-- Responsive table wrapper -->

                    <canvas id="myChart" width="400" height="200"></canvas>

                <div class="text-center">
            <a href="adminpage.php" class="btn btn-secondary mr-2">Back to Admin</a>
        </div>
    </div>
    <script>
        var ctx = document.getElementById('myChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'bar',  // You can change the chart type to 'line', 'pie', 'doughnut', etc.
            data: {
                labels: <?php echo $labels_json; ?>,
                datasets: [{
                    label: 'My Dataset',
                    data: <?php echo $data_json; ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

    <?php
        // Close the database connection
        $conn->close();
    ?>
    
</body>
</html>

