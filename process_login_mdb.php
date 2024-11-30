<?php
require_once 'session_config.php';
require 'vendor/autoload.php'; // Include Composer's autoloader for MongoDB

use Exception;
use MongoDB\Client;
use MongoDB\Driver\ServerApi;

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
    $pwd = $_POST['pwd'];

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
    $_SESSION['error'] = $pwd;
    $_SESSION['error'] = "Email/Password is incorrect"; // Use a generic error message for security
    header('Location: login_mdb.php');
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
    $uri = $config['mongodb_uri'];

    // Specify Stable API version 1
    $apiVersion = new ServerApi(ServerApi::V1);

    // Connect to MongoDB
    try {
        $client = new MongoDB\Client($uri, [], ['serverApi' => $apiVersion]);
        $db = $client->selectDatabase('somethingqlo'); 

        // Define the user collection
        $userCollection = $db->users;

        // Query for the user by email
        $user = $userCollection->findOne(['email' => $email]);

        if ($user) {           
            $fname = $user['name']['first'];
            $lname = $user['name']['last'];

            if (password_verify($pwd, $user['password'])) {
                // Password matches, authentication successful
                $success = true;
                // Set session variables
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = (string)$user['_id']; // MongoDB _id is an object, so cast it to string
                $_SESSION['fname'] = $fname;
                $_SESSION['lname'] = $lname;
                $_SESSION['email'] = $email;
                $_SESSION['address'] = $user['address'];
                $_SESSION['is_admin'] = $user['is_admin'];
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

    } catch (MongoDB\Driver\Exception\Exception $e) {
        $errorMsg = "MongoDB connection error: " . $e->getMessage();
        $success = false;
    }
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
                            <a href='login_mdb.php' class='btn btn-success'>Login</a>
                        </div>
                    </div>
                <?php else : ?>
                    <div class='error-message'>
                        <h4>Oops</h4>
                        <h4>The following input errors were detected:</h4>
                        <p><?php echo htmlspecialchars($errorMsg); ?></p>
                        <div class='mb-3'>
                            <a href='register_mdb.php' class='btn btn-danger'>Return to Sign Up</a>
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