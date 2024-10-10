<?php
ob_start(); // Start output buffering to handle redirects properly
require_once 'session_config.php'; // Include session configuration and start session

// Check if the user is not logged in, then redirect to login page
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: login.php');
    exit;
}

// Function to establish database connection
function getDatabaseConnection()
{
    $config = parse_ini_file('/var/www/private/db-config.ini'); // Read database configuration
    if (!$config) {
        die("Failed to read database config file.");
    }
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']); // Create connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

$conn = getDatabaseConnection(); // Establish database connection

// Add product to cart
if (isset($_POST['product_id'], $_POST['quantity'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    if ($quantity < 1) {
        $quantity = 1;  // Set to minimum if below 1
    } elseif ($quantity > 20) {
        $quantity = 20; // Set to maximum if above 20
    }

    // Fetch product details from the database
    $stmt = $conn->prepare('SELECT * FROM products WHERE product_id = ?');
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    // Check if product exists and quantity is positive
    if ($product && $quantity > 0) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Initialize product in cart if not existing
        if (!isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] = ['quantity' => 0, 'price' => $product['productPrice']];
        }

        // Add or update product quantity in the cart
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    }

    $stmt->close();
    header('Location: cart.php');
    exit;
}

// Remove product from cart
if (isset($_GET['remove']) && is_numeric($_GET['remove']) && isset($_SESSION['cart'][$_GET['remove']])) {
    unset($_SESSION['cart'][$_GET['remove']]);
}

// Update cart quantities
if (isset($_POST['update']) && isset($_SESSION['cart'])) {
    foreach ($_POST as $k => $v) {
        if (strpos($k, 'quantity-') === 0 && is_numeric($v)) {
            $id = str_replace('quantity-', '', $k);
            $quantity = (int)$v;

            if ($quantity < 1) {
                $quantity = 1;  // Set to minimum if below 1
            } elseif ($quantity > 20) {
                $quantity = 20; // Set to maximum if above 20
            }

            if (isset($_SESSION['cart'][$id]) && $quantity > 0) {
                $_SESSION['cart'][$id]['quantity'] = $quantity;
            }
        }
    }
    header('Location: cart.php');
    exit;
}

// Redirect to checkout page
if (isset($_POST['placeorder']) && !empty($_SESSION['cart'])) {
    header('Location: checkout.php');
    exit;
}

// Fetch and prepare cart product details for display
$products_in_cart = $_SESSION['cart'] ?? [];
$products = [];
$subtotal = 0.00;

if ($products_in_cart) {
    $product_ids = implode(',', array_keys($products_in_cart));
    $sql = "SELECT * FROM products WHERE product_id IN ($product_ids)";
    $result = $conn->query($sql);

    if ($result === false) {
        die("Error fetching products: " . $conn->error);
    }

    // Calculate subtotal and organize product data
    while ($row = $result->fetch_assoc()) {
        if (isset($products_in_cart[$row['product_id']])) {
            $productID = $row['product_id'];
            $products[$productID] = $row;
            $products_in_cart[$productID] += [
                'name' => $row['name'],
                'img' => $row['image_url'],
                'total' => $row['price'] * $products_in_cart[$productID]['quantity']
            ];
            $subtotal += $products_in_cart[$productID]['total'];
        }
    }
}

$conn->close();
$title = "Cart Page";
?>
<!DOCTYPE html>
<html lang="en">

<?php include "inc/head.inc.php"; ?>

<body>
    <?php include "inc/header.inc.php"; ?>
    <?php include "inc/nav.inc.php"; ?>
    <main>
        <div class="cart content-wrapper">
            <h1>Shopping Cart</h1>
            <form action="cart.php" method="post">
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
                                <td colspan="5" style="text-align:center;">You have no products added in your Shopping Cart</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($products_in_cart as $product_id => $cart_item) : ?>
                                <tr>
                                    <td class="img">
                                        <a href="product_details.php?id=<?= $product_id ?>">
                                            <img src="<?= $cart_item['img'] ?>" width="50" height="50" alt="<?= htmlspecialchars($cart_item['name']) ?>">
                                        </a>
                                    </td>
                                    <td>
                                        <a href="product_details.php?id=<?= $product_id ?>"><?= htmlspecialchars($cart_item['name']) ?></a>
                                        <br>
                                        <a href="cart.php?remove=<?= $product_id ?>" class="remove">Remove</a>
                                    </td>
                                    <td class="price">&dollar;<?= number_format($cart_item['price'], 2) ?></td>
                                    <td class="size"><?= htmlspecialchars($cart_item['size']) ?></td>
                                    <td class="color"><?= htmlspecialchars($cart_item['color']) ?></td>
                                    <td class="quantity">
                                        <input type="number" name="quantity-<?= $product_id ?>" value="<?= $cart_item['quantity'] ?>" min="1" max="20" placeholder="Quantity" required>
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
                <a href="product.php" class="cart-back-btn">Back to Products</a>
            </div>
        </div>
    </main>
    <?php include "inc/footer.inc.php"; ?>
    <?php ob_end_flush(); // End output buffering and send output to client 
    ?>
</body>

</html>