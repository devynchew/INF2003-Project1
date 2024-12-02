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
                        <th scope="col" class="text-nowrap">Stock</th>
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
                            echo '<td class="">' . $product['stock'] . '</td>';
                            echo '<td class="">
                                    <button type="button" class="btn btn-primary update-product-btn" data-toggle="modal" data-target="#updateProductModal" 
                                    data-id="' . $product['product_id'] . '" 
                                    data-name="' . $product['name'] . '" 
                                    data-description="' . $product['description'] . '" 
                                    data-price="' . $product['price'] . '" 
                                    data-category="' . $product['category']['name'] . '" ;
                                    data-stock="' . $product['stock'] . '" ';

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
                    } catch (Exception $e) {
                        echo 'An error occurred: ' . $e->getMessage();
                    }
                    ?>
                </tbody>
            </table>
            
        </div>
        <div class="text-center">
            <a href="adminpage.php" class="btn btn-secondary mr-2">Back to Admin</a>
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
                            <div class="form-group">
                                <label for="productStock">Stock</label>
                                <input type="number" step="1" class="form-control" id="productStock" name="productStock" required>
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
                                    $colors = $productCollection->distinct("colors");

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
                                    $sizes = $productCollection->distinct("sizes");

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
        <!-- Add New Product Modal -->
        <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="addProductForm" method="post" action="process_add_product_mdb.php">
                            <div class="form-group">
                                <label for="newProductURL">Image URL</label>
                                <input type="text" class="form-control" id="newProductURL" name="newProductURL" required>
                            </div>
                            <div class="form-group">
                                <label for="newProductName">Product Name</label>
                                <input type="text" class="form-control" id="newProductName" name="newProductName" required>
                            </div>
                            <div class="form-group">
                                <label for="newProductDescription">Description</label>
                                <textarea class="form-control" id="newProductDescription" name="newProductDescription" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="newProductPrice">Price</label>
                                <input type="number" step="0.01" class="form-control" id="newProductPrice" name="newProductPrice" required>
                            </div>
                            <div class="form-group">
                                <label for="newProductStock">Stock</label>
                                <input type="number" step="1" class="form-control" id="newProductStock" name="newProductStock" required>
                            </div>
                            <div class="form-group">
                                <label for="newProductCategory">Category</label>
                                <select class="form-control" id="newProductCategory" name="newProductCategory" required>
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
                                <label for="newProductColors">Available Colors</label>
                                <div id="newProductColors">
                                    <?php
                                    $colors = $productCollection->distinct("colors");

                                    if (!empty($colors)) {
                                        foreach ($colors as $color) {
                                            echo "<div class='form-check'>
                                                    <input class='form-check-input' type='checkbox' name='newProductColors[]' value='" . htmlspecialchars($color) . "' id='newColor_" . htmlspecialchars($product['product_id'] . '_' . $color) . "'>
                                                    <label class='form-check-label' for='newColor_" . htmlspecialchars($product['product_id'] . '_' . $color) . "'>" . htmlspecialchars($color) ."</label>
                                                </div>";
                                        }
                                    } else {
                                        echo "<p>No colors available.</p>";
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="newProductSizes">Available Sizes</label>
                                <div id="newProductSizes">
                                    <?php
                                    $sizes = $productCollection->distinct("sizes");

                                    if (!empty($sizes)) {
                                        foreach ($sizes as $size) {
                                            echo "<div class='form-check'>
                                                    <input class='form-check-input' type='checkbox' name='newProductSizes[]' value='" . htmlspecialchars($size) . "' id='newSize_" . htmlspecialchars($product['product_id'] . '_' . $size) . "'>
                                                    <label class='form-check-label' for='newSize_" . htmlspecialchars($product['product_id'] . '_' . $size) . "'>" . htmlspecialchars($size) . "</label>
                                                </div>";
                                        }
                                    } else {
                                        echo "<p>No colors available.</p>";
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="newProductGender">Gender</label>
                                <select class="form-control" id="newProductGender" name="newProductGender" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Unisex">Unisex</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" form="addProductForm">Add Product</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-5 mb-3">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="">Top-Selling Products</h2>
                    <canvas id="topSellingProductsChart" width="400" height="auto"></canvas>
                    <?php
                        $orderCollection = $db->orders;

                        // Aggregation pipeline
                        $pipeline = [
                            // Unwind the products array to deconstruct each product into its own document
                            ['$unwind' => '$products'],
                            // Group by product_id and calculate the total quantity
                            [
                                '$group' => [
                                    '_id' => '$products.product_id',
                                    'totalQuantity' => ['$sum' => '$products.quantity']
                                ]
                            ],
                            // Sort by totalQuantity in descending order
                            ['$sort' => ['totalQuantity' => -1]],
                            // Limit to get the top 5 ordered products
                            ['$limit' => 5]
                        ];

                        // Execute aggregation
                        $result = $orderCollection->aggregate($pipeline);

                        // Fetch and return the top 5 most ordered products
                        $topProducts = $result->toArray();

                        if (!empty($topProducts)) {
                            $productCollection = $db->products;
                            
                            $product_names = [];
                            $product_sales = [];
                            
                            foreach ($topProducts as $product) {
                                // Convert BSONDocument to PHP array
                                $productArray = $product->getArrayCopy();
                            
                                // Access _id and totalQuantity fields
                                $productId = $productArray['_id'];
                                $totalQuantity = $productArray['totalQuantity'];
                            
                                // Fetch the product document from the products collection
                                $productDoc = $productCollection->findOne(['product_id' => $productId]);
                            
                                if ($productDoc) {
                                    $product_names[] = $productDoc['name'];
                                    $product_sales[] = $totalQuantity;
                                } 
                            }
                            
                            // Convert PHP arrays to JavaScript for Chart.js
                            echo "<script>
                                var productNames = " . json_encode($product_names) . ";
                                var productSales = " . json_encode($product_sales) . ";
                            </script>";

                        } else {
                            echo "No products found in orders.\n";
                        }
                    ?>

                </div>
                <div class="col-md-6">
                    <h2>Popular Colors</h2>
                    <canvas id="popularColorsChart" width="400" height="auto"></canvas>
                    <?php
                        // Aggregation pipeline
                        $pipeline = [
                            ['$unwind' => '$products'],
                            [
                                '$group' => [
                                    '_id' => '$products.color',
                                    'totalQuantity' => ['$sum' => '$products.quantity']
                                ]
                            ],
                            // Sort by totalQuantity in descending order
                            ['$sort' => ['totalQuantity' => -1]],
                            // Limit to get the top 5 ordered products
                            ['$limit' => 5]
                        ];

                        // Execute aggregation
                        $result = $orderCollection->aggregate($pipeline);

                        // Fetch and return the top 5 most ordered colors
                        $topColors = $result->toArray();

                        $color_names = [];
                        $color_popularity = [];

                        foreach ($topColors as $color) {
                            // Convert BSONDocument to PHP array
                            $colorArray = $color->getArrayCopy();
                        
                            // Access _id and totalQuantity fields
                            $colorName = $colorArray['_id'];
                            $totalQuantity = $colorArray['totalQuantity'];
                        
                            $color_names[] = $colorName;
                            $color_popularity[] = $totalQuantity;
                        }

                        // Convert PHP arrays to JavaScript for Chart.js
                        echo "<script>
                            var colorNames = " . json_encode($color_names) . ";
                            var colorPopularity = " . json_encode($color_popularity) . ";
                        </script>";
                    
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="w-75 mx-auto">
                    <h2>Total Revenue per Category</h2>
                    <canvas id="revenueChart"></canvas>
                    <?php
                        try {
                            // Use the MongoDB orders and products collections
                            $orderCollection = $db->orders;
                            $productCollection = $db->products;

                            // Aggregation pipeline
                            $pipeline = [
                                // Unwind the products array to process each product individually
                                ['$unwind' => '$products'],

                                // Lookup to join with the products collection to fetch the price and category
                                [
                                    '$lookup' => [
                                        'from' => 'products', // Collection to join with
                                        'localField' => 'products.product_id', // Field in the orders collection
                                        'foreignField' => 'product_id', // Field in the products collection
                                        'as' => 'productDetails' // Output array containing matched documents
                                    ]
                                ],

                                // Unwind the productDetails array to process joined data
                                ['$unwind' => '$productDetails'],
                                
                                [
                                    '$project' => [
                                        'category' => '$productDetails.category.name',
                                        'price' => ['$toDouble' => '$productDetails.price'], // Cast price to double
                                        'quantity' => ['$toInt' => '$products.quantity'] // Cast quantity to integer
                                    ]
                                ],

                                // Group by category and calculate total revenue
                                [
                                '$group' => [
                                    '_id' => '$category', // Group by category name
                                    'totalRevenue' => [
                                        '$sum' => ['$multiply' => ['$price', '$quantity']] // Multiply price by quantity
                                    ]
                                ]
                            ],

                                // Sort by total revenue in descending order
                                ['$sort' => ['totalRevenue' => -1]]
                            ];

                            // Execute the aggregation pipeline
                            $result = $orderCollection->aggregate($pipeline);

                            // Process the result
                            $categories = [];
                            $revenues = [];
                            foreach ($result as $data) {
                                $categories[] = $data['_id']; // Category name
                                $revenues[] = $data['totalRevenue']; // Total revenue
                            }
                        } catch (Exception $e) {
                            echo "An error occurred: " . $e->getMessage();
                        }
                        ?>

                </div>
            </div>
        </div>
        </main>
        <?php
        include "inc/footer.inc.php";
        ?>
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
                var productStock = button.data('stock');
                var categoryName = button.data('category');

                // Populate the form fields with the current product data
                $('#productId').val(productId);
                $('#productName').val(productName);
                $('#productDescription').val(productDescription);
                $('#productPrice').val(productPrice);
                $('#productGender').val(productGender);
                $('#product_category').val(categoryName);
                $('#productStock').val(productStock);

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

            var topSellingProductsctx = document.getElementById('topSellingProductsChart').getContext('2d');
            var topSellingProductsChart = new Chart(topSellingProductsctx, {
                type: 'bar',
                data: {
                    labels: productNames, // Product names from PHP
                    datasets: [{
                        label: 'Units Sold',
                        data: productSales, // Sales data from PHP
                        backgroundColor: 'rgba(0, 0, 0, 0.5)',
                        borderColor: 'rgba(0, 0, 0, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            var popularColorsctx = document.getElementById('popularColorsChart').getContext('2d');
            var popularColorsChart = new Chart(popularColorsctx, {
                type: 'pie',
                data: {
                    labels: colorNames,
                    datasets: [{
                        data: colorPopularity,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                    }
                }
            });
            // PHP data to JavaScript
            const categories = <?php echo json_encode($categories); ?>;
            const revenues = <?php echo json_encode($revenues); ?>;

            var revenue_ctx = document.getElementById('revenueChart').getContext('2d');

            // Create the chart
            var revenueChart = new Chart(revenue_ctx, {
                type: 'bar', // Bar chart type
                data: {
                    labels: categories, // Category names as labels
                    datasets: [{
                        label: 'Revenue per Category ($)',
                        data: revenues, // Revenue values for each category
                        backgroundColor: 'rgba(0, 0, 0, 0.5)', // Bar color
                        borderColor: 'rgba(0, 0, 0, 1)', // Border color
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true // Ensure the y-axis starts at 0
                        }
                    },
                    plugins: {
                        legend: {
                            display: true, // Show the legend
                            position: 'top', // Legend position
                        }
                    }
                }
            });
        </script>
</body>

</html>