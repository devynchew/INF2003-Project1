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

            $config = parse_ini_file('/var/www/private/db-config.ini');
            // Establish MySQL connection using the server connection info from the function
            $servername = $config['servername'];
            $username = $config['username'];
            $password = $config['password'];
            $dbname = $config['dbname'];

            // Create connection
            $connection = mysqli_connect($servername, $username, $password, $dbname);

            // Check connection
            if (!$connection) {
                die("Connection failed: " . mysqli_connect_error());
            }
        ?>
        <main>
        <div class="container">
            <h1 class="mt-5 mb-3">Our Products</h1>

            <div class="row mb-4">
                <div class="col-md-6">
                    <form method="GET" action="">
                        <div class="form-group">
                            <label for="category">Filter by Category:</label>
                            <select class="form-control" id="category" name="category">
                                <option value="">All Categories</option>
                                <?php
                                    // Fetch distinct categories from the database
                                    $sql_categories = "SELECT DISTINCT category FROM product";
                                    $result_categories = mysqli_query($connection, $sql_categories);
                                    if (mysqli_num_rows($result_categories) > 0) {
                                        while ($row_category = mysqli_fetch_assoc($result_categories)) {
                                            echo '<option value="' . $row_category['category'] . '">' . $row_category['category'] . '</option>';
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary" style="background-color: #0056b3; margin-bottom: 15px;">Apply Filter</button>
                    </form>

                </div>
            </div>

            <div class="row">
                    <?php
                        // Adjust SQL query based on the selected category
                        $category_filter = isset($_GET['category']) ? $_GET['category'] : '';
                        $sql = "SELECT productID, productName, productInfo, productPrice, productImg FROM product";
                        if (!empty($category_filter)) {
                            $sql .= " WHERE category = '$category_filter'";
                        }
                        $result = mysqli_query($connection, $sql);

                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo '<div class="col-md-4 mb-4">';
                                echo '<div class="card">';
                                echo '<img src="' . $row['productImg'] . '" class="card-img-top" alt="Product Image">';
                                echo '<div class="card-body">';
                                echo '<h2 class="card-title" style="font-size: 16px;">' . $row['productName'] . '</h2>';
                                echo '<p class="card-text">$' . $row['productPrice'] . '</p>';
                                echo '<a href="product_details.php?id=' . $row['productID'] . '" class="btn btn-primary" style="background-color: #0056b3">View Details</a>';
                                echo '</div>';
                                echo '</div>'; 
                                echo '</div>'; 
                            }
                        } else {
                            echo "No products found";
                        }
                    ?>
                </div>
        </div>
        </main>
    </body>
</html>