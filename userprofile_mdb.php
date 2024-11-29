<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
require_once 'session_config.php';
require 'vendor/autoload.php'; // Include Composer's autoloader for MongoDB

use Exception;
use MongoDB\Client;
use MongoDB\Driver\ServerApi;
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

            global $fname, $lname, $email, $pwd_hashed, $errorMsg, $success;

            $email=$_SESSION['email'];

            // Load database configuration
            $config = parse_ini_file('/var/www/private/db-config.ini');
            $uri = $config['mongodb_uri'];

            // Specify Stable API version 1
            $apiVersion = new ServerApi(ServerApi::V1);

            // Connect to MongoDB
            try {
                $client = new MongoDB\Client($uri, [], ['serverApi' => $apiVersion]);
                $db = $client->selectDatabase('somethingqlo');                 
                
                // Define the user collection
                $usersCollection = $db->users;
            } 
            catch (Exception $e) {
                $errorMsg = "Connection failed: " . $e->getMessage();
                $success = false;
            }

            if (isset($email)) {
                // Query MongoDB collection for the user
                $user = $usersCollection->findOne(['email' => $email]);

                if ($user) {
                    $fname = $user['fname'];
                    $lname = $user['lname'];
                    $address = $user['address'];
                    $pwd_hashed = $user['password'];
                } else {
                    $errorMsg = "Email not found";
                }
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