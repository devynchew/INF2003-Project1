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
$db = $client->selectDatabase('somethingqlo'); // Replace with your database name

// Define the products collection
$productCollection = $db->products;

// Build the unique category list directly from the products collection
$categories = [];
try {
    $categoriesCursor = $productCollection->distinct("category.name"); // Fetch unique category names
    foreach ($categoriesCursor as $categoryName) {
        $categories[] = htmlspecialchars($categoryName); // Ensure safety for HTML rendering
    }
} catch (Exception $e) {
    echo "<p>Error fetching categories: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Build filters based on GET parameters
$filter = [];
if (!empty($_GET['category'])) {
    $filter['category.name'] = htmlspecialchars($_GET['category']);
}
if (!empty($_GET['search'])) {
    $filter['name'] = ['$regex' => htmlspecialchars($_GET['search']), '$options' => 'i'];
}

// Apply filters to fetch products
try {
    $products = $productCollection->find($filter);
} catch (Exception $e) {
    echo "<p>Error fetching filtered products: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit();
}
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
        <div class="container">
            <h1 class="mt-5 mb-3">Our Products</h1>

            <div class="row mb-4">
                <div class="col-md-6">
                    <form id="cat-filter" method="get" action="">
                        <div class="form-group d-flex">
                            <input type="search" name="search" id="search" class="form-control col-md-6" placeholder="Search.." />
                            <select class="form-control col-md-6 ml-2" id="category" name="category">
                                <option value="">All Categories</option>
                                <?php
                                foreach ($categories as $category) {
                                    $selected = (!empty($_GET['category']) && $_GET['category'] == $category) ? 'selected' : '';
                                    echo '<option value="' . $category . '" ' . $selected . '>' . $category . '</option>';
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
                if (!empty($_GET['category'])) {
                    echo '<h4 class="col-md-6 mt-2 mb-0">Filter Results: ' . htmlspecialchars($_GET['category']) . '</h4>';
                }
                ?>
            </div>
            <div class="row">
                <?php
                foreach ($products as $product) {
                    echo '<div class="col-md-4 mb-4">';
                    echo '<div class="card">';
                    echo '<img src="' . htmlspecialchars($product['image_url']) . '" class="card-img-top" alt="Product Image">';
                    echo '<div class="card-body">';
                    echo '<h2 class="card-subtitle" style="font-size: 12px; text-decoration:none;">' . htmlspecialchars($product['gender']) . '</h2>';
                    echo '<h2 class="card-title" style="font-size: 16px;">' . htmlspecialchars($product['name']) . '</h2>';
                    echo '<p class="card-text">$' . htmlspecialchars($product['price']) . '</p>';
                    echo '<a href="product_details.php?id=' . htmlspecialchars($product['product_id']) . '" class="btn btn-primary" style="background-color: #0056b3">View Details</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
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
