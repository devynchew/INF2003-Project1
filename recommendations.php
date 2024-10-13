<?php
require_once 'session_config.php';
?>
<!DOCTYPE html>
<html lang="en">
<style>
    /* Tooltip container */
    .tooltip2 {
        position: relative;
        display: inline;
        border-bottom: 1px solid black;
    }

    .tooltip2 .tooltiptext {
        visibility: hidden;
        width: 120px;
        background-color: black;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 5px 0;
        position: absolute;
        z-index: 1;
        bottom: 150%;
        left: 50%;
        margin-left: -60px;
        font-size: 12px;
    }

    .tooltip2 .tooltiptext::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: black transparent transparent transparent;
    }

    /* Show the tooltip text when you mouse over the tooltip container */
    .tooltip2:hover .tooltiptext {
        visibility: visible;
    }
</style>

<body>
    <?php
    session_start();
    //$user_id = isset($_SESSION['user_id']);
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
            <?php
            $displayed_product_ids = [];
            $user_id = isset($_SESSION['user_id']);
            // Function to get the highest clicked category ID for a user
            function getHighestClickedCategoryAndColor($connection, $user_id)
            {
                $query = "
                    SELECT 
                    p.category_id,
                    pc.color_id
                    FROM clicks cl
                    JOIN products p ON cl.product_id = p.product_id
                    JOIN productcolors pc ON p.product_id = pc.product_id
                    WHERE cl.user_id = $user_id
                    GROUP BY p.category_id, pc.color_id
                    ORDER BY SUM(cl.click_count) DESC
                    LIMIT 1;
                ";
                $result = $connection->query($query);
                if (!$result) {
                    // Return error message if query fails
                    return "Error: " . $connection->error;
                }
                // Return both category and color if results are found
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    return [$row['category_id'], $row['color_id']];
                } else {
                    return [null, null];
                }
            }

            function displayCategoryProducts($connection, $category_id)
            {
                if ($category_id) {
                    $category_products_query = "
                    SELECT p.product_id, 
                    p.name AS productname, 
                    p.price, p.image_url, p.gender
                    FROM products p
                    WHERE p.category_id = $category_id
                    ORDER BY RAND()
                    LIMIT 3";
                    $category_products_result = $connection->query($category_products_query);

                    echo '<div class="row">';
                    while ($product = $category_products_result->fetch_assoc()) {
                        $displayed_product_ids[] = $product['product_id'];
                        echo '<div class="col-md-4 mb-4">';
                        echo '<div class="card">';
                        echo '<img src="' . $product['image_url'] . '" class="card-img-top" alt="Product Image">';
                        echo '<div class="card-body">';
                        echo '<h2 class="card-subtitle" style="font-size: 12px; text-decoration:none;">' . $product['gender'] . '</h2>';
                        echo '<h2 class="card-title" style="font-size: 16px;">' . $product['productname'] . '</h2>';
                        echo '<p class="card-text">$' . $product['price'] . '</p>';
                        echo '<a href="product_details.php?id=' . $product['product_id'] . '" class="btn btn-primary" style="background-color: #0056b3">View Details</a>';
                        echo '</div></div></div>';
                    }
                    echo '</div>';
                }
                return $displayed_product_ids;
            }

            function getHighestClickedProd($connection, $user_id)
            {
                $prod_query = "
                SELECT p.name
                FROM clicks cl
                JOIN products p ON cl.product_id = p.product_id
                WHERE cl.user_id = $user_id
                GROUP BY p.product_id
                ORDER BY SUM(cl.click_count) DESC
                LIMIT 1;";
                $prod_result = $connection->query($prod_query);
                return ($prod_result->num_rows > 0) ? $prod_result->fetch_assoc()['name'] : null;
            }

            // Function to display 3 products with a specified color
            function displayColorProducts($connection, $color_id, $displayed_product_ids)
            {
                if ($color_id) {
                    $excluded_ids = implode(',', $displayed_product_ids) ?: '';
                    $color_products_query = "
                    SELECT p.product_id, p.name AS productname, p.price, image_url, p.gender
                    FROM products p
                    JOIN productcolors pc ON p.product_id = pc.product_id
                    WHERE pc.color_id = $color_id
                    AND p.product_id NOT IN ($excluded_ids)
                    ORDER BY RAND()
                    LIMIT 3";
                    $color_products_result = $connection->query($color_products_query);

                    echo '<div class="row">';
                    while ($product = $color_products_result->fetch_assoc()) {
                        echo '<div class="col-md-4 mb-4">';
                        echo '<div class="card">';
                        echo '<img src="' . $product['image_url'] . '" class="card-img-top" alt="Product Image">';
                        echo '<div class="card-body">';
                        echo '<h2 class="card-subtitle" style="font-size: 12px; text-decoration:none;">' . $product['gender'] . '</h2>';
                        echo '<h2 class="card-title" style="font-size: 16px;">' . $product['productname'] . '</h2>';
                        echo '<p class="card-text">$' . $product['price'] . '</p>';
                        echo '<a href="product_details.php?id=' . $product['product_id'] . '" class="btn btn-primary" style="background-color: #0056b3">View Details</a>';
                        echo '</div></div></div>';
                    }
                    echo '</div>';
                }
            }
            if ($user_id) {
                $check_clicks_query = "
                    SELECT EXISTS (
                        SELECT 1
                        FROM clicks
                        WHERE user_id = $user_id
                    ) AS user_has_clicks";
                $result = $connection->query($check_clicks_query);
                $row = $result->fetch_assoc();
                if ($row['user_has_clicks'] == 1) {
                    // User has clicked on products, to add tooltip with highest clicked product here
                    echo '<h1 class="mt-5 mb-3">Recommended Products based on your history</h1>';
                    $mostClickedProd = getHighestClickedProd($connection, $user_id);
                    echo '<p class="mt-3 mb-3" style="font-size: 1.2em;">Based on your 
                    <span class="tooltip2">most clicked product:
                    <span class="tooltiptext">' . $mostClickedProd . '</span></span></p>';
                    echo '<div class="row">';
                    [$category_id, $color_id] = getHighestClickedCategoryAndColor($connection, $user_id);
                    $displayed_product_ids = displayCategoryProducts($connection, $category_id);
                    echo '<p class="mt-3 mb-3" style="font-size: 1.2em;">You might also like: </p>';
                    displayColorProducts($connection, $color_id, $displayed_product_ids);
                    echo '</div>';
                } else {
                    // User has no clicks recorded
                    echo '<h1 class="mt-5 mb-3">No recommended products yet, go browse our store!</h1>';
                    echo '<a href="index.php" class="btn btn-primary mt-3">Go to Home Page</a>';
                }
            } else {
                echo '<h1 class="mt-5 mb-3">Login to view recommendations!</h1>';
                echo '<a href="login.php" class="btn btn-primary mt-3">Login</a>';
            }

            ?>
        </div>
    </main>
    <?php
    include "inc/footer.inc.php";
    ?>
</body>

</html>