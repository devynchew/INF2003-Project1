<?php
require_once 'session_config.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != 1) {
    header("Location: login.php");
    exit;
}

// Include a function to handle file uploads
function uploadFile($file) {
    $targetDirectory = "images/"; // Specify the directory where files should be stored
    
    // Generate a unique ID to append to the file name (using the current timestamp and a random number for extra uniqueness)
    $uniqueID = time() . '-' . rand(1000, 9999);
    
    // Extract the file extension from the original file name
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    
    // Construct the new file name by appending the unique ID to the original file name, before the file extension
    $newFileName = pathinfo($file["name"], PATHINFO_FILENAME) . "-" . $uniqueID . "." . $imageFileType;
    $targetFile = $targetDirectory . $newFileName;
    
    $uploadOk = 1;

    // Check if image file is an actual image or fake image
    $check = getimagesize($file["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size
    if ($file["size"] > 2000000) { // For example, restrict file size to 500KB
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
        return false;
    } else {
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            return $targetFile; // Return the path of the uploaded file
        } else {
            echo "Sorry, there was an error uploading your file.";
            return false;
        }
    }
}


// Sanitization helper function
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Initialize variables
$email = $pwd = $errorMsg = "";
$success = true;

// Process form when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $productName = sanitize_input($_POST['productName']);
    $productInfo = sanitize_input($_POST['productInfo']);
    $productPrice = sanitize_input($_POST['productPrice']);
    $category = sanitize_input($_POST['category']);
    $feature1 = sanitize_input($_POST['feature1']);
    $feature2 = sanitize_input($_POST['feature2']);
    $feature3 = sanitize_input($_POST['feature3']);
    $quantity = sanitize_input($_POST['quantity']);

    
    // Handle file upload
    if (isset($_FILES['productImg'])) {
        // Check if any error occurred with the file upload
        if ($_FILES['productImg']['error'] === UPLOAD_ERR_OK) {
            $productImgPath = uploadFile($_FILES['productImg']);
            if (!$productImgPath) {
                $errorMsg .= "Error uploading file.<br>";
                $success = false;
            } else {
                // Successfully uploaded file, you can echo details here if needed
                $errorMsg .= "File uploaded successfully: " . htmlspecialchars($_FILES['productImg']['name']) . "<br>";
            }
        } else {
            // File upload error
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
                UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
                UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
                UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
            ];
            // Show a more detailed error message
            $errorMsg .= "File upload error: " . $errorMessages[$_FILES['productImg']['error']] . "<br>";
            $success = false;
        }
    } else {
        $errorMsg .= "File is required.<br>";
        $errorMsg .= "You submitted the following details:<br>";
        $errorMsg .= "Product Name: " . $productName . "<br>";
        $errorMsg .= "Product Info: " . $productInfo . "<br>";
        $errorMsg .= "Product Img: " . (isset($productImgPath) ? $productImgPath : "No image path available") . "<br>"; 
    
        // Start output buffering to capture var_dump output
        ob_start();
        var_dump([
            'productName' => $productName,
            'productInfo' => $productInfo,
            'productImgPath' => isset($productImgPath) ? $productImgPath : "No image path",
            'POST' => $_POST, // Caution: This will include all POST data, potentially sensitive information
            'FILES' => $_FILES // Details about the file that was attempted to be uploaded
        ]);
        $dumpOutput = ob_get_clean(); // Get the output buffer content
    
        // Append the var_dump output to the error message
        $errorMsg .= "<pre>Debug Info: " . htmlspecialchars($dumpOutput) . "</pre>";
    
        $success = false;
    }
    
    

    if ($success) {
        // Insert into database
        $config = parse_ini_file('/var/www/private/db-config.ini');
        $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("INSERT INTO INF1005Web.product (productName, productPrice, productInfo, productImg, feature1, feature2, feature3, category, quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)");
        $stmt->bind_param("sdssssssi", $productName,  $productPrice,$productInfo,  $productImgPath,$feature1, $feature2, $feature3,$category,$quantity);
        if ($stmt->execute()) {
            $_SESSION['successMsg'] = "Product added successfully.";
        } else {
            $_SESSION['errorMsg'] = "Error: " . $stmt->error;
        }
        

        $stmt->close();
        $conn->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
    
    <head>
        <meta charset="UTF-8">
        <title>Admin Page - Add Product</title>
    </head>
    <body>
        <?php
            include "inc/head.inc.php";
            include "inc/header.inc.php";
            include "inc/nav.inc.php";
        ?>

        <div role="main" class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                <?php if (isset($_SESSION['successMsg'])): ?>
                <div class="alert alert-success" role="alert">
                    <?= $_SESSION['successMsg'] ?>
                </div>
                <?php unset($_SESSION['successMsg']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['errorMsg'])): ?>
                <div class="alert alert-danger" role="alert">
                    <?= $_SESSION['errorMsg'] ?>
                </div>
                <?php unset($_SESSION['errorMsg']); ?>
            <?php endif; ?>
                    <h2 class="text-center">Add Product</h2>
                    <?php if (!$success && !empty($errorMsg)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= $errorMsg ?>
                        </div>
                    <?php endif; ?>
                    <form action="adminpage.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="productName" class="form-label">Product Name:</label>
                            <input type="text" id="productName" name="productName" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="productInfo" class="form-label">Product Info:</label>
                            <textarea id="productInfo" name="productInfo" class="form-control" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="productPrice" class="form-label">Product Price:</label>
                            <input type="number" id="productPrice" name="productPrice" class="form-control" step="0.01" required>
                        </div>

                        <div class="mb-3">
                            <label for="productImg" class="form-label">Product Image:</label>
                            <input type="file" id="productImg" name="productImg" class="form-control" required>
                        </div>

                        <div class="mb-3">
                        <label for="category" class="form-label">Category:</label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="Men">Men</option>
                            <option value="Women">Women</option>
                            <option value="Kids">Kids</option>
                        </select>
                        </div>
                        

                        <!-- Additional fields for feature1, feature2, feature3 -->
                        <div class="mb-3">
                            <label for="feature1" class="form-label">Feature 1:</label>
                            <input type="text" id="feature1" name="feature1" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="feature2" class="form-label">Feature 2:</label>
                            <input type="text" id="feature2" name="feature2" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="feature3" class="form-label">Feature 3:</label>
                            <input type="text" id="feature3" name="feature3" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" min="1" required>
                        </div>

                        <div class="text-center">
                            <div class="btn-group" role="group" aria-label="Form Actions">
                                <a href="adminpage.php" class="btn btn-secondary mr-2">Back to Admin</a>
                                <input type="submit" value="Add Product" class="btn btn-primary">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php include "inc/footer.inc.php"; ?>
    </body>
</html>

       
