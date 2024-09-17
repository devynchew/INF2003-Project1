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

        ?>
        <main class="formcontainer">
            <h1>Reset Password Code</h1>
            <p>
            An code has been sent to your email, please check your email and enter the reset code.
            </p>
            <form action="process_codepw.php" method="post">

                <div class="mb-3">
                    <label for="email" class="form-label">Code:</label>
                    <input required type="text" id="code" name="code" class="form-control" placeholder="Enter code">
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