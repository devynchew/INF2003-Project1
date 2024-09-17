<?php
require_once 'session_config.php';
session_start();
$fname = $_SESSION['fname'];
$email=$_SESSION['email'];
$orderID = $_GET['orderID'];
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
                        <h4 class="userprofile-header">Order #<?php echo $orderID ?></h4>
                        <p>View your order details.</p>

                        <table id="ordertable" style="border-collapse: collapse; width:100%;">
                            <tr style="border-bottom: 1px solid #ddd;">
                                
                                <th>Product ID</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Information</th>
                                <th>Quantity</th>
                            </tr>

                            <?php
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
                                        // Prepare the statement to get all products from order
                                        $stmt = $conn->prepare("SELECT * FROM order_items WHERE orderID=?");

                                        // Bind & execute the query statement:
                                        $stmt->bind_param("s", $orderID);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $rowcount = mysqli_num_rows($result);
                            
                                        while ($row = $result->fetch_assoc())
                                        {
                                            // Prepare the statement to get all product
                                            $stmt1 = $conn->prepare("SELECT * FROM product WHERE productID=?");

                                            
                                            $stmt1->bind_param("s", $row["productID"]);
                                            $stmt1->execute();
                                            $result1 = $stmt1->get_result();
                                            $row1 = $result1->fetch_assoc();

                                            echo "<tr style='padding-top: 10px;'>";
                                            echo "<td><a href='product_details.php?id=".$row["productID"]."'>".$row["productID"]."</a></td>";
                                            echo "<td>".$row1["productName"]."</td>";
                                            echo "<td>".$row1["productPrice"]."</td>";
                                            echo "<td>".$row1["productInfo"]."</td>";
                                            echo "<td>".$row["quantity"]."</td>";
                                            echo "</tr>";
                                        }
                                        
                                        $stmt->close();
                                        $stmt1->close();
                                    }
                                    $conn->close();
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