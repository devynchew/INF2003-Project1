<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once 'session_config.php';
require 'vendor/autoload.php'; // Include Composer's autoloader for MongoDB
use Exception;
use MongoDB\Client;
use MongoDB\Driver\ServerApi;
// Load configuration
$config = parse_ini_file('/var/www/private/db-config.ini');
$uri = $config['mongodb_uri'];

// Specify Stable API version 1
$apiVersion = new ServerApi(ServerApi::V1);

// Connect to MongoDB
$client = new MongoDB\Client($uri, [], ['serverApi' => $apiVersion]);
$db = $client->selectDatabase('somethingqlo');

// Define the users collection
$userCollection = $db->users; // Replace with your users collection name
$productCollection = $db->products; // Replace with your products collection name

// Step 1: Get user's highest clicked product ID
$user = $userCollection->findOne(['user_id' => 1]); // Assuming user_id is 1
if ($user && isset($user['clicks'])) {
    $clicks = (array)$user['clicks']; // Convert BSONDocument to PHP array
    $highestClickedProductId = array_keys($clicks, max($clicks))[0];
} else {
    echo "No user or click data found.";
    exit;
}

// Step 2: Get product details using product ID
$product = $productCollection->findOne(['product_id' => (int)$highestClickedProductId]);
if (!$product) {
    echo "Product not found.";
    exit;
}
$productCategory = $product['category']['name'];
$productColors = $product['colors'];

// Step 3: Get similar products by category
$similarCategoryProducts = $productCollection->find(['category.name' => $productCategory, 'product_id' => ['$ne' => (int)$highestClickedProductId]]);

// Step 4: Get similar products by color
$similarColorProducts = $productCollection->find(['colors' => ['$in' => $productColors], 'product_id' => ['$ne' => (int)$highestClickedProductId]]);

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
<head>
    <title>Recommendations</title>
</head>
<body>
<?php
    session_start();
    //$user_id = isset($_SESSION['user_id']);
    include "inc/head.inc.php";
    include "inc/header.inc.php";
    include "inc/nav.inc.php";?>
    <main>
        <div class="container">
    <h2>Similar Category Products</h2>
    <div class="category-products">
        <?php
        echo '<div class="row">';
        foreach ($similarCategoryProducts as $catProduct) {
            
            echo '<div class="col-md-4 mb-4">';
            echo '<div class="card">';
            echo '<img src="' . $catProduct['image_url'] . '" class="card-img-top" alt="Product Image">';
            echo '<div class="card-body">';
            echo '<h2 class="card-subtitle" style="font-size: 12px; text-decoration:none;">' . $catProduct['gender'] . '</h2>';
            echo '<h2 class="card-title" style="font-size: 16px;">' . $catProduct['name'] . '</h2>';
            echo '<p class="card-text">$' . $catProduct['price'] . '</p>';
            echo '<a href="product_details_mdb.php?id=' . $catProduct['product_id'] . '" class="btn btn-primary" style="background-color: #0056b3">View Details</a>';
            echo '</div></div></div>';
        }echo '</div>';
        ?>
    </div>

    <h2>Similar Color Products</h2>
    <div class="color-products">
        <?php
        echo '<div class="row">';
        foreach ($similarColorProducts as $colorProduct) {
            echo '<div class="col-md-4 mb-4">';
            echo '<div class="card">';
            echo '<img src="' . $colorProduct['image_url'] . '" class="card-img-top" alt="Product Image">';
            echo '<div class="card-body">';
            echo '<h2 class="card-subtitle" style="font-size: 12px; text-decoration:none;">' . $colorProduct['gender'] . '</h2>';
            echo '<h2 class="card-title" style="font-size: 16px;">' . $colorProduct['name'] . '</h2>';
            echo '<p class="card-text">$' . $colorProduct['price'] . '</p>';
            echo '<a href="product_details_mdb.php?id=' . $colorProduct['product_id'] . '" class="btn btn-primary" style="background-color: #0056b3">View Details</a>';
            echo '</div></div></div>';
        }echo '</div>';
        ?>
    </div>
    </div>
    </main>
    <?php
    include "inc/footer.inc.php";
    ?>
</body>
</html>
