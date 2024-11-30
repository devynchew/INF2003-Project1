<?php
require_once 'session_config.php';
require 'vendor/autoload.php'; // Include Composer's autoloader for MongoDB
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
    $hashed_pwd = password_hash($pwd, PASSWORD_DEFAULT);
    if (!$hashed_pwd) {
        $errorMsg .= "Cannot hash password";
        $success = false;
    }
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

function saveMemberToDB() {
    global $fname, $lname, $email, $hashed_pwd, $errorMsg, $success;


    // Load database configuration
    $config = parse_ini_file('/var/www/private/db-config.ini');
    $uri = $config['mongodb_uri'];

    // Specify Stable API version 1
    $apiVersion = new ServerApi(ServerApi::V1);

    try {
        // Connect to MongoDB
        $client = new MongoDB\Client($uri, [], ['serverApi' => $apiVersion]);
        $db = $client->selectDatabase('somethingqlo'); 
    
        // Define the users collection
        $userCollection = $db->users;

        $pipeline = [
            ['$group' => ['_id' => null, 'maxUserId' => ['$max' => '$user_id']]]
        ];
        
        $result = $userCollection->aggregate($pipeline)->toArray();

        // insert into users
        $user = $userCollection->insertOne([
            'user_id' => $result[0]['maxUserId']+1,
            'name.first' => $fname,
            'name.last' => $lname, 
            'email' => $email,
            'password' => $hashed_pwd,
            'is_admin' => false
            ]);

    } catch (MongoDB\Driver\Exception\Exception $e) {
        $errorMsg = "MongoDB connection error: " . $e->getMessage();
        $success = false;
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