<?php
require_once 'session_config.php';
require 'vendor/autoload.php'; // Include Composer's autoloader for MongoDB

use Exception;
use MongoDB\Client;
use MongoDB\Driver\ServerApi;
?>

<!DOCTYPE html>
<html lang="en">
    <?php
        include "inc/head.inc.php";
    ?>
    <body>
        <?php
            include "inc/header.inc.php";
        ?> 
        <?php
            include "inc/nav.inc.php";
        ?>
        <?php
            session_start();
                    
            ini_set('display_errors', 1); 
            ini_set('display_startup_errors', 1); 
            error_reporting(E_ALL);
            
            $email=$_SESSION['email'];
            $fname = $lname = $address = $errorMsg = "";
            $success = true;

            // First Name
            if (empty($_POST["fname"])) {
                $errorMsg .= "First name is required.<br>";
                $success = false;
            } else {
                $fname = sanitize_input($_POST["fname"]);
            }

            // Last Name
            if (empty($_POST["lname"])) {
                $errorMsg .= "Last name is required.<br>";
                $success = false;
            } else {
                $lname = sanitize_input($_POST["lname"]);
            }

            // Address
            if (empty($_POST["address"])) {
                $errorMsg .= "Address is required.<br>";
                $success = false;
            } else {
                $address = sanitize_input($_POST["address"]);
            }

            if ($success) {
                echo "<main class='formcontainer'>";
                echo "<h4>Your update is successful!</h4>";
                echo "<p>Your information has been updated ". $fname. " " . $lname . "</p>";
                echo "<button onclick=\"location.href='userprofile_mdb.php';\" class='submitbtn'>Return to user profile</button>";
                echo "</main>";

                updateMemberToDB();
            } else {
                echo "<main class='formcontainer'>";
                echo "<h4>Oops</h4>";
                echo "<p>The following input errors were detected:</p>";
                echo "<p>" . $errorMsg . "</p>";
                echo "<button onclick=\"location.href='userprofile_mdb.php';\" class='submitbtn'>Return to user profile</button>";
                echo "</main>";
            }

            function sanitize_input($data) {
                $data = trim($data);
                $data = stripslashes($data);
                $data = htmlspecialchars($data);
                return $data;
            }

            /*
            * Helper function to write the member data to the database.
            */
            function updateMemberToDB()
            {
                global $fname, $lname, $address, $email, $errorMsg, $success;

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

                    $user = $userCollection->findOne(['email' => $email]);

                    if($user)
                    {
                        $update = $userCollection->updateOne(
                            ['email' => $email], 
                            ['$set' => [
                                'name.first' => $fname, 
                                'name.last' => $lname,  
                                'address' => $address 
                            ]]
                        );

                        // if ($update->getModifiedCount() > 0) {
                        //     echo "<p>Your information has been updated successfully!</p>";
                        // } else {
                        //     echo "<p>Something went wrong while updating your information.</p>";
                        // }
                    }
                    else
                    {
                        echo "<p>User not found.</p>";
                    }

                } catch (MongoDB\Driver\Exception\Exception $e) {
                    $errorMsg = "MongoDB connection error: " . $e->getMessage();
                    $success = false;
                }
            }
        ?>

        <?php
          include "inc/footer.inc.php";
        ?>
    </body>
</html>