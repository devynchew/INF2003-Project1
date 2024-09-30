<!DOCTYPE html>
<html lang="en">

<body>
    <?php
    include "inc/head.inc.php";
    include "inc/header.inc.php";
    include "inc/nav.inc.php";

    $config = parse_ini_file('/var/www/private/db-config.ini');
    // Establish MySQL connection using the server connection info from the function
    $servername = $config['servername'];
    $username = $config['username'];
    $password = $config['password'];
    $dbname = $config['dbname'];

    // Create connection
    $connection = mysqli_connect($servername, $username, $password, $dbname);

    // Check connection
    if (!$connection) {
        echo "<script>console.error('Connection failed: " . mysqli_connect_error() . "');</script>";
        die();
    } else {
        echo "<script>console.log('SQL Connected successfully');</script>";
    }
    ?>

    <main class="container">
        <h1 class="mt-2 mb-3">Products</h1>
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Thumbnail</th>
                    <th scope="col">Name</th>
                    <th scope="col">Description</th>
                    <th scope="col">Category</th>
                    <th scope="col">Gender</th>
                    <th scope="col">Price</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT products.product_id, products.name, products.description, categories.name AS category, products.gender, products.image_url, products.price FROM products, categories WHERE products.category_id=categories.category_id";
                $result = mysqli_query($connection, $sql);
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr class="">';
                        echo '<td class="">' . $row['product_id'] . '</td>';
                        echo '<td class=""><img src="' . $row['image_url'] . '" alt="Product Image" style="max-width: 30px;"></td>';
                        echo '<td class="">' . $row['name'] . '</td>';
                        echo '<td class="">' . $row['description'] . '</td>';
                        echo '<td class="">' . $row['category'] . '</td>';
                        echo '<td class="">' . $row['gender'] . '</td>';
                        echo '<td class="">$' . $row['price'] . '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo "No products found";
                }

                ?>
            </tbody>
        </table>
    </main>
    <?php
    include "inc/footer.inc.php";
    ?>
    <script src="script.js"></script>
</body>