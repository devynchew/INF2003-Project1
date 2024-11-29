<?php
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

    // Enable error reporting for debugging
    // error_reporting(E_ALL);
    // ini_set('display_errors', 1);

    //set cart session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    $product_id = $_GET['id'];

    // Check if product ID is set in the URL
    if (isset($_GET['id']))
    { // fetch prod details if id is set
        
        $config = parse_ini_file('/var/www/private/db-config.ini');
        $uri = $config['mongodb_uri'];

        // Specify Stable API version 1
        $apiVersion = new ServerApi(ServerApi::V1);

        // Connect to MongoDB
        try {
            $client = new MongoDB\Client($uri, [], ['serverApi' => $apiVersion]);
            $db = $client->selectDatabase('somethingqlo');                 
            
            // Define the orders collection
            $productsCollection = $db->products;

            $product = $productsCollection->findone(['product_id' => (int)$product_id]);
            
            if ($product) {
                echo '<div class="container">';
                echo '<div class="row mt-5">';
                echo '<div class="col-md-6">';
                echo '<img src="' . $product['image_url'] . '" class="img-fluid product-image" alt="Product Image">';
                echo '</div>';
                echo '<div class="col-md-6 product-details">';
                // Display content
                echo '<div id="display-content">';
    
                echo '<h2>' . $product['name'] . '</h2>';
                echo '<h3 class="price">Price: $' . $product['price'] . '</h3>';
                echo '<p class="description">' . $product['description'] . '</p>';
    
                echo '<div class="mt-3">'; // Adding margin-top for spacing
                echo '<form action="cart_mdb.php" method="post">';
                echo '<input type="hidden" name="product_id" value="' . $product['product_id'] . '">';

    ?>
    
    <select class="form-control" id="colors" name="colors">
        <option value="">Colors</option>
        <?php

            $colors = $product['colors'];

            foreach ($colors as $color) {
                echo '<option value="' . $color . '">' . $color . '</option>';
            }
            
            ?>
    </select>        

    <div class="mt-3"> 
    <select class="form-control" id="sizes" name="sizes">
        <option value="">Sizes</option>
        <?php

            $sizes = $product['sizes'];

            foreach ($sizes as $size) {
                echo '<option value="' . $size . '">' . $size . '</option>';
            }

        ?>
    </select>

    <div class="mt-3"> 
        <?php

                echo '<label for="quantity">Quantity:</label>';
                echo '<input type="number" name="quantity" class="form-control" id="quantity" value="1" min="1">';
                echo '<button type="submit" class="btn btn-primary" style="margin-top: 10px;">Add to Cart</button>';
                echo '</form>';
                echo '</div>';
                echo '</div>'; // End of display content

                echo '</div>'; // End of product details
                echo '</div>'; // End of row
                echo '</div>'; // End of container

                
                echo '</div>';
                echo '</div>';
                

            }
            else {
                echo "Product not found";
            }
        }
        catch (Exception $e) {
            $errorMsg = "Connection failed: " . $e->getMessage();
            $success = false;
        }

    }
    ?>

    <?php
    include "inc/footer.inc.php";
    ?>

</body>

</html>