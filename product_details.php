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
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    //set cart session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    function displayProductAndReviews($connection)
    {
        $productID = $_GET['id'];
        $test = isset($_SESSION['user_id']);
        if ($test) {
            $user_id = $_SESSION['user_id'];
            if (isset($user_id )&&isset($productID)) { // to register clicks
                // Retrieve productID from URL parameter
                
                $check_click_sql = "SELECT click_id, click_count FROM clicks WHERE user_id = $user_id AND product_id = $productID";
                $clickResult = mysqli_query($connection, $check_click_sql);
            
                if (mysqli_num_rows($clickResult) > 0) {
                    // If record exists, fetch the click_count and increment it
                    $row = mysqli_fetch_assoc($clickResult);
                    $click_id = $row['click_id'];
                    $new_click_count = $row['click_count'] + 1;
            
                    // Update the existing click_count
                    $update_click_sql = "UPDATE clicks SET click_count = $new_click_count WHERE click_id = $click_id";
                    mysqli_query($connection, $update_click_sql);
                } else {
                    // If no record exists, insert a new row with click_count = 1
                    $insert_click_sql = "INSERT INTO clicks (user_id, product_id, click_count) VALUES ($user_id, $productID, 1)";
                    mysqli_query($connection, $insert_click_sql);
                }
            }
        } else {
            $user_id = 1;
        }
        // Check if product ID is set in the URL
        if (isset($_GET['id'])){ // fetch prod details if id is set
            // Fetch product details from the database
            $productSql = "SELECT p.name AS productname, p.description AS productdesc, p.price, p.image_url, p.category_id FROM products p WHERE p.product_id = $productID";
            $productResult = mysqli_query($connection, $productSql);

            if (mysqli_num_rows($productResult) > 0) {
                $productRow = mysqli_fetch_assoc($productResult);

                echo '<div class="container">';
                echo '<div class="row mt-5">';
                echo '<div class="col-md-6">';
                echo '<img src="' . $productRow['image_url'] . '" class="img-fluid product-image" alt="Product Image">';
                echo '</div>';
                echo '<div class="col-md-6 product-details">';
                // Display content
                echo '<div id="display-content">';

                echo '<h2>' . $productRow['productname'] . '</h2>';
                echo '<h3 class="price">Price: $' . $productRow['price'] . '</h3>';
                echo '<p class="description">' . $productRow['productdesc'] . '</p>';

                echo '<div class="mt-3">'; // Adding margin-top for spacing
                echo '<form action="cart.php" method="post">';
                echo '<input type="hidden" name="product_id" value="' . $productID . '">';

                ?>
                <select class="form-control" id="colors" name="colors">
                    <option value="">Colors</option>
                    <?php
                    
                        // Fetch product colors from the database
                        $sql_colors = "SELECT DISTINCT c.name AS colorname FROM colors c JOIN productcolors p ON c.color_id = p.color_id WHERE p.product_id=$productID";
                        $result_colors = mysqli_query($connection, $sql_colors);
                        if (mysqli_num_rows($result_colors) > 0) {
                            while ($row_colors = mysqli_fetch_assoc($result_colors)) {
                                echo '<option value="' . $row_colors['colorname'] . '">' . $row_colors['colorname'] . '</option>';
                            }
                        }
                            
                        
                        ?>
                </select>
                
                <div class="mt-3"> 
                <select class="form-control" id="sizes" name="sizes">
                    <option value="">Sizes</option>
                    <?php
                    
                        // Fetch product sizes from the database
                        $sql_sizes = "SELECT DISTINCT s.name AS sizename FROM sizes s JOIN productsizes p ON s.size_id = p.size_id WHERE p.product_id=$productID";
                        $result_sizes = mysqli_query($connection, $sql_sizes);
                        if (mysqli_num_rows($result_sizes) > 0) {
                            while ($row_sizes = mysqli_fetch_assoc($result_sizes)) {
                                echo '<option value="' . $row_sizes['sizename'] . '">' . $row_sizes['sizename'] . '</option>';
                            }
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
                

            }
            echo '</div>'; // End of product details
            echo '</div>'; // End of row
            echo '</div>'; // End of container

            
            echo '</div>';
            echo '</div>';

        } else {
            echo "Product not found";
        }
    }

    function postReview($connection)
    {
        // Handle form submission
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_review'])) {
            // Retrieve form data
            $review = $_POST['review'];
            $productID = $_GET['id']; // Assuming productID is obtained from the URL parameter
            $currentTime = date("Y-m-d H:i:s"); // Get current time in MySQL datetime format
    
            // Perform SQL insertion with productID and current time
            $sql = "INSERT INTO review (productID, review, timeStamp) VALUES ('$productID' , '$review', '$currentTime')";
            if (mysqli_query($connection, $sql)) {
                echo "Review submitted successfully.";
            } else {
                echo "Error: " . $sql . "<br>" . mysqli_error($connection);
            }
        }
    }

    // Establish MySQL connection using the server connection info from the function
    $config = parse_ini_file('/var/www/private/db-config.ini');
    $servername = $config['servername'];
    $username = $config['username'];
    $password = $config['password'];
    $dbname = $config['dbname'];
    $connection = mysqli_connect($servername, $username, $password, $dbname);

    // Check connection
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    displayProductAndReviews($connection);

    // Handle form submission
    postReview($connection);

    // Close the MySQL connection
    mysqli_close($connection);

    ?>

    <?php
    include "inc/footer.inc.php";
    ?>

</body>

</html>