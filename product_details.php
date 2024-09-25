<!DOCTYPE html>
<html lang="en">

<body>
    <?php
    include "inc/head.inc.php";
    include "inc/header.inc.php";
    include "inc/nav.inc.php";
    ?>
    <?php

    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    //set cart session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    function displayProductAndReviews($connection)
    {
        // Check if product ID is set in the URL
        if (isset($_GET['id'])) {
            // Retrieve productID from URL parameter
            $productID = $_GET['id'];

            // Fetch product details from the database
            //$productSql = "SELECT productName, productInfo, productPrice, productImg FROM product WHERE productID = $productID";
            $productSql = "SELECT name, description, price, image_url, category_id FROM products WHERE product_id = $productID";
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
                echo '<h2>' . $productRow['name'] . '</h2>';
                echo '<h3 class="price">Price: $' . $productRow['price'] . '</h3>';
                echo '<p class="description">' . $productRow['description'] . '</p>';
                echo '<h3>Product Details</h3>';
                echo '<ul>';
                //$productRow['feature1']
                echo '<li>' . 'feature1 (you can put color here)' . '</li>';
                echo '<li>' . 'feature2' . '</li>';
                echo '<li>' . 'feature3' . '</li>';
                echo '</ul>';
                echo '<div class="mt-3">'; // Adding margin-top for spacing
                echo '<form action="cart.php" method="post">';
                echo '<input type="hidden" name="product_id" value="' . $productID . '">';
                echo '<label for="quantity">Quantity:</label>';
                echo '<input type="number" name="quantity" class="form-control" id="quantity" value="1" min="1">';
                echo '<button type="submit" class="btn btn-primary" style="margin-top: 10px;">Add to Cart</button>';
                echo '</form>';
                echo '</div>';
                echo '</div>'; // End of display content
                /*
                if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == 1) {
                    // Edit form (hidden initially)
                    echo '<div id="edit-content" style="display: none;">';
                    echo '<form id="editForm" action="process_savechanges.php" method="post" enctype="multipart/form-data">';
                    // Hidden input for product ID
                    echo '<input type="hidden" name="productId" value="' . $productID . '">';

                    // Product Name
                    echo '<div class="form-group">';
                    echo '<label for="productName">Product Name:</label>';
                    echo '<input type="text" class="form-control mb-2" name="productName" value="' . htmlspecialchars($productRow['productName']) . '">';
                    echo '</div>';

                    // Product Price
                    echo '<div class="form-group">';
                    echo '<label for="productPrice">Price:</label>';
                    echo '<input type="text" class="form-control mb-2" name="productPrice" value="' . htmlspecialchars($productRow['productPrice']) . '">';
                    echo '</div>';

                    // Product Info
                    echo '<div class="form-group">';
                    echo '<label for="productInfo">Info:</label>';
                    echo '<textarea class="form-control mb-2" name="productInfo">' . htmlspecialchars($productRow['productInfo']) . '</textarea>';
                    echo '</div>';

                    // Product Image Upload
                    echo '<div class="form-group">';
                    echo '<label for="productImg">Product Image:</label>';
                    echo '<input type="file" class="form-control-file mb-2" name="productImg">';
                    echo '<small>Current Image: ' . htmlspecialchars($productRow['productImg']) . '</small>';
                    echo '</div>';

                    // Product Features
                    echo '<div class="form-group">';
                    echo '<label for="feature1">Feature 1:</label>';
                    echo '<input type="text" class="form-control mb-2" name="feature1" value="' . htmlspecialchars($productRow['feature1']) . '">';
                    echo '<label for="feature2">Feature 2:</label>';
                    echo '<input type="text" class="form-control mb-2" name="feature2" value="' . htmlspecialchars($productRow['feature2']) . '">';
                    echo '<label for="feature3">Feature 3:</label>';
                    echo '<input type="text" class="form-control mb-2" name="feature3" value="' . htmlspecialchars($productRow['feature3']) . '">';
                    echo '</div>';

                    // Product Category
                    // Assuming you have a way to list categories, possibly fetched from the database
                    // bro idk what is this category stuff, commenting out first - dom 25/9/24
                    echo '<div class="form-group">';
                    echo '<label for="category">Category:</label>';
                    echo '<select class="form-control mb-2" name="category">';
                    $selectedCategory = htmlspecialchars($productRow['category']);

                    // Define your categories
                    $categories = ['Men', 'Women', 'Kids'];

                    // Loop through the categories and mark the current product's category as selected
                    foreach ($categories as $category) {
                        $selected = ($category == $selectedCategory) ? 'selected' : '';
                        echo "<option value=\"$category\" $selected>$category</option>";
                    }
                    echo '</select>';
                    echo '</div>';

                    // Quantity - assuming this is for setting a default or available stock quantity
                    // Assuming $productRow['quantity'] contains the quantity
                    echo '<div class="form-group">';
                    echo '<label for="quantity">Quantity:</label>';
                    echo '<input type="number" class="form-control mb-2" name="quantity" value="' . htmlspecialchars($productRow['quantity']) . '" min="1">';
                    echo '</div>';


                    // Save Changes Button
                    echo '<button type="submit" class="btn btn-success mt-2">Save Changes</button>';
                    echo '<button type="button" onclick="cancelEdit2()" class="btn btn-secondary mt-2">Cancel</button>';
                    echo '</form>';
                    echo '</div>'; // End of edit form
                    // Check if errorMsg session variable is set
                    if (isset($_SESSION['errorMsg'])) {
                        echo '<div class="alert alert-danger" role="alert">';
                        if (is_array($_SESSION['errorMsg'])) {
                            // If it's an array, display each message on a new line
                            foreach ($_SESSION['errorMsg'] as $msg) {
                                echo htmlspecialchars($msg) . "<br>";
                            }
                        } else {
                            // If it's a string, display it directly
                            echo htmlspecialchars($_SESSION['errorMsg']);
                        }
                        echo '</div>';

                        // Clear the session variable after displaying the error
                        unset($_SESSION['errorMsg']);
                    }
                    // Show edit button for admins
    
                    echo '<div class="mt-3" id="display-content2" style="display: block;">';
                    echo '<button onclick="editProduct()" class="btn btn-secondary">Edit Product</button>';
                    echo '</div>';

                    echo '<div class="mt-3" id="display-content3" style="display: block;">';
                    echo '<form action="deleteproduct.php" method="post" onsubmit="return confirm(\'Are you sure you want to delete this product?\');">';
                    echo '<input type="hidden" name="productId" value="' . $productID . '">';
                    echo '<button type="submit" class="btn btn-danger">Delete</button>';
                    echo '</form>';
                    echo '</div>';
                }
                    */

            }
            echo '</div>'; // End of product details
            echo '</div>'; // End of row
            echo '</div>'; // End of container
    


            // Fetch reviews for the product
            /*
            $reviewSql = "SELECT review, timeStamp FROM review WHERE productID = $productID";
            $reviewResult = mysqli_query($connection, $reviewSql);

            echo '<div class="row mt-5">';
            echo '<div class="col-md-12 product-details">';
            echo '<div class="row align-items-center mb-3">';
            echo '<h3 class="mb-0 col">Reviews</h3>'; // Reviews heading to the left
            //NEW COMMENT SECTION
            echo '<form method="post" style="margin-right: 18px;">';
            echo '<div class="input-group mb-3">';
            echo '<input type="text" name="review" class="form-control" placeholder="Enter comment" aria-label="Enter Comment" aria-describedby="basic-addon2">';
            echo '<div class="input-group-append">';
            echo '<button type="submit" name="submit_review" class="btn btn-outline-secondary">Submit Review</button>';
            echo '</div>';
            echo '</div>';
            echo '</form>';

            echo '</div>';

            if (mysqli_num_rows($reviewResult) > 0) {
                // Display reviews
                //while ($reviewRow = mysqli_fetch_assoc($reviewResult)) {
                //    echo '<div class="review-container">';
                //    echo '<div class="review">';
                //    echo '<p>' . $reviewRow['review'] . '</p>';
                //    echo '<p>' . $reviewRow['timeStamp'] . '</p>';
                //    echo '</div>';
                //    echo '</div>'; // Close review-container
                //    echo '<hr>'; // Add a horizontal line between reviews
                //}
                while ($reviewRow = mysqli_fetch_assoc($reviewResult)) {
                    echo '<div class="review-container">';
                    echo '<div class="review">';
                    echo '<p class="review-text">' . $reviewRow['review'] . '</p>';
                    echo '<p class="review-timestamp">Posted on: ' . date("Y-m-d", strtotime($reviewRow['timeStamp'])) . '</p>'; // Modified to display date only
                    echo '</div>';
                    echo '</div>'; // Close review-container
                    echo '<hr class="review-divider">'; // Add a styled horizontal line between reviews
                }
            } else {
                // Display a message if there are no reviews
                echo '<p>No reviews yet.</p>';
            }*/
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