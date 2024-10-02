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
    $_SESSION['errormsg'] = "Connection failed: " . $conn->connect_error;
    header("Location: manageuser.php");
    exit;
}

$isSuperAdmin = isset($_SESSION['isSuperAdmin']) && $_SESSION['isSuperAdmin'];

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
    // Sanitize the input to prevent XSS attacks
    $memberId = sanitize_input($_POST['user_id']);

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");

    // Bind the parameter to the statement
    $stmt->bind_param("i", $memberId);

    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        // Check if a row was actually deleted
        if ($stmt->affected_rows > 0) {
            $_SESSION['successMsg'] = "User deleted successfully.";
        } else {
            // No row was deleted, possibly because the user_id was not found
            $_SESSION['errorMsg'] = "User not found or already deleted.";
        }
    } else {
        // Handle errors during the execution of the statement
        $_SESSION['errorMsg'] = "Error deleting user: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
} else {
    // Handle cases where the necessary data was not posted
    $_SESSION['errorMsg'] = "Invalid request.";
}

// Close the database connection
$conn->close();

// Redirect back to the manage user page
header("Location: manageuser.php");
exit;
?>