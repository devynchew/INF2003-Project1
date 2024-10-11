<?php
// Include database configuration
$config = parse_ini_file('/var/www/private/db-config.ini');
$servername = $config['servername'];
$username = $config['username'];
$password = $config['password'];
$dbname = $config['dbname'];

// Create connection
$connection = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get product details from POST request
    $product_id = mysqli_real_escape_string($connection, $_POST['productId']);
    $product_name = mysqli_real_escape_string($connection, $_POST['productName']);
    $product_description = mysqli_real_escape_string($connection, $_POST['productDescription']);
    $product_price = mysqli_real_escape_string($connection, $_POST['productPrice']);
    $product_category = $_POST['product_category'];
    $product_colors = explode(", ", mysqli_real_escape_string($connection, $_POST['productColors']));
    $product_sizes = explode(", ", mysqli_real_escape_string($connection, $_POST['productSizes']));
    $product_gender = mysqli_real_escape_string($connection, $_POST['productGender']);
    
    // Start transaction
    mysqli_begin_transaction($connection);

    try {
        // Update the `products` table
        $update_product_sql = "UPDATE products 
                               SET name = ?, description = ?, price = ?, gender = ?, category_id = ?
                               WHERE product_id = ?";
        $stmt = mysqli_prepare($connection, $update_product_sql);
        mysqli_stmt_bind_param($stmt, 'ssdsdi', $product_name, $product_description, $product_price, $product_gender, $product_category, $product_id);
        mysqli_stmt_execute($stmt);
        
        // Update the `productcolors` table
        // First, delete existing entries for this product in `productcolors`
        // $delete_colors_sql = "DELETE FROM productcolors WHERE product_id = ?";
        // $stmt = mysqli_prepare($connection, $delete_colors_sql);
        // mysqli_stmt_bind_param($stmt, 'i', $product_id);
        // mysqli_stmt_execute($stmt);

        // Insert new colors
        // foreach ($product_colors as $color) {
        //     $insert_color_sql = "INSERT INTO productcolors (product_id, color_id) 
        //                          VALUES (?, (SELECT color_id FROM colors WHERE name = ? LIMIT 1))";
        //     $stmt = mysqli_prepare($connection, $insert_color_sql);
        //     mysqli_stmt_bind_param($stmt, 'is', $product_id, $color);
        //     mysqli_stmt_execute($stmt);
        // }

        // Update the `productsizes` table
        // First, delete existing entries for this product in `productsizes`
        // $delete_sizes_sql = "DELETE FROM productsizes WHERE product_id = ?";
        // $stmt = mysqli_prepare($connection, $delete_sizes_sql);
        // mysqli_stmt_bind_param($stmt, 'i', $product_id);
        // mysqli_stmt_execute($stmt);

        // Insert new sizes
        // foreach ($product_sizes as $size) {
        //     $insert_size_sql = "INSERT INTO productsizes (product_id, size_id) 
        //                         VALUES (?, (SELECT size_id FROM sizes WHERE name = ? LIMIT 1))";
        //     $stmt = mysqli_prepare($connection, $insert_size_sql);
        //     mysqli_stmt_bind_param($stmt, 'is', $product_id, $size);
        //     mysqli_stmt_execute($stmt);
        // }

        // Commit the transaction
        mysqli_commit($connection);

        // Redirect back to the products page with a success message
        header("Location: manage_products.php?status=success");
        exit();
        
    } catch (Exception $e) {
        // Rollback the transaction if something goes wrong
        mysqli_rollback($connection);
        echo "Failed to update product: " . $e->getMessage();
        // Redirect back to the products page with an error message
        header("Location: manage_products.php?status=error");
        exit();
    }
    
} else {
    // If the request method is not POST, redirect back to the products page
    header("Location: manage_products.php");
    exit();
}

// Close the connection
mysqli_close($connection);
?>
