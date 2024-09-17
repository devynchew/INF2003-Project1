<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'mail/Exception.php';
require 'mail/PHPMailer.php';
require 'mail/SMTP.php';

if (isset($_POST["send"])) {
    $email = $_POST["email"];
    
    // Validate email address
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mail = new PHPMailer(true);

        try {
            // SMTP configuration settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'enquiry@somethingqlo.com';
            $mail->Password = 'yngbtpspekilfjry'; // Your generated app password
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465; // Use 587 if you have set `SMTPSecure = 'tls'`

            // ... The rest of your PHPMailer setup code ...

            $mail->setFrom('enquiry@somethingqlo.com');
            $mail->addAddress($email); // Use the validated email address
            $mail->isHTML(true);
            $mail->Subject = "Welcome to Our Newsletter!";
            $mail->Body = "Welcome to somethingqlo! We will send you updates every week on the newest fashion trends"; // Corrected spelling
            
            $mail->send();
            echo "<script>alert('Sent Successfully'); document.location.href='index.php';</script>";
        } catch (Exception $e) {
            echo "<script>alert('Mailer Error: " . $mail->ErrorInfo . "'); history.go(-1);</script>";
        }
    } else {
        echo "<script>alert('Invalid email address.'); history.go(-1);</script>";
    }
}
?>
