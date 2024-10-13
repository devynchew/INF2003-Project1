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

    $config = parse_ini_file('/var/www/private/db-config.ini');
    $servername = $config['servername'];
    $username = $config['username'];
    $password = $config['password'];
    $dbname = $config['dbname'];

    $connection = mysqli_connect($servername, $username, $password, $dbname);

    if (!$connection) {
        echo "<script>console.error('Connection failed: " . mysqli_connect_error() . "');</script>";
        die();
    } else {
        echo "<script>console.log('SQL Connected successfully');</script>";
    }
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
                    // Fetching products and their related categories, colors, and sizes
                    $fetch_all_product_sql = "SELECT 
                                p.product_id,
                                p.image_url,
                                p.name AS product_name,
                                p.description,
                                p.price,
                                p.gender,
                                cat.name AS category,
                                cat.category_id,
                                GROUP_CONCAT(DISTINCT c.name ORDER BY c.name ASC SEPARATOR ', ') AS colors,
                                GROUP_CONCAT(DISTINCT s.name ORDER BY FIELD(s.name, 'S', 'M', 'L', 'XL') SEPARATOR ', ') AS sizes
                            FROM products p
                            LEFT JOIN categories cat ON p.category_id = cat.category_id
                            LEFT JOIN productcolors pc ON p.product_id = pc.product_id
                            LEFT JOIN colors c ON pc.color_id = c.color_id
                            LEFT JOIN productsizes ps ON p.product_id = ps.product_id
                            LEFT JOIN sizes s ON ps.size_id = s.size_id
                            GROUP BY p.product_id";
                    $all_product_result = mysqli_query($connection, $fetch_all_product_sql);

                    if (mysqli_num_rows($all_product_result) > 0) {
                        while ($row = mysqli_fetch_assoc(result: $all_product_result)) {
                            echo '<tr>';
                            echo '<td class="text-nowrap">' . $row['product_id'] . '</td>';
                            echo '<td class="text-nowrap"><img src="' . $row['image_url'] . '" alt="Product Image" style="max-width: 30px;"></td>';
                            echo '<td class="text-nowrap">' . $row['product_name'] . '</td>';
                            echo '<td class="text-nowrap">' . $row['description'] . '</td>';
                            echo '<td class="text-nowrap">$' . $row['price'] . '</td>';
                            echo '<td class="text-nowrap">' . $row['category'] . '</td>';
                            echo '<td class="text-nowrap">' . $row['colors'] . '</td>';
                            echo '<td class="text-nowrap">' . $row['sizes'] . '</td>';
                            echo '<td class="text-nowrap">' . $row['gender'] . '</td>';
                            echo '<td class="text-nowrap">
                                    <button type="button" class="btn btn-primary update-product-btn" data-toggle="modal" data-target="#updateProductModal" 
                                    data-id="' . $row['product_id'] . '" 
                                    data-name="' . $row['product_name'] . '" 
                                    data-description="' . $row['description'] . '" 
                                    data-price="' . $row['price'] . '" 
                                    data-colors="' . $row['colors'] . '" 
                                    data-sizes="' . $row['sizes'] . '" 
                                    data-gender="' . $row['gender'] . '" 
                                    data-category-id="' . $row['category_id'] . '">Edit
                                    </button>
                                </td>';
                            // Add delete button
                            echo "<td><form method='POST' action='process_delete_product.php' style='display:inline;'>";
                            echo "<input type='hidden' name='product_id' value='" . $row['product_id'] . "'>";
                            echo "<button type='submit' class='btn btn-danger'>Delete</button>";
                            echo "</form></td>";
                            echo '</tr>';
                        }
                    } else {
                        echo "No products found";
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
                        // Fetch the current product details
                        $product_id = $row['product_id'];
                        $fetch_single_product_sql = "SELECT * FROM products WHERE product_id = ?";
                        $stmt = mysqli_prepare($connection, $fetch_single_product_sql);
                        mysqli_stmt_bind_param($stmt, "i", $product_id);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        $product = mysqli_fetch_assoc($result);

                        // Fetch the categories from the database
                        $categories_sql = "SELECT category_id, name FROM categories";
                        $categories_result = mysqli_query($connection, $categories_sql);

                        // Fetch available colors from the database
                        $colors_sql = "SELECT color_id, name FROM colors";
                        $colors_result = mysqli_query($connection, $colors_sql);

                        // Fetch available sizes from the database
                        $available_sizes_sql = "SELECT size_id, name FROM sizes";
                        $available_sizes_result = mysqli_query($connection, $available_sizes_sql);
                        ?>
                        <form id="editProductForm" method="post" action="process_update_product.php">
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
                                    if (mysqli_num_rows($categories_result) > 0) {
                                        while ($category = mysqli_fetch_assoc($categories_result)) {
                                            $selected = ($product['category_id'] == $category['category_id']) ? 'selected' : '';
                                            echo "<option value='" . $category['category_id'] . "' $selected>" . $category['name'] . "</option>";
                                        }
                                    } else {
                                        echo "<option value=''>No categories available</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="productColors">Available Colors</label>
                                <div id="productColors">
                                    <?php
                                    if (mysqli_num_rows($colors_result) > 0) {
                                        while ($color = mysqli_fetch_assoc($colors_result)) {
                                            echo "<div class='form-check'>
                        <input class='form-check-input' type='checkbox' name='productColors[]' value='" . $color['color_id'] . "' id='color_" . $color['color_id'] . "'>
                        <label class='form-check-label' for='color_" . $color['color_id'] . "'>" . $color['name'] . "</label>
                      </div>";
                                        }
                                    } else {
                                        echo "<p>No colors available</p>";
                                    }
                                    ?>
                                </div>
                            </div>
                            <!-- Product Sizes (Checkboxes) -->
                            <div class="form-group">
                                <label for="productSizes">Available Sizes</label>
                                <div id="productSizes">
                                    <?php
                                    if (mysqli_num_rows($available_sizes_result) > 0) {
                                        while ($size = mysqli_fetch_assoc($available_sizes_result)) {
                                            echo "<div class='form-check'>
                                                <input class='form-check-input' type='checkbox' name='productSizes[]' value='" . $size['size_id'] . "' id='size_" . $size['size_id'] . "'>
                                                <label class='form-check-label' for='size_" . $size['size_id'] . "'>" . $size['name'] . "</label>
                                              </div>";
                                        }
                                    } else {
                                        echo "No sizes available";
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
                        <form id="addProductForm" method="post" action="process_add_product.php">
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
                                <label for="newProductCategory">Category</label>
                                <select class="form-control" id="newProductCategory" name="newProductCategory" required>
                                    <?php
                                    $categories_sql = "SELECT category_id, name FROM categories";
                                    $categories_result = mysqli_query($connection, $categories_sql);

                                    if (mysqli_num_rows($categories_result) > 0) {
                                        while ($category = mysqli_fetch_assoc($categories_result)) {
                                            echo "<option value='" . $category['category_id'] . "'>" . $category['name'] . "</option>";
                                        }
                                    } else {
                                        echo "<option value=''>No categories available</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="newProductColors">Available Colors</label>
                                <div id="newProductColors">
                                    <?php
                                    $colors_sql = "SELECT color_id, name FROM colors";
                                    $colors_result = mysqli_query($connection, $colors_sql);

                                    if (mysqli_num_rows($colors_result) > 0) {
                                        while ($color = mysqli_fetch_assoc($colors_result)) {
                                            echo "<div class='form-check'>
                        <input class='form-check-input' type='checkbox' name='newProductColors[]' value='" . $color['color_id'] . "' id='newColor_" . $color['color_id'] . "'>
                        <label class='form-check-label' for='newColor_" . $color['color_id'] . "'>" . $color['name'] . "</label>
                      </div>";
                                        }
                                    } else {
                                        echo "<p>No colors available</p>";
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="newProductSizes">Available Sizes</label>
                                <div id="newProductSizes">
                                    <?php
                                    $sizes_sql = "SELECT size_id, name FROM sizes";
                                    $sizes_result = mysqli_query($connection, $sizes_sql);

                                    if (mysqli_num_rows($sizes_result) > 0) {
                                        while ($size = mysqli_fetch_assoc($sizes_result)) {
                                            echo "<div class='form-check'>
                        <input class='form-check-input' type='checkbox' name='newProductSizes[]' value='" . $size['size_id'] . "' id='newSize_" . $size['size_id'] . "'>
                        <label class='form-check-label' for='newSize_" . $size['size_id'] . "'>" . $size['name'] . "</label>
                      </div>";
                                        }
                                    } else {
                                        echo "<p>No sizes available</p>";
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
                </div>
                <div class="col-md-6">
                    <h2>Popular Colors</h2>
                    <canvas id="popularColorsChart" width="400" height="auto"></canvas>
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
            var categoryId = button.data('category-id'); // Get the category ID

            // Populate the form fields with the current product data
            $('#productId').val(productId);
            $('#productName').val(productName);
            $('#productDescription').val(productDescription);
            $('#productPrice').val(productPrice);
            $('#productGender').val(productGender);
            $('#product_category').val(categoryId);

            // Pre-check the color checkboxes based on the selected colors
            var selectedColors = productColors.split(', ');
            $('input[name="productColors[]"]').each(function() {
                $(this).prop('checked', selectedColors.includes($(this).next('label').text()));
            });

            // Pre-check the size checkboxes based on the selected sizes
            var selectedSizes = productSizes.split(', ');
            $('input[name="productSizes[]"]').each(function() {
                $(this).prop('checked', selectedSizes.includes($(this).next('label').text()));
            });
        });

        // Display top 5 selling products
        <?php
        $top_selling_sql = "SELECT p.name, SUM(op.quantity) AS total_sold
                    FROM ordersproduct op
                    JOIN products p ON op.product_id = p.product_id
                    GROUP BY p.name
                    ORDER BY total_sold DESC
                    LIMIT 5";
        $top_selling_result = mysqli_query($connection, $top_selling_sql);

        $product_names = [];
        $product_sales = [];

        while ($row = mysqli_fetch_assoc($top_selling_result)) {
            $product_names[] = $row['name'];
            $product_sales[] = $row['total_sold'];
        }

        // Convert PHP arrays to JavaScript arrays
        echo "var productNames = " . json_encode($product_names) . ";\n";
        echo "var productSales = " . json_encode($product_sales) . ";\n";

        $colors_sql = "SELECT c.name, COUNT(pc.product_id) AS color_popularity
               FROM productcolors pc
               JOIN colors c ON pc.color_id = c.color_id
               JOIN ordersproduct op ON op.product_id = pc.product_id
               GROUP BY c.name
               ORDER BY color_popularity DESC";
        $colors_result = mysqli_query($connection, $colors_sql);

        $color_names = [];
        $color_popularity = [];

        while ($row = mysqli_fetch_assoc($colors_result)) {
            $color_names[] = $row['name'];
            $color_popularity[] = $row['color_popularity'];
        }

        echo "var colorNames = " . json_encode($color_names) . ";\n";
        echo "var colorPopularity = " . json_encode($color_popularity) . ";\n";
        ?>

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
    </script>
</body>

</html>