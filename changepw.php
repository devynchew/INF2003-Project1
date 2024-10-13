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
            $email = $_SESSION['changepw_email'];
        ?>
        
        <main class="formcontainer">
            <h1>Change Password</h1>
            <p>
            Please enter your new password for the email: <?php echo $email ?>
            </p>
            
            <form action="process_changepw.php" method="post">

                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input required type="email" id="email" name="email" class="form-control" placeholder="Enter email">
                </div>

                <div class="mb-3">
                    <label for="pwd" class="form-label">New Password:</label>
                    <input required type="password" id="pwd" name="pwd" class="form-control" placeholder="Enter password">
                </div>

                <div class="mb-3">
                    <label for="confirm_pwd" class="form-label">Confirm New Password:</label>
                    <input required type="password" id="confirm_pwd" name="pwd_confirm" class="form-control" placeholder="Enter password">
                </div>

                <div class="mb-3">
                    <button type="submit" class="submitbtn">Submit </button>
                </div>

            </form>
        </main>
        <?php
            include "inc/footer.inc.php";
        ?>
    </body>
</html>