
<?php
session_start();

// Sanitization helper function
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to display errors from the session
function display_session_errors() {
    if (isset($_SESSION['errors']) && count($_SESSION['errors']) > 0) {
        echo "<div style='color: red; font-weight: bold; margin-bottom: 20px;'>";
        foreach ($_SESSION['errors'] as $error) {
            echo htmlspecialchars($error) . "<br>";
        }
        echo "</div>";
        unset($_SESSION['errors']); // Clear errors after displaying
    }
}

try {
    // Load database configuration
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        throw new Exception("Failed to read database config file.");
    }

    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    $_SESSION['errors'] = [htmlspecialchars($e->getMessage())];
    header("Location: errorPage.php"); // Redirect to an error page or another page to display the errors
    exit;
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];

    // Sanitize and validate inputs
    $productId = isset($_POST['productId']) ? sanitize_input($_POST['productId']) : '';
    $productName = isset($_POST['productName']) ? sanitize_input($_POST['productName']) : '';
    $productPriceRaw = isset($_POST['productPrice']) ? sanitize_input($_POST['productPrice']) : '';
    $productInfo = isset($_POST['productInfo']) ? sanitize_input($_POST['productInfo']) : '';
    $category = isset($_POST['category']) ? sanitize_input($_POST['category']) : '';
    $feature1 = isset($_POST['feature1']) ? sanitize_input($_POST['feature1']) : '';
    $feature2 = isset($_POST['feature2']) ? sanitize_input($_POST['feature2']) : '';
    $feature3 = isset($_POST['feature3']) ? sanitize_input($_POST['feature3']) : '';
    $quantity = isset($_POST['quantity']) ? sanitize_input($_POST['quantity']) : '';

    $productId = is_numeric($productId) ? (int)$productId : 0; // Cast to integer if numeric
    $productPrice = is_numeric($productPrice) ? (float)$productPrice : 0.0; // Cast to float if numeric
    $quantity = is_numeric($quantity) ? (int)$quantity : 0; // Cast to integer if numeric

    // Validate required fields
    // Validate required fields and check if numeric without casting first
    if (empty($productName)) {
        $errors[] = "Product name is required.";
    }
    if (empty($productPriceRaw) || !is_numeric($productPriceRaw)) {
        $error_message = "Product price must be a numeric value.";
        if (!empty($productPriceRaw)) {
            $error_message .= " Entered price: '" . htmlspecialchars($productPriceRaw) . "'.";
        }
        $errors[] = $error_message;
    } else {
        // Now safe to cast since it's confirmed to be numeric
        $productPrice = (float)$productPriceRaw;
    }

        // Handle the product image upload if a file was submitted
        $productImg = ''; // Default to no change
        if (!empty($_FILES['productImg']['name'])) {
            $targetDirectory = "images/"; // Ensure this directory exists and is writable
            $targetFile = $targetDirectory . basename($_FILES['productImg']['name']);
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            $validExtensions = ['jpg', 'png', 'jpeg', 'gif'];
    
            // Check if image file is an actual image or fake image
            $check = getimagesize($_FILES['productImg']['tmp_name']);
            if ($check === false) {
                $errors[] = "File is not an image.";
            }
    
            // Check file size (e.g., 5MB maximum)
            if ($_FILES['productImg']['size'] > 5000000) {
                $errors[] = "Your file is too large.";
            }
    
            // Allow certain file formats
            if (!in_array($imageFileType, $validExtensions)) {
                $errors[] = "Only JPG, JPEG, PNG & GIF files are allowed.";
            }
    
            // Check if $errors is empty (no errors found)
            if (empty($errors)) {
                if (move_uploaded_file($_FILES['productImg']['tmp_name'], $targetFile)) {
                    $productImg = $targetFile; // File path to store in the database
                } else {
                    $errors[] = "There was an error uploading your file.";
                }
            }
        }

    if (empty($errors)) {
        try {
                // Update the product in the database
                if (!empty($productImg)){
                    $sql = "UPDATE INF1005Web.product SET productName=?, productPrice=?, productInfo=?, productImg=?, feature1=?, feature2=?, feature3=?, category=?, quantity=? WHERE productId=?";
                    if ($stmt = $conn->prepare($sql)) {
                        // Bind variables to the prepared statement
                        $stmt->bind_param("sdssssssii", $productName, $productPrice, $productInfo, $productImg, $feature1, $feature2, $feature3, $category, $quantity, $productId);
            
                        // Attempt to execute the prepared statement
                        if (!$stmt->execute()) {
                            $errors[] = "Error updating record: " . $stmt->error;
                        }
                        $stmt->close();
                    } else {
                        $errors[] = "Error preparing the update statement: " . $conn->error;
                    }
                }else{
                    $sql = "UPDATE INF1005Web.product SET productName=?, productPrice=?, productInfo=?, feature1=?, feature2=?, feature3=?, category=?, quantity=? WHERE productId=?";
                    if ($stmt = $conn->prepare($sql)) {
                        // Bind variables to the prepared statement
                        $stmt->bind_param("sdsssssii", $productName, $productPrice, $productInfo, $feature1, $feature2, $feature3, $category, $quantity, $productId);
            
                        // Attempt to execute the prepared statement
                        if (!$stmt->execute()) {
                            $errors[] = "Error updating record: " . $stmt->error;
                        }
                        $stmt->close();
                    } else {
                        $errors[] = "Error preparing the update statement: " . $conn->error;
                    }
                }
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        $_SESSION['errorMsg'] = $errors;
        header("Location: product_details.php?id=" . urlencode($productId));
        exit();
    } else {
        header("Location: product_details.php?id=" . urlencode($productId) . "&success=1");
        exit();
    }
} else {
    // Not a POST request, redirect to home or error page
    header('Location: index.php');
    exit();
}

// Remember to close your database connection if it's no longer needed
if (isset($conn)) {
    $conn->close();
}

// Place this function call where you want to display errors on your target page (e.g., at the top of your form in editProduct.php)
// display_session_errors();
?>
