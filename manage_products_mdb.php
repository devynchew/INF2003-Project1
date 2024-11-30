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

// Define the products collection
$productCollection = $db->products;
?>

<!DOCTYPE html>
<html lang="en">

<body>
    <?php
    session_start();
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        header("Location: login.php");
        exit;
    }
    ?>
    <?php
    include "inc/head.inc.php";
    include "inc/header.inc.php";
    include "inc/nav.inc.php";
    ?>

    <main class="manage_product_container">
        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Product updated successfully.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php elseif (isset($_GET['status']) && $_GET['status'] == 'error'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> Failed, Please try again.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php elseif (isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Product deleted successfully.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        <h1 class="mt-2 mb-3">Products</h1>
        <!-- Button to Open Modal for Adding a New Product -->
        <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addProductModal">
            Add New Product
        </button>
        <div class="table_container">
            <table class="table product_table">
                <thead>
                    <tr>
                        <th scope="col" class="text-nowrap">ID</th>
                        <th scope="col" class="text-nowrap">Thumbnail</th>
                        <th scope="col" class="text-nowrap">Name</th>
                        <th scope="col" class="text-nowrap">Description</th>
                        <th scope="col" class="text-nowrap">Price</th>
                        <th scope="col" class="text-nowrap">Category</th>
                        <th scope="col" class="text-nowrap">Available Colors</th>
                        <th scope="col" class="text-nowrap">Available Sizes</th>
                        <th scope="col" class="text-nowrap">Gender</th>
                        <th scope="col" class="text-nowrap"></th>
                        <th scope="col" class="text-nowrap"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $products = $productCollection->find();

                        foreach ($products as $product) {
                            $colors = $product['colors'];
                            $sizes = $product['sizes'];
                            
                            $colors = (array) $product['colors']; // Convert object to array
                            $sizes = (array) $product['sizes'];   // Convert object to array


                            echo '<tr>';
                            echo '<td class="">' . $product['product_id'] . '</td>';
                            echo '<td class=""><img src="' . $product['image_url'] . '" alt="Product Image" style="max-width: 30px;"></td>';
                            echo '<td class="">' . $product['name'] . '</td>';
                            echo '<td class="">' . $product['description'] . '</td>';
                            echo '<td class="">$' . $product['price'] . '</td>';
                            echo '<td class="">' . $product['category']['name'] . '</td>';
                            
                            // Colors
                            echo '<td class="">';
                            if (isset($colors) && is_array($colors) && !empty($colors)) {
                                echo implode(', ', $colors);
                            } else {
                                echo 'No colors available';
                            }
                            echo '</td>';
                            
                            // Sizes
                            echo '<td class="">';
                            if (isset($sizes) && is_array($sizes) && !empty($sizes)) {
                                echo implode(', ', $sizes);
                            } else {
                                echo 'No sizes available';
                            }
                            echo '</td>';
                            
                            echo '<td class="">' . $product['gender'] . '</td>';
                            echo '<td class="">
                                    <button type="button" class="btn btn-primary update-product-btn" data-toggle="modal" data-target="#updateProductModal" 
                                    data-id="' . $product['product_id'] . '" 
                                    data-name="' . $product['name'] . '" 
                                    data-description="' . $product['description'] . '" 
                                    data-price="' . $product['price'] . '" 
                                    data-category="' . $product['category']['name'] . '" ';
                            
                            // Data Attributes for Colors
                            if (isset($colors) && is_array($colors)) {
                                $colorsString = implode(', ', $colors); // Join colors into a comma-separated string
                                echo 'data-colors="' . htmlspecialchars($colorsString) . '" ';
                            }
                            
                            
                            // Data Attributes for Sizes
                            if (isset($sizes) && is_array($sizes)) {
                                $sizesString = implode(', ', $sizes); // Join sizes into a comma-separated string
                                echo 'data-sizes="' . htmlspecialchars($sizesString) . '" ';
                            }
                            
                            echo 'data-gender="' . $product['gender'] . '">Edit
                                    </button>
                                </td>';
                            
                            // Delete Button
                            echo "<td>
                                    <form method='POST' action='process_delete_product_mdb.php' style='display:inline;'>
                                        <input type='hidden' name='product_id' value='" . $product['product_id'] . "'>
                                        <button type='submit' class='btn btn-danger'>Delete</button>
                                    </form>
                                </td>";
                            
                            echo '</tr>';
                            
                        }
                    }
                    catch (Exception $e) {
                        echo 'An error occurred: ' . $e->getMessage();
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Edit Product Modal -->
        <div class="modal fade" id="updateProductModal" tabindex="-1" aria-labelledby="updateProductModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateProductModalLabel">Edit product</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?php
                            $sizes = (array) $product['sizes'];   // Convert object to array
                        

                        ?>
                        <form id="editProductForm" method="post" action="process_update_product_mdb.php">
                            <input type="hidden" id="productId" name="productId">
                            <div class="form-group">
                                <label for="productName">Name</label>
                                <input type="text" class="form-control" id="productName" name="productName" required>
                            </div>
                            <div class="form-group">
                                <label for="productDescription">Description</label>
                                <textarea class="form-control" id="productDescription" name="productDescription" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="productPrice">Price</label>
                                <input type="number" step="0.01" class="form-control" id="productPrice" name="productPrice" required>
                            </div>
                            <!-- Product Category (Dropdown) -->
                            <div class="form-group">
                                <label for="product_category">Category</label>
                                <select class="form-control" id="product_category" name="product_category" required>
                                    <?php
                                        $categories = $productCollection->aggregate([
                                            ['$group' => ['_id' => '$category.name']]
                                        ]);

                                        foreach ($categories as $category) {
                                            echo '<option value="' . htmlspecialchars($category['_id']) . '">' . htmlspecialchars($category['_id']) . '</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="productColors">Available Colors</label>
                                <div id="productColors">
                                    <?php
                                        $colors = $product['colors']; // Access all available colors
                                        
                                        if (!empty($colors)) {
                                            foreach ($colors as $color) {
                                                echo "<div class='form-check'>
                                                    <input class='form-check-input' type='checkbox' name='productColors[]' value='" . htmlspecialchars($color) . "' id='color_" . htmlspecialchars($product['product_id'] . '_' . $color) . "'>
                                                    <label class='form-check-label' for='color_" . htmlspecialchars($product['product_id'] . '_' . $color) . "'>" . htmlspecialchars($color) . "</label>
                                                </div>";
                                            }
                                        } else {
                                            echo "<p>No colors available.</p>";
                                        }
                                    ?>
                                </div>
                            </div>
                            <!-- Product Sizes (Checkboxes) -->
                            <div class="form-group">
                                <label for="productSizes">Available Sizes</label>
                                <div id="productSizes">
                                    <?php
                                        $sizes = $product['sizes']; // Access all available sizes
    
                                        if (!empty($sizes)) {
                                            foreach ($sizes as $size) {
                                                echo "<div class='form-check'>
                                                    <input class='form-check-input' type='checkbox' name='productSizes[]' value='" . htmlspecialchars($size) . "' id='color_" . htmlspecialchars($product['product_id'] . '_' . $size) . "'>
                                                    <label class='form-check-label' for='size_" . htmlspecialchars($product['product_id'] . '_' . $size) . "'>" . htmlspecialchars($size) . "</label>
                                                </div>";
                                            }
                                        } else {
                                            echo "<p>No colors available.</p>";
                                        }
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="productGender">Gender</label>
                                <select class="form-control" id="productGender" name="productGender" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Unisex">Unisex</option>
                                </select>
                            </div>
                        </form>
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" form="editProductForm">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
        // Update product
        $('#updateProductModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var productId = button.data('id');
            var productName = button.data('name');
            var productDescription = button.data('description');
            var productPrice = button.data('price');
            var productColors = button.data('colors');
            var productSizes = button.data('sizes');
            var productGender = button.data('gender');
            var categoryName = button.data('category');

            // Populate the form fields with the current product data
            $('#productId').val(productId);
            $('#productName').val(productName);
            $('#productDescription').val(productDescription);
            $('#productPrice').val(productPrice);
            $('#productGender').val(productGender);
            $('#product_category').val(categoryName);

            // Pre-check the color checkboxes based on the selected colors
            var selectedColors = productColors.split(', ');
            $('input[name="productColors[]"]').each(function() {
                $(this).prop('checked', selectedColors.includes($(this).val())); // Compare with `value`
            });


            // Pre-check the size checkboxes based on the selected sizes
            var selectedSizes = productSizes.split(', ');
            $('input[name="productSizes[]"]').each(function() {
                $(this).prop('checked', selectedSizes.includes($(this).next('label').text()));
            });
        });
        </script>
</body>

</html>