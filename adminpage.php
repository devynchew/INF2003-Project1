<?php
session_start();
if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != 1) {
    header("Location: login.php");
    exit;
}
?>
<?php
        include "inc/head.inc.php";
        include "inc/header.inc.php";
        include "inc/nav.inc.php";
      ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Example of including Bootstrap for styling -->
</head>
<body>
<div role="main" class="container mt-5">
    <h2>Admin Dashboard</h2>
    <div role="complementary" class="list-group mt-4">
        <!-- Manage Users Button -->
        <a href="manageuser.php" class="list-group-item list-group-item-action">
            Manage Users
        </a>

        <!-- Manage Orders Button -->
        <a href="manageorders.php" class="list-group-item list-group-item-action">
            Manage Orders
        </a>

        <!-- Add Product Button -->
        <a href="addproduct.php" class="list-group-item list-group-item-action">
            Add Product
        </a>
    </div>
</div>