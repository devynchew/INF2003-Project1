<?php
session_start();
$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Check for database connection error
if ($conn->connect_error) {
    $_SESSION['error'] = "Connection failed: " . $conn->connect_error;
    header("Location: userorders.php");
    exit;
}

// Ensure the request is POST and the orderID is provided
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['orderID'])) {
    $orderID = $_POST['orderID'];

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("UPDATE orders SET orderStatus = 'Request for Refund' WHERE orderID = ?");
    $stmt->bind_param("i", $orderID);

    // Execute the statement and check if the update was successful
    if ($stmt->execute()) {
        // Check if any rows were actually updated
        if ($stmt->affected_rows > 0) {
            $_SESSION['successMsg'] = "Refund requested successfully for order ID: $orderID.";
        } else {
            $_SESSION['errorMsg'] = "Order not found or already requested for refund.";
        }
    } else {
        $_SESSION['errorMsg'] = "Error requesting refund: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
} else {
    $_SESSION['errorMsg'] = "Invalid request.";
}

// Close the database connection
$conn->close();

// Redirect back to the orders management page
header("Location: userorders.php");
exit;
?>
