<?php
require_once 'session_config.php';
require 'vendor/autoload.php'; // Include Composer's autoloader for MongoDB
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
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
    $user_id = isset($_SESSION['user_id']);
    // Check if product ID is set in the URL
    if (isset($product_id))
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
            // Define the users collection
            $userCollection = $db->users; // Replace with your users collection name
            $product = $productsCollection->findone(['product_id' => (int)$product_id]);
            if (isset($user_id)){
                $user = $userCollection->findOne(['user_id' => (int)$user_id]);
                if ($user) {
                $clicks = $user['clicks'] ?? [];
                if (isset($clicks[(int)$product_id])) {
                    // Increment the click count for the product
                    $clicks[(int)$product_id] += 1;
                    //echo"incremented $product_id";
                } else {
                    // Create a new entry for the product with a click count of 1
                    $clicks[(int)$product_id] = 1;
                    //echo"created click for $product_id";
                }
                $userCollection->updateOne(
                    ['user_id' => (int)$user_id],
                    ['$set' => ['clicks' => $clicks]]
                );
            }else {
                //echo"$user_id";
                //echo"user not found";
            }
            }
            else{
                //echo"user id not found";
            }
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