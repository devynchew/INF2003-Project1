<?php
require_once 'session_config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    include "inc/head.inc.php";
    ?>
    <meta charset="UTF-8">
    <title>Register</title>
</head>


<?php
include "inc/header.inc.php";

include "inc/nav.inc.php";


// Sanitization helper function
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Initialize variables
$fname = $lname = $email = $pwd = $errorMsg = "";
$success = true;

// First Name Validation
if (empty($_POST["fname"])) {
    $errorMsg .= "First name is required.";
    $success = false;
} else {
    $fname = sanitize_input($_POST["fname"]);
}

// Last Name Validation
if (empty($_POST["lname"])) {
    $errorMsg .= "Last name is required.";
    $success = false;
} else {
    $lname = sanitize_input($_POST["lname"]);
}

// Email Validation
if (empty($_POST["email"])) {
    $errorMsg .= "Email is required.";
    $success = false;
} else {
    $email = sanitize_input($_POST["email"]);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg .= "Invalid email format.";
        $success = false;
    } else {
        $email = strtolower($email); // Convert email to lowercase
    }
}

// Password Confirmation Validation
if (empty($_POST["pwd"]) || empty($_POST["pwd_confirm"])) {
    $errorMsg .= "Password and password confirmation are required.";
    $success = false;
} else if ($_POST["pwd"] !== $_POST["pwd_confirm"]) {
    $errorMsg .= "Passwords do not match.";
    $success = false;
} else {
    $hashed_pwd = $_POST["pwd"];
    // $hashed_pwd = password_hash($pwd, PASSWORD_DEFAULT);
    // if (!$hashed_pwd) {
    //     $errorMsg .= "Cannot hash password";
    //     $success = false;
    // }
}

// Terms and Conditions Validation
if (!isset($_POST["agree"]) || $_POST["agree"] !== "on") {
    $errorMsg .= "You must agree to the terms and conditions.";
    $success = false;
}

// Final Output
if ($success) {
    saveMemberToDB();
}
//Save member to DB
function saveMemberToDB()
{
    global $fname, $lname, $email, $hashed_pwd, $errorMsg, $success;
    // Create database connection.
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        $errorMsg = "Failed to read database config file.";
        $success = false;
    } else {
        $conn = new mysqli(
            $config['servername'],
            $config['username'],
            $config['password'],
            $config['dbname']
        );
        // Check connection
        if ($conn->connect_error) {
            $errorMsg = "Connection failed: " . $conn->connect_error;
            $success = false;
        } else {
            // Prepare the statement:
            $stmt = $conn->prepare("INSERT INTO users (fname, lname, email, password) VALUES (?, ?, ?, ?)");
            // Bind & execute the query statement:
            $stmt->bind_param("ssss", $fname, $lname, $email, $hashed_pwd);
            if (!$stmt->execute()) {
                $errorMsg = "Execute failed: (" . $stmt->errno . ") " .
                    $stmt->error;
                $success = false;
            }
            $stmt->close();
        }
        $conn->close();
    }
}
?>

<body>
    <div class="content-wrapper">
        <div class="container">
            <div class="text-center">

                <?php if ($success) : ?>
                    <div class='success-message'>
                        <h3>Your registration is successful.</h3>
                        <h5>Thank you for signing up, <?php echo htmlspecialchars($fname) . " " . htmlspecialchars($lname); ?>!<br>
                            Kindly return to the Login page to log in.</h5>
                        <div class='mb-3'>
                            <a href='login.php' class='btn btn-success'>Login</a>
                        </div>
                    </div>
                <?php else : ?>
                    <div class='error-message'>
                        <h4>Oops</h4>
                        <h4>The following input errors were detected:</h4>
                        <p><?php echo htmlspecialchars($errorMsg); ?></p>
                        <div class='mb-3'>
                            <a href='register.php' class='btn btn-danger'>Return to Sign Up</a>
                        </div>
                    </div>
                <?php endif; ?>


            </div>
        </div>
    </div>

    <?php
    include "inc/footer.inc.php";
    ?>
</body>

</html>