<?php
require_once 'session_config.php';
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
                                <li><a href="userprofile.php">My Account</a></li>
                                <li><a href="userorders.php">My Orders</a></li>
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
                                <th>Status</th>
                                <th>Total Price</th>
                                <th>Date</th>
                                <th></th>
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
                                        // Prepare the statement to get member id from email
                                        $stmt = $conn->prepare("SELECT * FROM orders WHERE member_id = (SELECT member_id FROM members WHERE email=?)");
                                        // $stmt = $conn->prepare("SELECT * FROM orders WHERE member_id = '4'");

                                        // Bind & execute the query statement:
                                        $stmt->bind_param("s", $email);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $rowcount = mysqli_num_rows($result);

                                        if($rowcount > 0)
                                        {
                                            while ($row = $result->fetch_assoc())
                                            {
                                                $date = date("Y-m-d",strtotime($row["orderDate"]));

                                                echo "<tr style='padding-top: 10px;'>";
                                                echo "<td><a href='orderdetails.php?orderID=".$row["orderID"]."'>".$row["orderID"]."</a></td>";
                                                echo "<td>".$row["orderStatus"]."</td>";
                                                echo "<td>".$row["totalPrice"]."</td>";
                                                echo "<td>".$date."</td>";
                                                echo "<td>";
                                                if ($row["orderStatus"] != "Refunded") {
                                                    echo "<form action='refund.php' method='post'>";
                                                    echo "<input type='hidden' name='orderID' value='" . $row['orderID'] . "'>";
                                                    echo "<button type='submit' class='btn btn-warning btn-sm'>Request Refund</button>";
                                                    echo "</form>";
                                                }
                                                echo "</td>";
                                                echo "</tr>";
                                            }
                                        }
                                        else 
                                        {
                                            $errorMsg = true;
                                        }
                                        
                                        $stmt->close();
                                    }
                                    $conn->close();
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