<?php
require_once 'session_config.php';
?>

<nav role="navigation" class="navbar navbar-expand-lg navbar-light bg-light">

    <!-- <div class="nav-logo">
        <span class="material-symbols-outlined">laundry</span>
    </div> -->
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <i class="fa-solid fa-bars" style="color: #ffffff;"></i>
    </button>

    <div class="navbar">
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <!-- <li class="nav-item active">
                    <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
                </li> -->
                <li class="nav-item active">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="product_mdb.php">Products</a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="recommendations.php">For You</a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="aboutus.php">About Us</a>
                </li>
            </ul>

        </div>
    </div>

    <div class="navbar-right">

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">

                <!-- Show cart only if user is logged in -->
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php" aria-label="Shopping cart"><i class="fa-solid fa-cart-shopping" style="color: #ffffff;"></i></a>
                    </li>
                <?php endif; ?>

                <!-- If user NOT logged in -->
                <?php if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login_mdb.php" aria-label="User login"><i class="fa-regular fa-user" style="color: #ffffff;"></i></a>
                    </li>

                    <!-- If user is logged in -->
                <?php else: ?>

                    <!-- If user is ADMIN -->
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>

                        <li class="nav-item">
                            <a class="nav-link" href="adminpage.php" aria-label="admin user"><i class="fa-solid fa-screwdriver-wrench" style="color: #ffffff;"></i></a>
                        </li>

                        <!-- If user is NOT ADMIN-->
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="userprofile_mdb.php" aria-label="user profile"><i class="fa-regular fa-user" style="color: #ffffff;"></i></a>
                        </li>
                    <?php endif; ?>


                    <li class="nav-item">
                        <a class="nav-link" href="logout.php" aria-label="Logout"><i class="fa-solid fa-right-from-bracket" style="color: #ffffff;"></i></a>
                    </li>

                <?php endif; ?>

            </ul>
        </div>
    </div>

</nav>