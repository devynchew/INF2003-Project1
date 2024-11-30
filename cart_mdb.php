<?php
ob_start(); // Start output buffering to handle redirects properly
require_once 'session_config.php'; // Include session configuration and start session
require 'vendor/autoload.php'; // Include Composer's autoloader for MongoDB

use Exception;
use MongoDB\Client;
use MongoDB\Driver\ServerApi;

// Check if the user is not logged in, then redirect to login page
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: login_mdb.php');
    exit;
}

// Load database configuration
$config = parse_ini_file('/var/www/private/db-config.ini');
$uri = $config['mongodb_uri'];

// Specify Stable API version 1
$apiVersion = new ServerApi(ServerApi::V1);

try {
    $client = new MongoDB\Client($uri, [], ['serverApi' => $apiVersion]);
    $db = $client->selectDatabase('somethingqlo'); 

    // Define the user collection
    $productsCollection = $db->products;

    // Add product to cart
    if (isset($_POST['product_id'], $_POST['quantity'], $_POST['colors'], $_POST['sizes'])) {
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        $colors = $_POST['colors'];
        $sizes = $_POST['sizes'];

        if (empty($colors) || empty($sizes)) {
            $_SESSION['alert'] = "Please select both a color and a size.";
            header('Location:product_mdb.php'); 
            exit;
        }

        if ($quantity < 1) {
            $quantity = 1;  // Set to minimum if below 1
        } elseif ($quantity > 20) {
            $quantity = 20; // Set to maximum if above 20
        }

        // Fetch product details from the database
        $productDetails = $productsCollection->findone(['product_id' => (int)$product_id]);

        // Check if product exists and quantity is positive
        if ($productDetails) {
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            // Create a unique key for each combination of product_id, color, and size
            $cart_key = $product_id . '-' . $colors . '-' . $sizes;

            // Initialize product in cart if not existing
            if (!isset($_SESSION['cart'][$cart_key])) {
                $_SESSION['cart'][$cart_key] = [
                    'product_id' => $product_id,
                    'name' => $productDetails['name'],
                    'img' => $productDetails['image_url'],
                    'quantity' => 0,
                    'price' => $productDetails['price'],
                    'color' => $colors,   // Save color
                    'size' => $sizes      // Save size
                ];
            }

            // Add or update product quantity in the cart
            $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
        }

        header('Location: cart_mdb.php');
        exit;
    }

    // Remove product from cart
    if (isset($_GET['remove']) && isset($_SESSION['cart'][$_GET['remove']])) {
        unset($_SESSION['cart'][$_GET['remove']]);
    }

    // Update cart quantities
    if (isset($_POST['update']) && isset($_SESSION['cart'])) {
        foreach ($_POST as $k => $v) {
            if (strpos($k, 'quantity-') === 0 && is_numeric($v)) {
                $cart_key = str_replace('quantity-', '', $k); // Use unique cart key (product_id-color-size)
                $quantity = (int)$v;

                if ($quantity < 1) {
                    $quantity = 1;  // Set to minimum if below 1
                } elseif ($quantity > 20) {
                    $quantity = 20; // Set to maximum if above 20
                }

                if (isset($_SESSION['cart'][$cart_key]) && $quantity > 0) {
                    $_SESSION['cart'][$cart_key]['quantity'] = $quantity;
                }
            }
        }
        header('Location: cart_mdb.php');
        exit;
    }

    // Redirect to checkout page
    if (isset($_POST['placeorder']) && !empty($_SESSION['cart'])) {
        header('Location: checkout_mdb.php');
        exit;
    }

    // Fetch and prepare cart product details for display
    $products_in_cart = $_SESSION['cart'] ?? [];
    $products = [];
    $subtotal = 0.00;

    if ($products_in_cart) {
        $product_ids = array_map('intval', array_unique(array_map(function ($key) {
            return explode('-', $key)[0];
        }, array_keys($products_in_cart))));
        
        $productDetailsCursor = $productsCollection->find(['product_id' => ['$in' => $product_ids]]);
        $productDetails = iterator_to_array($productDetailsCursor);

        if (!$productDetails) {
            die("Error fetching products.");
        }

        // Calculate subtotal and organize product data
        foreach ($productDetails as $product) {
            foreach ($products_in_cart as $cart_key => $cart_item) {
                $cart_product_id = explode('-', $cart_key)[0]; // Extract product_id from cart_key
                if ($cart_product_id == $product['product_id']) {
                    $products[$cart_key] = $product;
                    $products_in_cart[$cart_key] += [
                        'name' => $product['name'],
                        'img' => $product['image_url'],
                        'total' => $product['price'] * $cart_item['quantity']
                    ];
                    $subtotal += $products_in_cart[$cart_key]['total'];
                }
            }
        }
    }

    $title = "Cart Page";
?>
<!DOCTYPE html>
<html lang="en">

<?php include "inc/head.inc.php"; ?>

<body>
    <?php include "inc/header.inc.php"; ?>
    <?php include "inc/nav.inc.php"; ?>
    <main>
        <?php if (isset($_SESSION['alert'])): ?>
            <div class="alert alert-warning" style="justify-content: center;">
                <?= htmlspecialchars($_SESSION['alert']) ?>
            </div>
            <?php unset($_SESSION['alert']); // Clear the alert ?>
        <?php endif; ?>

        <div class="cart content-wrapper">
            <h1>Shopping Cart</h1>
            <form action="cart_mdb.php" method="post">
                <table>
                    <thead>
                        <tr>
                            <td colspan="2">Product</td>
                            <td>Price</td>
                            <td>Size</td>
                            <td>Color</td>
                            <td>Quantity</td>
                            <td>Total</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)) : ?>
                            <tr>
                                <td colspan="7" style="text-align:center;">You have no products added in your Shopping Cart</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($products_in_cart as $cart_key => $cart_item) : ?>
                                <tr>
                                    <td class="img">
                                        <a href="product_details_mdb.php?id=<?= htmlspecialchars(explode('-', $cart_key)[0]) ?>">
                                            <img src="<?= $cart_item['img'] ?>" width="50" height="50" alt="<?= htmlspecialchars($cart_item['name']) ?>">
                                        </a>
                                    </td>
                                    <td>
                                        <a href="product_details_mdb.php?id=<?= htmlspecialchars(explode('-', $cart_key)[0]) ?>"><?= htmlspecialchars($cart_item['name']) ?></a>
                                        <br>
                                        <a href="cart_mdb.php?remove=<?= urlencode($cart_key) ?>" class="remove">Remove</a>
                                    </td>
                                    <td class="price">&dollar;<?= number_format($cart_item['price'], 2) ?></td>
                                    <td class="size"><?= htmlspecialchars($cart_item['size']) ?></td>
                                    <td class="color"><?= htmlspecialchars($cart_item['color']) ?></td>
                                    <td class="quantity">
                                        <input type="number" name="quantity-<?= htmlspecialchars($cart_key) ?>" value="<?= $cart_item['quantity'] ?>" min="1" max="20" placeholder="Quantity" required>
                                    </td>
                                    <td class="price">&dollar;<?= number_format($cart_item['price'] * $cart_item['quantity'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>

                </table>
                <div class="subtotal">
                    <span class="text">Subtotal</span>
                    <span class="price">&dollar;<?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="buttons">
                    <input type="submit" value="Update" name="update">
                    <input type="submit" value="Place Order" name="placeorder">
                </div>
            </form>
            <div class="buttons">
                <a href="product_mdb.php" class="cart-back-btn">Continue Shopping</a>
            </div>
        </div>
    </main>
<?php
}
catch (Exception $e) {
    $errorMsg = "Connection failed: " . $e->getMessage();
    $success = false;
}
?>
    <?php include "inc/footer.inc.php"; ?>
    <?php ob_end_flush(); // End output buffering and send output to client ?>
</body>

</html>
