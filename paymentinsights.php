<?php
session_start();

$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Check if the user is logged in and if they are an admin or superadmin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

// Flag to determine if the current user is a superadmin
$isSuperAdmin = isset($_SESSION['isSuperAdmin']) && $_SESSION['isSuperAdmin'];

// Prepare the SQL query to fetch payment method summary
$sql = "SELECT t.payment_method, COUNT(t.transaction_id) AS transaction_count, SUM(o.total_amount) AS total_amount
        FROM transactions t
        JOIN orders o ON t.order_id = o.order_id";

// Filter by payment method if selected
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['payment_method'])) {
    $payment_method = $_POST['payment_method'];
    
    if ($payment_method != 'all') {
        $sql .= " WHERE t.payment_method = '" . $conn->real_escape_string($payment_method) . "'";
    }
}

// Group by payment method for all cases
$sql .= " GROUP BY t.payment_method";
$result = $conn->query($sql);

// Check for errors
if (!$result) {
    die("Error fetching data: " . $conn->error);
}

// Fetch summary data for the table and chart
$labels = [];
$data = [];
$table_rows = "";

while ($row = $result->fetch_assoc()) {
    $labels[] = $row['payment_method'];
    $data[] = $row['total_amount'];
    
    // Populate the main summary table rows
    $table_rows .= "<tr>
                        <td>{$row['payment_method']}</td>
                        <td>{$row['transaction_count']}</td>
                        <td>\${$row['total_amount']}</td>
                        <td>
                            <form method='POST' action=''>
                                <input type='hidden' name='view_details' value='{$row['payment_method']}'>
                                <button type='submit' class='btn btn-info'>View Details</button>
                            </form>
                        </td>
                    </tr>";
}

// Convert PHP arrays to JSON for use in the chart
$labels_json = json_encode($labels);
$data_json = json_encode($data);

// If 'View Details' is clicked, fetch transaction details for the selected payment method
$transaction_details = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['view_details'])) {
    $payment_method = $_POST['view_details'];

    // Prepare the SQL statement
    $details_sql = "SELECT u.email, CONCAT(u.fname, ' ', u.lname) AS full_name, 
                            t.transaction_id, o.order_id, o.total_amount, o.order_date
                    FROM transactions t
                    JOIN orders o ON t.order_id = o.order_id
                    JOIN users u ON o.user_id = u.user_id
                    WHERE t.payment_method = ?";

    // Initialize a prepared statement
    $details_stmt = $conn->prepare($details_sql);

    // Check if the statement was prepared successfully
    if ($details_stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind the parameter
    $details_stmt->bind_param("s", $payment_method);

    // Execute the statement
    $details_stmt->execute();

    // Get the result
    $details_result = $details_stmt->get_result();

    if ($details_result && $details_result->num_rows > 0) {
        // Create the transaction details table
        $transaction_details = "<h3 class='text-center mt-4'>Transaction Details for Payment Method: {$payment_method}</h3>
                                <table class='table table-striped table-bordered'>
                                    <thead>
                                        <tr>
                                            <th>User Email</th>
                                            <th>Name</th>
                                            <th>Transaction ID</th>
                                            <th>Order ID</th>
                                            <th>Total Amount</th>
                                            <th>Order Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>";

        while ($detail_row = $details_result->fetch_assoc()) {
            $transaction_details .= "<tr>
                                        <td>{$detail_row['email']}</td>
                                        <td>{$detail_row['full_name']}</td>
                                        <td>{$detail_row['transaction_id']}</td>
                                        <td>{$detail_row['order_id']}</td>
                                        <td>\${$detail_row['total_amount']}</td>
                                        <td>{$detail_row['order_date']}</td>
                                    </tr>";
        }
        $transaction_details .= "</tbody></table>";
    } else {
        $transaction_details = "<p>No transaction details found for the selected payment method.</p>";
    }

    // Close the prepared statement
    $details_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Insights</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

            <h2 class="text-center">Payment Method Insights</h2>

            <!-- Dropdown filter form -->
            <form method="POST" action="">
                <div class="text-center">
                    <label for="dropdown1">Payment Method:</label>
                    <select id="dropdown1" name="payment_method">
                        <option value="all">All</option>
                        <option value="Visa">Visa</option>
                        <option value="Mastercard">Mastercard</option>
                        <option value="American Express">American Express</option>
                    </select>

                    <input type="submit" value="Filter" class="btn btn-primary">
                </div>
            </form>

            <!-- Summary Table with Details Button -->
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

            <!-- Display the chart -->
            <div class="table-responsive mt-4">
                <canvas id="myChart" width="300" height="150"></canvas>
            </div>

            <!-- Display the transaction details if a payment method was selected -->
            <?= $transaction_details; ?>

            <a href="adminpage.php" class="btn btn-secondary mr-2">Back to Admin</a>
        </div>
    </div>

    <!-- Chart JS Script -->
    <script>
        var ctx = document.getElementById('myChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: <?php echo $labels_json; ?>,
                datasets: [{
                    label: 'Total Amount',
                    data: <?php echo $data_json; ?>,
                    backgroundColor: ['rgba(75, 192, 192, 0.2)','rgba(255, 99, 132, 0.2)','rgba(153, 102, 255, 0.2)'],
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });
    </script>

    <?php
    // Close the database connection
    $conn->close();
    ?>
</body>
</html>
