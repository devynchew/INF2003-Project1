<?php
require_once 'session_config.php';
?>
<!DOCTYPE html>
<html lang="en">
    <body>
        <?php
            include "inc/head.inc.php";
        ?>
        <?php
            session_start();
            $email = $_POST['email'];

            //Import PHPMailer classes into the global namespace
            //These must be at the top of your script, not inside a function
            use PHPMailer\PHPMailer\PHPMailer;
            use PHPMailer\PHPMailer\SMTP;
            use PHPMailer\PHPMailer\Exception;

            require 'mail/Exception.php';
            require 'mail/PHPMailer.php';
            require 'mail/SMTP.php';

            //Create an instance; passing `true` enables exceptions
            $mail = new PHPMailer(true);

            try {
                //Server settings
                $mail->SMTPDebug = false;                      //Enable verbose debug output
                $mail->isSMTP();                                            //Send using SMTP
                $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                $mail->Username   = 'inf1005.grp9@gmail.com';                     //SMTP username
                $mail->Password   = 'eskslzdcfbqxsjow';                               //SMTP APP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
                $mail->Port       = 465;                             //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

                //Recipients
                $mail->setFrom('inf1005.grp9@gmail.com', 'Admin');
                $mail->addAddress($email);     //Add a recipient

                $code = substr(str_shuffle('1234567890QWERTYUIOPASDFGHJKLZXCVBNM'),0,10);

                //Content
                $mail->isHTML(true);                                  //Set email format to HTML
                $mail->Subject = 'Password Reset';
                $mail->Body    = 'Here is your reset password code '.$code.', please do not share it with anyone. </br>Reset your password in a day.';

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
                        $stmt = $conn->prepare("SELECT * FROM members WHERE email=?");

                        // Bind & execute the query statement:
                        $stmt->bind_param("s", $email);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        // Email found
                        if ($result->num_rows > 0) 
                        {

                            $updatestmt = $conn->prepare("UPDATE members SET code=?, updated_date=? WHERE email=?");
                            

                            // Bind & execute the query statement:
                            $updatestmt->bind_param("sss", $code,date("Y-m-d h:i:s"),$email);

                            if (!$updatestmt->execute())
                            {
                                $errorMsg = "Execute failed: (" . $updatestmt->errno . ") " .$updatestmt->error;
                                $success = false;
                            }
                            
                            $mail->send();
                            header("Location: codepw.php");

                            $updatestmt->close();

                            
                        } 
                        else {
                            // Email not found
                            $errorMsg = "Email not found...";
                            $success = false;

                            $_SESSION["emailnotfound"] = "Email not found in system.";
                            header("Location: forgotpw.php");
                            
                        }
                        
                        $stmt->close();
                    }
                }
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }

        ?>

        
    </body>
</html>