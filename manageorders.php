<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Check for database connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT o.order_id, u.email, o.order_date, COUNT(p.quantity) AS quantity, o.total_amount
FROM orders o
JOIN users u ON o.user_id = u.user_id
JOIN ordersproduct p ON o.order_id = p.order_id
GROUP BY o.order_id
";
$result = $conn->query($sql);
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
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['order_id']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= date("F j, Y", strtotime($row['order_date'])) ?></td>
                            <td><?= htmlspecialchars($row['quantity']) ?></td>
                            <td><?= htmlspecialchars($row['total_amount']) ?></td>
                            <td>
                                <?php if ($row['orderStatus'] == 'Request for Refund'): ?>
                                    <form action="approveRefund.php" method="post" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                    <?php endif; ?>
                    <form action="deleteOrder.php" method="post" style="display: inline;">
                        <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this order?');">Delete</button>
                    </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
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

<?php $conn->close(); ?>
