<?php
session_start(); // Start the session to access session variables.

// Initialize or clear previous error messages
$_SESSION['errorMsg'] = [];

// Include your database connection script here
try {
    // Load database configuration
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        throw new Exception("Database configuration loading failed.");
    }

    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    $_SESSION['errorMsg'][] = htmlspecialchars($e->getMessage());
    header("Location: errorPage.php");
    exit;
}

// Check if the user is logged in and is an admin
if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != 1) {
    $_SESSION['errorMsg'][] = "Unauthorized access.";
    header("Location: productDetails.php"); // Adjust as necessary to redirect to the appropriate page
    exit;
}

// Check if the product ID is set and is a valid number
if (isset($_POST['productId']) && filter_var($_POST['productId'], FILTER_VALIDATE_INT)) {
    $productId = $_POST['productId'];

    // First, fetch the name of the image file associated with this product
    $sqlFetchImage = "SELECT productImg FROM product WHERE productID = ?";
    $stmtFetchImage = $conn->prepare($sqlFetchImage);
    $stmtFetchImage->bind_param('i', $productId);
    $stmtFetchImage->execute();
    $result = $stmtFetchImage->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $productImgPath = $row['productImg'];
        
        // Prepare SQL statement to delete the product
        $sqlDelete = "DELETE FROM product WHERE productID = ?";
        $stmtDelete = $conn->prepare($sqlDelete);

        // Bind the integer parameter and execute
        $stmtDelete->bind_param('i', $productId);
        if ($stmtDelete->execute() && $stmtDelete->affected_rows > 0) {
            // Check if the image path is not empty and file exists before attempting to delete
            if (!empty($productImgPath) && file_exists($productImgPath)) {
                if (!unlink($productImgPath)) {
                    
                    
                }
            }
        } else {
            $_SESSION['errorMsg'][] = "No product found with the provided ID.";
        }
        
        $stmtDelete->close();
    } else {
        $_SESSION['errorMsg'][] = "No product found with the provided ID.";
    }
    $stmtFetchImage->close();
} else {
    $_SESSION['errorMsg'][] = "Invalid product ID.";
}

// Close the database connection
$conn->close();

// Redirect back to the product details page, or to a product listing if ID is not available
if (!empty($_SESSION['errorMsg'])) {
    header("Location: productDetails.php?id=" . $productId); // Adjust as necessary
} else {
    header("Location: product.php"); // Or any other appropriate page
}
exit;
?>
