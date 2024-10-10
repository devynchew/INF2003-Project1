<?php
require_once 'session_config.php';
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
                echo "<button onclick=\"location.href='userprofile.php';\" class='submitbtn'>Return to user profile</button>";
                echo "</main>";

                updateMemberToDB();
            } else {
                echo "<main class='formcontainer'>";
                echo "<h4>Oops</h4>";
                echo "<p>The following input errors were detected:</p>";
                echo "<p>" . $errorMsg . "</p>";
                echo "<button onclick=\"location.href='userprofile.php';\" class='submitbtn'>Return to user profile</button>";
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

                // Create database connection.
                $config = parse_ini_file('/var/www/private/db-config.ini');
                if (!$config)
                {
                    $errorMsg = "Failed to read database config file.";
                    $success = false;
                }
                else
                {
                    $conn = new mysqli(
                        $config['servername'],
                        $config['username'],
                        $config['password'],
                        $config['dbname']
                    );
                    // Check connection
                    if ($conn->connect_error)
                    {
                        $errorMsg = "Connection failed: " . $conn->connect_error;
                        $success = false;
                    }
                    else
                    {
                        // Prepare the statement:
                        $stmt = $conn->prepare("UPDATE users SET fname=? , lname=? , address=? WHERE email=?");

                        // Bind & execute the query statement:
                        $stmt->bind_param("ssss", $fname, $lname, $address, $email);

                        $_SESSION['fname'] = $fname;

                        if (!$stmt->execute())
                        {
                            $errorMsg = "Execute failed: (" . $stmt->errno . ") " .$stmt->error;
                            $success = false;
                        }
                        $stmt->close();
                    }
                    $conn->close();
                }
            }
        ?>

        <?php
          include "inc/footer.inc.php";
        ?>
    </body>
</html>