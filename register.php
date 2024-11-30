<?php
require_once 'session_config.php';
?>
<!DOCTYPE html>
<html lang="en">

<?php
$title = "Register Page";
include "inc/head.inc.php";
?>

<body>
    <?php
    include "inc/header.inc.php";
    include "inc/nav.inc.php";
    ?>
    <main class="container">
        <h1>Member Registration</h1>
        <p>
            For existing members, please go to the
            <a href="login.php">Sign In page</a>.
        </p>
        <form action="process_register_mdb.php" method="post">
            <div class="mb-3">
                <label for="fname" class="form-label">First Name:</label>
                <input required maxlength="45" type="text" id="fname" name="fname" class="form-control" placeholder="Enter first name">
            </div>

            <div class="mb-3">
                <label for="lname" class="form-label">Last Name:</label>
                <input required maxlength="45" type="text" id="lname" name="lname" class="form-control" placeholder="Enter last name">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input required maxlength="45" type="email" id="email" name="email" class="form-control" placeholder="Enter email">
            </div>

            <div class="mb-3">
                <label for="pwd" class="form-label">Password:</label>
                <input required maxlength="45" type="password" id="pwd" name="pwd" class="form-control" placeholder="Enter password" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}">
            </div>
            <div class="mb-3">
                <label for="pwd_confirm" class="form-label">Confirm Password:</label>
                <input required maxlength="45" type="password" id="pwd_confirm" name="pwd_confirm" class="form-control" placeholder="Re-enter password" title="Must match the new password and follow the required pattern" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}">
                <span id="pwdMsg"></span>
            </div>
            <input type="checkbox" onclick="togglePassword()"> Show Password

            <div class="mb-3 form-check">
                <input required type="checkbox" name="agree" id="agree" class="form-check-input">
                <label class="form-check-label" for="agree">Agree to terms and conditions.</label>
            </div>

            <div class="mb-3">
                <button type="submit" class="submitbtn">Submit</button>
            </div>
        </form>
    </main>
    <?php
    include "inc/footer.inc.php";
    ?>

    <script>
        // Function to validate that passwords match
        function validatePassword() {
            var passwordInput = document.getElementById("pwd");
            var confirmPasswordInput = document.getElementById("pwd_confirm");
            var message = document.getElementById("pwdMsg");

            // Check if the password meets the required pattern
            if (!passwordInput.value.match(passwordInput.pattern)) {
                message.textContent = "Password must contain at least one number, one uppercase and lowercase letter, and at least 8 or more characters.";
                message.style.color = "red";
            } else {
                message.textContent = ""; // Clear message if the pattern is correct
            }

            // Check if passwords match only if the pattern is correct
            if (passwordInput.value.match(passwordInput.pattern) && passwordInput.value !== confirmPasswordInput.value) {
                message.textContent = "Passwords do not match.";
                message.style.color = "red";
            }
        }

        // Event listeners for password fields
        document.getElementById("pwd").onkeyup = validatePassword;
        document.getElementById("pwd_confirm").onkeyup = validatePassword;

        document.getElementById("pwd").oninput = function() {
            var pattern = this.pattern;
            var message = document.getElementById("pwdMsg");

            if (!this.value.match(pattern)) {
                // Set message for invalid input
                message.textContent = "Password must contain at least one number, one uppercase and lowercase letter, and at least 8 or more characters.";
                message.style.color = "red";
            } else {
                // Clear message when input is valid
                message.textContent = "";
            }
        };

        // Toggle password visibility
        function togglePassword() {
            var password = document.getElementById("pwd");
            var confirmPassword = document.getElementById("pwd_confirm");

            if (password.type === "password") {
                password.type = "text";
                confirmPassword.type = "text";
            } else {
                password.type = "password";
                confirmPassword.type = "password";
            }
        }
    </script>
</body>

</html>