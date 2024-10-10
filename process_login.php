<?php
require_once 'session_config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Sanitization helper function
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Initialize variables
$email = $pwd = $errorMsg = "";
$success = true;

// Email Validation
if (empty($_POST["email"])) {
    $errorMsg .= "Email is required.<br>";
    $success = false;
} else {
    $email = sanitize_input($_POST["email"]);
    $email = strtolower($email);  // Convert email to lowercase
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg .= "Invalid email format.<br>";
        $success = false;
    }
}

// Password Validation
if (empty($_POST["pwd"])) {
    $errorMsg .= "Password is required.<br>";
    $success = false;
} else {
    $pwd = $_POST["pwd"];
}

// Attempt to authenticate the user if there are no prior input validation errors
if ($success) {
    authenticateUser($email, $pwd);
}

// Final Output based on authentication result
if ($success) {
    header('Location: index.php');
    exit;
} else {
    $_SESSION['error'] = "Email/Password is incorrect"; // Use a generic error message for security
    header('Location: login.php');
    exit;
}

unset($_SESSION['error']);

/*
* Helper function to authenticate the login.
*/
function authenticateUser($email, $pwd)
{
    global $errorMsg, $success, $fname, $lname;

    // Load database configuration
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        $errorMsg = "Failed to read database config file.";
        $success = false;
        return;
    }

    // Create database connection
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    // Check connection
    if ($conn->connect_error) {
        $errorMsg = "Connection failed: " . $conn->connect_error;
        $success = false;
        return;
    }

    // Prepare and execute the statement
    $stmt = $conn->prepare("SELECT user_id, password, fname, lname, address, is_admin FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row["user_id"];
        $fname = $row["fname"];
        $lname = $row["lname"];
        if ($pwd === $row["password"]) {
            // Password matches, authentication successful
            $success = true;
            // Set session variables
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['fname'] = $fname;
            $_SESSION['lname'] = $lname;
            $_SESSION['email'] = $email;
            $_SESSION['is_admin'] = $row["is_admin"];
            echo 'success login, session set';
        } else {
            // Invalid password
            $errorMsg = "Email not found or password doesn't match...";
            $success = false;
        }
    } else {
        // Email not found
        $errorMsg = "Email not found or password doesn't match...";
        $success = false;
    }

    $stmt->close();
    $conn->close();
} ?>


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