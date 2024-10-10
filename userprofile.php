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

            global $fname, $lname, $email, $pwd_hashed, $errorMsg, $success;

            $email=$_SESSION['email'];

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
                    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");

                    // Bind & execute the query statement:
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0)
                    {
                        // Note that email field is unique, so should only have
                        // one row in the result set.
                        $row = $result->fetch_assoc();
                        $fname = $row["fname"];
                        $lname = $row["lname"];
                        $address = $row["address"];
                        $pwd_hashed = $row["password"];

                    }
                    else
                    {
                        $errorMsg = "Email not found";
                    }
                    $stmt->close();
                }
                $conn->close();
            }
        ?>

        <main>

        <br>
        <div class="userprofile-container">
            <div class="userprofile">
                <div class="usermenu">
                    <div class="user-container">
                        <div class="profile-input-field" style="margin:10px;">
                            <p style="text-align: center;"> <?php echo $fname; ?> <p>

                        </div>
                    </div>
                        
                    <div class="user-container" style="margin-top: 10px;">
                        <div class="profile-input-field" id="navigation">
                            <ul>
                                <li><a href="userprofile.php">My Account</a></li>
                                <li><a href="userorders.php">My Orders</a></li>
                            </ul>

                        </div>
                    </div>
                </div>
                    
                <div class="userinfo">
                    <div class="profile-input-field" style="margin:20px;">
                        <h2 class= "userprofile-header" style="font-size: 24px; font-weight: Bold; margin-bottom: 20px; text-decoration: none;">PERSONAL INFO</h2>
                        <p>Update your personal information.</p>
                        <br>
                        <p>Login email: <?php echo $email; ?></p>

                        <form action="process_updateinfo.php" method="post" >
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" class="form-control" name="fname" style="width:20em;" placeholder="Enter your First Name" value="<?php echo $fname; ?>" required />
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" class="form-control" name="lname" style="width:20em;" placeholder="Enter your Last Name" required value="<?php echo $lname; ?>" />
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" class="form-control" name="address" style="width:20em;" placeholder="Enter your Address" required value="<?php echo $address; ?>" />
                        </div>
                        <div class="form-group">    
                            <button type='submit' class='submitbtn'>Update</button><br><br>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <br>

        </html>

        </main>

        <?php
        include "inc/footer.inc.php";
        ?>
    </body>
</html>