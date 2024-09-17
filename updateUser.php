<?php
session_start();

if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != 1) {
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['member_id'])) {
    $memberId = sanitize_input($_POST['member_id']);
    $fname = sanitize_input($_POST['fname']);
    $lname = sanitize_input($_POST['lname']);
    $email = sanitize_input($_POST['email']);
    $isAdmin = ($isSuperAdmin && isset($_POST['isAdmin'])) ? 1 : 0; // Assuming checkbox sends on value if checked

    if ($isSuperAdmin) {
        $sql = "UPDATE members SET fname = ?, lname = ?, email = ?, isAdmin = ? WHERE member_id = ?";
    } else {
        $sql = "UPDATE members SET fname = ?, lname = ?, email = ? WHERE member_id = ?";
    }

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $_SESSION['errorMsg'] = "Failed to prepare statement. Error: " . $conn->error;
        header("Location: manageuser.php");
        exit;
    }

    if ($isSuperAdmin) {
        $stmt->bind_param("sssii", $fname, $lname, $email, $isAdmin, $memberId);
    } else {
        $stmt->bind_param("sssi", $fname, $lname, $email, $memberId);
    }

    if ($stmt->execute()) {
        unset($_SESSION['errorMsg']);
        $_SESSION['successMsg']= "Success!";
    } else {
        $_SESSION['errorMsg'] = "Failed to update user. Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    $_SESSION['errorMsg'] = "Invalid request.";
}

$conn->close();
header("Location: manageuser.php");
exit;
?>
