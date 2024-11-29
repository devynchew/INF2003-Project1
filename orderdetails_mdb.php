<?php
require_once 'session_config.php';
require 'vendor/autoload.php'; // Include Composer's autoloader for MongoDB

use Exception;
use MongoDB\Client;
use MongoDB\Driver\ServerApi;

session_start();
$fname = $_SESSION['fname'];
$email=$_SESSION['email'];
$order_id = $_GET['order_id'];
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
                                <li><a href="userprofile.php">My Account</a></li>
                                <li><a href="userorders.php">My Orders</a></li>
                            </ul>

                        </div>
                    </div>
                </div>
                    
                <div class="userinfo">
                    <div class="profile-input-field" style="margin:20px;">
                        <h4 class="userprofile-header">Order #<?php echo $order_id ?></h4>
                        <p>View your order details.</p>

                        <table id="ordertable" style="border-collapse: collapse; width:100%;">
                            <tr style="border-bottom: 1px solid #ddd;">
                                
                                <th>Product ID</th>
                                <th>Name</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Price</th>
                                <th>Quantity</th>
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
                                    $productsCollection = $db->products;

                                    $order = $ordersCollection->findone(['order_id' => (int)$order_id]);
                                    
                                    if (!$order) {
                                        $errorMsg = "Order not found!";
                                        $success = false;
                                    } else {
                                        $products = $order['products']; 

                                        foreach ($products as $product) {
                                            $product_id = (int)$product['product_id'];
                                            $quantity = (int)$product['quantity'];


                                            $productDetails = $productsCollection->findOne(['product_id' => $product['product_id']]);

                                            echo "<tr style='padding-top: 10px;'>";
                                            echo "<td><a href='product_details.php?id=".$product_id."'>".$product_id."</a></td>";
                                            echo "<td>".$product["name"]."</td>";
                                            echo "<td>".$product["color"]."</td>";
                                            echo "<td>".$product["size"]."</td>";
                                            echo "<td>".$productDetails["price"]."</td>";
                                            echo "<td>".$quantity."</td>";
                                            echo "</tr>";
                                        }
                                    }
                                
                                } 
                                catch (Exception $e) {
                                    $errorMsg = "Connection failed: " . $e->getMessage();
                                    $success = false;
                                }
                            ?>

                        </table>
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