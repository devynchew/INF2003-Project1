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

    <main class="manage_product_container">
        <h1 class="mt-2 mb-3">Products</h1>
        <div class="table_container">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col" class="text-nowrap">id</th>
                        <th scope="col" class="text-nowrap">Thumbnail</th>
                        <th scope="col" class="text-nowrap">Name</th>
                        <th scope="col" class="text-nowrap">Description</th>
                        <th scope="col" class="text-nowrap">Price</th>
                        <th scope="col" class="text-nowrap">Category</th>
                        <th scope="col" class="text-nowrap">Available Colors</th>
                        <th scope="col" class="text-nowrap">Gender</th>
                        <th scope="col" class="text-nowrap"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT 
                            p.product_id,
                            p.image_url,
                            p.name AS product_name,
                            p.description,
                            p.price,
                            cat.name AS category,
                            GROUP_CONCAT(c.name ORDER BY c.name ASC SEPARATOR ', ') AS colors,
                            p.gender
                        FROM 
                            products p
                        JOIN 
                            categories cat ON p.category_id = cat.category_id
                        JOIN 
                            productcolors pc ON p.product_id = pc.product_id
                        JOIN 
                            colors c ON pc.color_id = c.color_id
                        GROUP BY 
                            p.product_id;";
                    $result = mysqli_query($connection, $sql);
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<tr>';
                            echo '<td class="text-nowrap">' . $row['product_id'] . '</td>';
                            echo '<td class="text-nowrap"><img src="' . $row['image_url'] . '" alt="Product Image" style="max-width: 30px;"></td>';
                            echo '<td class="text-nowrap">' . $row['product_name'] . '</td>';
                            echo '<td class="text-nowrap">' . $row['description'] . '</td>';
                            echo '<td class="text-nowrap">$' . $row['price'] . '</td>';
                            echo '<td class="text-nowrap">' . $row['category'] . '</td>';
                            echo '<td class="text-nowrap">' . $row['colors'] . '</td>';
                            echo '<td class="text-nowrap">' . $row['gender'] . '</td>';
                            echo '<td class="text-nowrap"><button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal2">Edit</button></td>';
                            echo '</tr>';
                        }
                    } else {
                        echo "No products found";
                    }

                    ?>
                </tbody>
            </table>
        </div>
        <!-- Button trigger modal -->
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
            Launch demo modal
        </button>

        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        ...
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </div>

    </main>
    <?php
    include "inc/footer.inc.php";
    ?>
    <script src="script.js"></script>
</body>