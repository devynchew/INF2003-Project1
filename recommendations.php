<?php
require_once 'session_config.php';
?>
<!DOCTYPE html>
<html lang="en">

<body>
    <?php
    session_start();
    $user_id = isset($_SESSION['user_id']);
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

    // Function to get the highest clicked category ID for a user
    function getHighestClickedCategory($connection, $user_id)
    {
        $category_query = "
        SELECT p.category_id
        FROM clicks cl
        JOIN products p ON cl.product_id = p.product_id
        WHERE cl.user_id = $user_id
        GROUP BY p.category_id
        ORDER BY COUNT(*) DESC
        LIMIT 1";
        $category_result = $connection->query($category_query);
        return ($category_result->num_rows > 0) ? $category_result->fetch_assoc()['category_id'] : null;
    }

    // Function to display 3 products from a specified category
    function displayCategoryProducts($connection, $category_id)
    {
        if ($category_id) {
            $category_products_query = "
            SELECT p.product_id, 
            p.product_name AS productname, 
            p.price, p.product_image AS image_url, p.gender
            FROM products p
            WHERE p.category_id = $category_id
            ORDER BY RAND()
            LIMIT 3";
            $category_products_result = $connection->query($category_products_query);

            echo '<div class="row">';
            while ($product = $category_products_result->fetch_assoc()) {
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

    // Function to get the highest clicked color ID for a user
    function getHighestClickedColor($connection, $user_id)
    {
        $color_query = "
        SELECT p.color_id
        FROM clicks cl
        JOIN products p ON cl.product_id = p.product_id
        WHERE cl.user_id = $user_id
        GROUP BY p.color_id
        ORDER BY COUNT(*) DESC
        LIMIT 1";
        $color_result = $connection->query($color_query);
        return ($color_result->num_rows > 0) ? $color_result->fetch_assoc()['color_id'] : null;
    }

    // Function to display 3 products with a specified color
    function displayColorProducts($connection, $color_id)
    {
        if ($color_id) {
            $color_products_query = "
            SELECT p.product_id, p.product_name AS productname, p.price, p.product_image AS image_url, p.gender
            FROM products p
            WHERE p.color_id = $color_id
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
                    // User has clicked on products
                    echo '<h1 class="mt-5 mb-3">Recommended Products based on your history</h1>';
                    echo '<div class="row">';
                    $category_id = getHighestClickedCategory($conn, $user_id);
                    displayCategoryProducts($conn, $category_id);

                    // Display products based on highest clicked color
                    $color_id = getHighestClickedColor($conn, $user_id);
                    displayColorProducts($conn, $color_id);
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