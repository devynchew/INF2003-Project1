<?php
require_once 'session_config.php';
?>
<!DOCTYPE html>
<html lang="en">

<?php
$title = "Login Page";
include "inc/head.inc.php";
?>

<body>
    <?php
    include "inc/header.inc.php";
    include "inc/nav.inc.php";
    ?>
    <main class="container">
        <div>
            <h1>Member Login</h1>
            <p>
                Existing Members log in here.
                For new members, please go to the
                <a href="register.php" style="color: #0056b3; text-decoration: underline;">Member Registration page</a>

            </p>
            <?php
            // Check if there's an error message in the session and display it
            if (isset($_SESSION['error'])) : ?>
                <p style="color: red;"><?php echo $_SESSION['error']; ?></p>
                <?php
                // Clear the error message after displaying it
                unset($_SESSION['error']);
                ?>
            <?php endif; ?>
            <form action="process_login.php" method="post">

                <div class="mb-3">
                    <!-- <label for="email" class="form-label">Email:</label> -->
                    <input required type="email" id="email" name="email" class="form-control" placeholder="Enter email">
                </div>

                <div class="mb-3">
                    <!-- <label for="pwd" class="form-label">Password:</label> -->
                    <input required type="password" id="pwd" name="pwd" class="form-control" placeholder="Enter password">
                </div>

                <div class="mb-3">
                    <button type="submit" class="submitbtn">Submit</button>
                </div>

                <a href="forgotpw.php" style="color: #0056b3; text-decoration: underline;">Forgot Password</a>

            </form>
        </div>
    </main>
    <?php
    include "inc/footer.inc.php";
    ?>
</body>

</html>