<?php
ob_start(); // Start output buffering
require_once 'session_config.php';

// Check if the user is not logged in
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    // Redirect to the login page
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    include "inc/head.inc.php";
    ?>
    <meta charset="UTF-8">
    <title>Checkout Page</title>
</head>
<?php
include "inc/header.inc.php";
include "inc/nav.inc.php";

$countries = include('inc/countries.php');

// Establish database connection
function getDatabaseConnection()
{
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        die("Failed to read database config file.");
    }

    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

$conn = getDatabaseConnection();

// Initialize subtotal
$subtotal = 0;
$products_in_cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Checkout logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout']) && !empty($_SESSION['cart'])) {
    // Get user input from the form
    $paymentMethod = $_POST['paymentMethod'] ?? '';
    $firstName = $_POST['firstName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $country = $_POST['country'] ?? '';
    $zip = $_POST['zip'] ?? '';

    // Retrieve member_id from session
    $user_id = $_SESSION['user_id'] ?? null;
    if (is_null($user_id)) {
        die('Please Log In.');
    }

    // Combine address components
    $fullAddress = $address . ', ' . $country . ', ' . $zip;

    $conn->begin_transaction();

    try {
        $totalPrice = $subtotal;

        $cart_details = [];
        foreach ($_SESSION['cart'] as $productID => $details) {
            $quantity = $details['quantity'];
            $productPrice = $details['price'];
            $color = $details['color'];
            $size = $details['size'];
            $totalPrice += $productPrice * $quantity;
            // $stmt = $conn->prepare("UPDATE product SET quantity = quantity - ? WHERE productID = ?");
            // $stmt->bind_param('ii', $quantity, $productID);
            // $stmt->execute();
            // $stmt->close();

            $cart_details[] = [
                'productID' => $productID,
                'quantity' => $quantity,
                'productPrice' => $productPrice,
                'totalPrice' => $totalPrice,
                'color' => $color,
                'size' => $size
            ];
        }

        // Insert into orders table 
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, order_date, order_time) VALUES (?, ?, CURDATE(), CURTIME())");
        $stmt->bind_param('id', $user_id, $totalPrice);
        $stmt->execute();
        $stmt->close();

        $conn->commit();

        $sql_order = "SELECT MAX(order_id) as order_id from orders";
        $result_order = mysqli_query($conn, $sql_order);
        if (mysqli_num_rows($result_order) > 0) {
            while ($row_order = mysqli_fetch_assoc($result_order)) {
                $orderID = $row_order['order_id'];
            }
        }
        
        foreach ($cart_details as $item) {
            $sql_get_ids = "SELECT c.color_id, s.size_id from colors c, sizes s where c.name='$color' AND s.name='$size'";
            $result_ids = mysqli_query($conn, $sql_get_ids);
            if (mysqli_num_rows($result_ids) > 0) {
                while ($row_ids = mysqli_fetch_assoc($result_ids)) {
                    $colorID = $row_ids['color_id'];
                    $sizeID = $row_ids['size_id'];
                }
            }

            $stmt = $conn->prepare("INSERT INTO ordersproduct (order_id, product_id, color_id, size_id, quantity)
            VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('iiiii', $orderID, $item['productID'], $colorID, $sizeID, $quantity);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();

        // Save the cart to the order summary before clearing the cart
        $_SESSION['order_summary'] = [
            'orderID' => $orderID, 
            'totalPrice' => $totalPrice,
            'items' => $_SESSION['cart'],
            'firstName' => $firstName,  
            'lastName' => $lastName,   
            'email' => $email
        ];
        // Clear the cart
        $_SESSION['cart'] = [];

        
        header('Location: order_summary.php');
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        die("An error occurred: " . $e->getMessage());
    }
}
?>
<html>
<div id="checkout-page" class="cart content-wrapper">
    <h1>Checkout</h1>
    <h2>Order Summary</h2>
    <table>
        <thead>
            <tr>
                <td>Product</td>
                <td>Price</td>
                <td>Quantity</td>
                <td>Color</td>
                <td>Size</td>
                <td>Subtotal</td>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($products_in_cart)) : ?>
                <?php foreach ($products_in_cart as $productID => $item) : ?>
                    <tr>
                        <td>
                            <img src="<?= $item['img'] ?>" width="50" height="50" alt="<?= htmlspecialchars($item['name']) ?>">
                            <?= htmlspecialchars($item['name']) ?>
                        </td>
                        <td>&dollar;<?= $item['price'] ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= $item['color'] ?></td>
                        <td><?= $item['size'] ?></td>
                        <td>&dollar;<?= $item['price'] * $item['quantity'] ?></td>
                    </tr>
                <?php
                $subtotal += $item['price'] * $item['quantity'];
                endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="4" style="text-align:center;">You have no products added to checkout.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="subtotal">
        <span class="text">Total</span>
        <span class="price">&dollar;<?= number_format($subtotal, 2) ?></span>
    </div>
    <!--checkout form -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <h3 class="mb-3">Shipping and billing address</h3>
                <form action="checkout.php" method="post">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstName">First name</label>
                            <input type="text" class="form-control" name="firstName" id="firstName" placeholder="" value="<?php echo isset($_SESSION['fname']) ? htmlspecialchars($_SESSION['fname']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastName">Last name</label>
                            <input type="text" class="form-control"name="lastName" id="lastName" placeholder="" value="<?php echo isset($_SESSION['lname']) ? htmlspecialchars($_SESSION['lname']) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="email">Email <span class="text-muted"></span></label>
                        <input type="email" class="form-control" name="email" id="email" placeholder="you@example.com" value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>" required>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="address">Address</label>
                        <input type="text" class="form-control" name="address" id="address" value="<?php echo isset($_SESSION['address']) ? htmlspecialchars($_SESSION['address']) : ''; ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="country">Country</label>
                            <select class="form-control" id="country" name="country" required>
                                <option value="">Choose...</option>
                                <?php foreach ($countries as $code => $name) : ?>
                                    <option value="<?= htmlspecialchars($code) ?>"><?= htmlspecialchars($name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="zip">Zip Code</label>
                            <input type="text" class="form-control" name="zip" id="zip" placeholder="" required>
                        </div>
                    </div>

                    <hr class="mb-4">

                    <h4 class="mb-3">Payment</h4>

                    <div class="col-md-12 mb-3">
                        <p class="font-bold mb-3">Supported Cards</p>
                        <div class="card-logos">
                            <img src="/images/visa_logo.jpg" alt="Visa" class="payment-logo">
                            <img src="/images/mastercard_logo.jpg" alt="Mastercard" class="payment-logo">
                            <img src="/images/amex_logo.jpg" alt="American Express" class="payment-logo">
                            <!-- Hidden input to store the detected card type -->
                            <input type="hidden" name="paymentMethod" id="paymentMethod" value="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cc-name">Name on card</label>
                            <input type="text" class="form-control" id="cc-name" placeholder="" required>
                            <small class="text-muted">Full name as displayed on card</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cc-number">Credit card number</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="cc-number" placeholder="" required>
                                <img id="cardType" src="" alt="Card Type" class="card-logo hidden" />
                            </div>
                            <p class="invalid-message hidden" id="invalidCardNumber">Invalid Card number</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="cc-expiration">Expiration</label>
                            <input type="text" class="form-control" id="cc-expiration" placeholder="MM/YY" required>
                            <p class="invalid-message hidden" id="invalidExpiryDate">Invalid Date</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="cc-cvv">CVV</label>
                            <input maxlength="4" type="text" class="form-control" id="cc-cvv" placeholder="" required>
                            <p class="invalid-message hidden" id="invalidCVV">Invalid CVV</p>
                        </div>
                    </div>
                    <hr class="mb-4">
                    <button class="btn btn-primary btn-lg btn-block" type="submit" name="checkout">Continue to checkout</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Define DOM elements

    // For setting card image in CC number input field
    let imgElem = document.getElementById("cardType");

    // Warning messages
    let cardNumberWarning = document.getElementById("invalidCardNumber");
    let expiryDateWarning = document.getElementById("invalidExpiryDate");
    let cvvWarning = document.getElementById("invalidCVV");

    // For event listeners
    let firstNameInput = document.getElementById("firstName");
    let lastNameInput = document.getElementById("lastName");
    let cardNameInput = document.getElementById("cc-name");
    let cardNumberInput = document.getElementById("cc-number");
    let expiryDateInput = document.getElementById("cc-expiration");
    let cvvInput = document.getElementById("cc-cvv");

    // To store the detected card type
    let detectedCardType = "Unknown";

    // Allow string to be input
    function validateNameInput(event) {
        var regex = /^[a-zA-Z\s-']$/; // Allow letters, spaces, hyphens, and apostrophes
        var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
        if (!regex.test(key)) {
            event.preventDefault();
            return false;
        }
        return true;
    }

    // Allow numbers to be input
    function validateNumberInput(event) {
        var regex = /^[0-9]$/; // Allow only numbers
        var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
        if (!regex.test(key)) {
            event.preventDefault();
            return false;
        }
        return true;
    }

    // Luhn Algorithm for card number validation
    function validateLuhnAlgorithm(cardNumber) {
        let sum = 0;
        let isEven = false;

        for (let i = cardNumber.length - 1; i >= 0; i--) {
            let digit = parseInt(cardNumber.charAt(i), 10);
            if (isEven) {
                digit *= 2;
                if (digit > 9) {
                    digit -= 9;
                }
            }
            sum += digit;
            isEven = !isEven;
        }
        return sum % 10 === 0;
    }


    // Card Type Detection
    function detectCardType(cardNumber) {
        const patterns = {
            visa: /^4[0-9]{12}(?:[0-9]{3})?$/,
            mastercard: /^5[1-5][0-9]{14}$/,
            amex: /^3[47][0-9]{13}$/,
        };

        for (const cardType in patterns) {
            if (patterns[cardType].test(cardNumber)) {
                imgElem.style.display = 'inline';
                imgElem.src = `/images/${cardType}_logo.jpg`;
                detectedCardType = cardType;

                // Set the paymentMethod field value based on the detected card type
                let paymentMethodValue = "";
                switch (cardType) {
                    case 'visa':
                        paymentMethodValue = "Visa";
                        break;
                    case 'mastercard':
                        paymentMethodValue = "Mastercard";
                        break;
                    case 'amex':
                        paymentMethodValue = "American Express";
                        break;
                }
                document.getElementById('paymentMethod').value = paymentMethodValue;

                return cardType;
            }
        }

        // Handle case where no card type matches
        imgElem.style.display = 'none';
        detectedCardType = "Unknown";
        document.getElementById('paymentMethod').value = ""; // Clear the payment method if the card type is unknown
        return "Unknown";
    }

    // CVV/CVC Validation
    function validateCVV(cvv, cardType) {
        let cvvPattern;
        if (cardType === 'amex') {
            cvvPattern = /^[0-9]{4}$/; // American Express cards have 4-digit CVV
        } else {
            cvvPattern = /^[0-9]{3}$/; // Other cards have 3-digit CVV
        }
        return cvvPattern.test(cvv);
    }

    // Expiration Date Validation
    function validateExpirationDate(expirationDate) {
        const [expirationMonth, expirationYear] = expirationDate.split("/").map(Number);
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        const currentMonth = currentDate.getMonth() + 1; // January is 1

        const fullExpirationYear = expirationYear + 2000; // Adjust according to your date format, assuming MM/YY format

        // Check if the month is between 1 and 12 and year is in the future
        const isMonthValid = expirationMonth >= 1 && expirationMonth <= 12;
        const isYearValid = fullExpirationYear >= currentYear;
        const isDateValid = isMonthValid && (
            fullExpirationYear > currentYear ||
            (fullExpirationYear === currentYear && expirationMonth >= currentMonth)
        );

        return isDateValid;
    }



    // Event listeners for real-time validation
    document.addEventListener("DOMContentLoaded", function() {

        // Restrict First and Last names to string
        firstNameInput.addEventListener("keypress", validateNameInput);
        lastNameInput.addEventListener("keypress", validateNameInput);

        // Restrict CC name to string
        cardNameInput.addEventListener("keypress", validateNameInput);

        // Restrict CC number to numbers
        cardNumberInput.addEventListener("keypress", validateNumberInput);

        // Restrict CVV to numbers
        cvvInput.addEventListener("keypress", validateNumberInput);

        // CC name input listener (for Luhn, card type, revalidate CVV in case)
        cardNumberInput.addEventListener("keyup", (e) => {
            const cardNumber = e.target.value.trim();
            const isValidCardNumber = validateLuhnAlgorithm(cardNumber);

            cardNumberWarning.classList.toggle("hidden", isValidCardNumber);
            if (isValidCardNumber) {
                const cardType = detectCardType(cardNumber);
                imgElem.style.display = cardType !== "Unknown" ? 'inline' : 'none';
                if (cardType !== "Unknown") {
                    imgElem.src = `/images/${cardType}_logo.jpg`;
                }

                // Re-validate CVV when card type is determined
                const cvvValue = cvvInput.value.trim();
                if (cvvValue) {
                    const isValidCVV = validateCVV(cvvValue, detectedCardType);
                    cvvWarning.classList.toggle("hidden", isValidCVV);
                }
            } else {
                imgElem.style.display = 'none';
            }
        });

        // Expiry date input listener
        expiryDateInput.addEventListener("input", (e) => {
            let inputValue = e.target.value.replace(/[^0-9]/g, ''); // Remove non-numeric characters
            if (inputValue.length > 2) {
                inputValue = inputValue.substring(0, 2) + '/' + inputValue.substring(2, 4);
            }
            e.target.value = inputValue; // Update the input field with formatted value

            // Validate the date only if it follows the MM/YY format
            if (inputValue.length === 5) {
                const isValidDate = validateExpirationDate(inputValue);
                expiryDateWarning.classList.toggle("hidden", isValidDate);
            } else {
                expiryDateWarning.classList.add("hidden"); // Hide warning if not enough digits yet
            }
        });

        // CVV input listener
        cvvInput.addEventListener("input", (e) => {
            const cvv = e.target.value.trim();
            const isValidCVV = validateCVV(cvv, detectedCardType);
            cvvWarning.classList.toggle("hidden", isValidCVV);
        });



    });
</script>
<?php include "inc/footer.inc.php"; ?>
<?php ob_end_flush(); // End output buffering and send output to client 
?>
</body>

</html>