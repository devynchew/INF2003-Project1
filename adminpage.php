<!DOCTYPE html>
<html lang="en">

<body>
<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}
?>
<?php
    include "inc/head.inc.php";
    include "inc/header.inc.php";
    include "inc/nav.inc.php";
?>

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

        <!-- Customer Insights Button -->
        <a href="customerinsights.php" class="list-group-item list-group-item-action">
            Customer Insights
        </a>
    </div>
</div>