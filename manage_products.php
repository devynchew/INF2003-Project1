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
                <strong>Error!</strong> Failed to update the product. Please try again.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        <h1 class="mt-2 mb-3">Products</h1>
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
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetching products and their related categories, colors, and sizes
                    $sql = "SELECT 
                                p.product_id,
                                p.image_url,
                                p.name AS product_name,
                                p.description,
                                p.price,
                                p.gender,
                                cat.name AS category,
                                cat.category_id,
                                GROUP_CONCAT(DISTINCT c.name ORDER BY c.name ASC SEPARATOR ', ') AS colors,
                                GROUP_CONCAT(DISTINCT s.name ORDER BY s.name ASC SEPARATOR ', ') AS sizes
                            FROM products p
                            LEFT JOIN categories cat ON p.category_id = cat.category_id
                            LEFT JOIN productcolors pc ON p.product_id = pc.product_id
                            LEFT JOIN colors c ON pc.color_id = c.color_id
                            LEFT JOIN productsizes ps ON p.product_id = ps.product_id
                            LEFT JOIN sizes s ON ps.size_id = s.size_id
                            GROUP BY p.product_id";
                    $result = mysqli_query($connection, $sql);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
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
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal" 
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
                            echo '</tr>';
                        }
                    } else {
                        echo "No products found";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Edit product</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?php
                        // Fetch the current product details
                        $product_id = $row['product_id'];
                        $sql = "SELECT * FROM products WHERE product_id = ?";
                        $stmt = mysqli_prepare($connection, $sql);
                        mysqli_stmt_bind_param($stmt, "i", $product_id);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        $product = mysqli_fetch_assoc($result);
                        echo '<h3>$' . $product['category_id'] . '</h3>';

                        // Fetch the categories from the database
                        $categories_sql = "SELECT category_id, name FROM categories";
                        $categories_result = mysqli_query($connection, $categories_sql);
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
                                <input type="text" class="form-control" id="productColors" name="productColors" required>
                            </div>
                            <div class="form-group">
                                <label for="productSizes">Available Sizes</label>
                                <input type="text" class="form-control" id="productSizes" name="productSizes" required>
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

    </main>
    <?php
    include "inc/footer.inc.php";
    ?>
    <script>
        $('#exampleModal').on('show.bs.modal', function(event) {
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
            $('#productColors').val(productColors);
            $('#productSizes').val(productSizes);
            $('#productGender').val(productGender);
            $('#product_category').val(categoryId); // Set the category in the dropdown
        });
    </script>
</body>

</html>