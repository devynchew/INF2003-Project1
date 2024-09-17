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
        
        
        <main class="formcontainer">
            <h1>Forgot Password</h1>
            <p>
            Please enter your registered email address:
            </p>
            <?php
            // Check if there's an error message in the session and display it
            if (isset($_SESSION['emailnotfound'])): ?>
                <p style="color: red;"><?php echo $_SESSION['emailnotfound']; ?></p>
                <?php 
                // Clear the error message after displaying it
                    unset($_SESSION['emailnotfound']); 
                ?>
            <?php endif; ?>
            <form action="process_forgotpw.php" method="post">

                <div class="mb-3">
                    <label for="email" class="form-label">Email address:</label>
                    <input required type="email" id="email" name="email" class="form-control" placeholder="Enter email">
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