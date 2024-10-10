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
        echo "<script>console.error('Connection failed: " . mysqli_connect_error() . "');</script>";
        die();
    } else {
        echo "<script>console.log('SQL Connected successfully');</script>";
    }
    ?>
    <main>
        <div class="container">
            <h1 class="mt-5 mb-3">Our Products</h1>

            <div class="row mb-4">
                <div class="col-md-6">
                    <form id="cat-filter" method="get" action="">
                        <div class="form-group">
                            <label for="category">Filter by Category:</label>
                            <select class="form-control" id="category" name="category">
                                <option value="">All Categories</option>

                                <?php
                                
                                    // Fetch distinct categories from the database
                                    $sql_categories = "SELECT DISTINCT name FROM categories";
                                    $result_categories = mysqli_query($connection, $sql_categories);
                                    if (mysqli_num_rows($result_categories) > 0) {
                                        while ($row_category = mysqli_fetch_assoc($result_categories)) {
                                            echo '<option value="' . $row_category['name'] . '">' . $row_category['name'] . '</option>';
                                        }
                                    }
                                        
                                    
                                ?>
                            </select>
                        </div>
                        
                    </form>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button type="submit" form="cat-filter" class="btn btn-primary" style="background-color: #0056b3; margin-bottom: 15px;">Apply Filter</button>
                </div>
            <?php

                 if (isset($_GET['category'])!="") {
                    echo '<h4 class="col-md-6 mt-2 mb-0">Filter Results: '.$_GET['category'].'</h4>';
                } 

            ?>
            </div>
            <div class="row">
                <?php

                // Adjust SQL query based on the selected category
                $category_filter = isset($_GET['category']) ? $_GET['category'] : '';
                // $sql = "SELECT p.product_id, p.name, p.description, p.price, p.image_url FROM products p";
                $sql = "SELECT  p.product_id, p.name AS productname, p.description, p.gender, p.price, p.image_url, c.name AS categoryname FROM products p, categories c WHERE p.category_id=c.category_id";
                if (!empty($category_filter)) {
                            $sql .= " AND c.name = '$category_filter'";
                        }
                $result = mysqli_query($connection, $sql);
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<div class="col-md-4 mb-4">';
                        echo '<div class="card">';
                        echo '<img src="' . $row['image_url'] . '" class="card-img-top" alt="Product Image">';
                        echo '<div class="card-body">';
                        echo '<h2 class="card-subtitle" style="font-size: 12px; text-decoration:none;">' . $row['gender'] . '</h2>';
                        echo '<h2 class="card-title" style="font-size: 16px;">' . $row['productname'] . '</h2>';
                        echo '<p class="card-text">$' . $row['price'] . '</p>';
                        echo '<a href="product_details.php?id=' . $row['product_id'] . '" class="btn btn-primary" style="background-color: #0056b3">View Details</a>';
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
    <?php
    include "inc/footer.inc.php";
    ?>
</body>

</html>