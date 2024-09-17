<?php
session_start();
$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['orderID'])) {
    $orderID = $_POST['orderID'];
    $sql = "DELETE FROM orders WHERE orderID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderID);
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Order deleted successfully.';
    } else {
        $_SESSION['errorMsg'] = 'Error deleting order: ' . $conn->error;
    }

    $stmt->close();
} else {
    $_SESSION['errorMsg'] = 'Invalid request.';
}

$conn->close();
header("Location: manageorders.php");
exit;
?>
