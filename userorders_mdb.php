<?php
require_once 'session_config.php';
require 'vendor/autoload.php'; // Include Composer's autoloader for MongoDB

use Exception;
use MongoDB\Client;
use MongoDB\Driver\ServerApi;

session_start();
$fname = $_SESSION['fname'];
$email=$_SESSION['email'];

$errorMsg = false;
?>


<!DOCTYPE html>
<html lang="en">
    <body>
        <?php
            include "inc/head.inc.php";
            include "inc/header.inc.php";
            include "inc/nav.inc.php";
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
                                <li><a href="userprofile_mdb.php">My Account</a></li>
                                <li><a href="userorders_mdb.php">My Orders</a></li>
                            </ul>

                        </div>
                    </div>
                </div>
                    
                <div class="userinfo">
                    <div class="profile-input-field" style="margin:20px;">
                    <h2 class= "userprofile-header" style="font-size: 24px; font-weight: Bold; margin-bottom: 20px; text-decoration: none;">MY ORDERS</h2>
                        <p>View your order history or check the status of a recent order.</p>

                        <table id="ordertable" style="border-collapse: collapse; width:100%;">
                            <tr style="border-bottom: 1px solid #ddd;">
                                
                                <th>Order</th>
                                <th>Date</th>
                                <th>Products</th>
                                <th>Total Price</th>
                                <th></th>
                            </tr>

                            <?php
                                // Load database configuration
                                $config = parse_ini_file('/var/www/private/db-config.ini');
                                $uri = $config['mongodb_uri'];

                                // Specify Stable API version 1
                                $apiVersion = new ServerApi(ServerApi::V1);

                                // Connect to MongoDB
                                try {
                                    $client = new MongoDB\Client($uri, [], ['serverApi' => $apiVersion]);
                                    $db = $client->selectDatabase('somethingqlo');                 
                                    
                                    // Define the orders collection
                                    $ordersCollection = $db->orders;
                                    $usersCollection = $db->users;

                                    $user = $usersCollection->findOne(['email' => $email]);

                                    if ($user) {
                                        // Find the orders for the user
                                        $orders = $ordersCollection->find(['user_id' => $user['user_id']]);

                                        // Check if orders exist
                                        if ($orders->isDead()) {
                                            $errorMsg = "You have not placed any orders yet.";
                                        } else {
                                            foreach ($orders as $order) {
                                                $date = date("Y-m-d", strtotime($order["order_date"]));
                                                
                                                // Count the number of products in the order
                                                $productCount = count($order['products']);

                                                echo "<tr style='padding-top: 10px;'>";
                                                echo "<td><a href='orderdetails_mdb.php?order_id=" . $order["order_id"] . "'>" . $order["order_id"] . "</a></td>";
                                                echo "<td>" . $date . "</td>";
                                                echo "<td>" . $productCount . "</td>";
                                                echo "<td>" . $order["total_amount"] . "</td>";
                                                echo "</tr>";
                                            }
                                        }
                                    } else {
                                        $errorMsg = "User not found.";
                                    }
                                
                                } 
                                catch (Exception $e) {
                                    $errorMsg = "Connection failed: " . $e->getMessage();
                                    $success = false;
                                }
                            ?>

                        </table>

                        <?php if ($errorMsg == true): ?>
                            
                            <div style="justify-content:center; margin-top: 80px; text-align:center;">
                                <p>You have not placed any orders yet.</p>
                            </div>

                        <?php endif; ?>
    
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