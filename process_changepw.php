<?php
require_once 'session_config.php';
?>
<!DOCTYPE html>
<html lang="en">
    <body>
        <?php
            include "inc/head.inc.php";
            include "inc/header.inc.php";
            include "inc/nav.inc.php";
        ?>
        <?php

            session_start();

            $email = $_SESSION["changepw_email"];
            $confirmPwd = $pwd = $errorMsg = "";
            $success = true;

            // Password
            if (empty($_POST["pwd"])) {
                $errorMsg .= "Password is required.<br>";
                $success = false;
            } else {
                $pwd = $_POST["pwd"]; // Password will not be sanitized
                if (strlen($pwd) < 8) {
                    $errorMsg .= "Password must be at least 8 characters long.<br>";
                    $success = false;
                }
            }

            // Confirm Password
            if (empty($_POST["pwd_confirm"])) {
                $errorMsg .= "Please confirm your password.<br>";
                $success = false;
            } else {
                $confirmPwd = $_POST["pwd_confirm"]; // Confirm password will not be sanitized
                if ($pwd !== $confirmPwd) {
                    $errorMsg .= "Passwords do not match.<br>";
                    $success = false;
                }
            }

            if ($success) {
                echo "<main class='formcontainer'>";
                echo "<h4>Password Change Successful</h4>";
                echo "<p>Your password has been changed!</p>";
                echo "<button onclick=\"location.href='login.php';\" class='submitbtn'>Login</button>";
                echo "</main>";

                // Hash the password
                $pwd = password_hash($pwd, PASSWORD_DEFAULT);
                changePW();
            } else {
                echo "<main class='formcontainer'>";
                echo "<h4>Oops</h4>";
                echo "<p>The following input errors were detected:</p>";
                echo "<p>" . $errorMsg . "</p>";
                echo "<button onclick=\"location.href='changepw.php';\" class='submitbtn'>Return to Change Password</button>";
                echo "</main>";
            }

            function sanitize_input($data) {
                $data = trim($data);
                $data = stripslashes($data);
                $data = htmlspecialchars($data);
                return $data;
            }

            function changePW() {
                global $pwd, $email, $errorMsg, $success;

                // Database connection
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
                        $stmt = $conn->prepare("UPDATE members SET password=? WHERE email=?");

                        // Bind & execute the query statement:
                        $stmt->bind_param("ss", $pwd, $email);

                        if (!$stmt->execute())
                        {
                            $errorMsg = "Execute failed: (" . $stmt->errno . ") " .$stmt->error;
                            $success = false;
                        }

                        unset($_SESSION["changepw_email"]);
                        $stmt->close();
                    }
                }
            }

        ?>
        
        <?php
          include "inc/footer.inc.php";
        ?>
    </body>
</html>