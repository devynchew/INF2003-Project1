<?php
session_start();
$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['orderID'])) {
    $orderID = $_POST['orderID'];
    $sql = "UPDATE orders SET orderStatus = 'Refunded' WHERE orderID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderID);
    if ($stmt->execute()) {
        // Optionally set a session variable to show a success message on the next page
        $_SESSION['message'] = 'Order refunded successfully.';
    } else {
        // Error handling
        $_SESSION['error'] = 'Error updating record: ' . $conn->error;
    }

    $stmt->close();
} else {
    // Handle invalid access or missing orderID
    $_SESSION['error'] = 'Invalid request.';
}

$conn->close();
header("Location: manageorders.php");
exit;
?>
