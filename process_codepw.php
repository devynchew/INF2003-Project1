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

            $code = $email = $errorMsg = "";
            $success = false;

            checkCode();

            if($success)
            {
                echo "<main class='formcontainer'>";
                echo "<h4>Code is correct.</h4>";
                echo "<p>CLick the button to change to your new password.</p>";
                echo "<button onclick=\"location.href='changepw.php';\" class='submitbtn'>Change Password</button>";
                echo "</main>";
            }
            else 
            {
                echo "<main class='formcontainer'>";
                echo "<h4>Reset code is wrong or expired.</h4>";
                echo "<p>Please try again.</p>";
                echo "<button onclick=\"location.href='codepw.php';\" class='submitbtn'>Return to Reset Password</button>";
                echo "</main>";
            }

            function checkCode()
            {
                global $code, $email, $errorMsg, $success;
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
                        $stmt = $conn->prepare("SELECT * FROM members WHERE code=? AND updated_date >= NOW() - Interval 1 DAY");

                        $code = $_POST["code"];

                        // Bind & execute the query statement:
                        $stmt->bind_param("s", $code);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0)
                        {
                            $row = $result->fetch_assoc();
                            $email = $row["email"];

                            $_SESSION['changepw_email'] = $email;

                            $success = true;
                        }
                        else
                        {
                            $errorMsg = true;
                            $success = false;
                        }
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