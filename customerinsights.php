<?php
session_start();

$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Check if the user is logged in and if they are an admin or superadmin
// This is a simplified check; your actual implementation might be different
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

// Flag to determine if the current user is a superadmin
$isSuperAdmin = isset($_SESSION['isSuperAdmin']) && $_SESSION['isSuperAdmin'];

// Prepare the SQL query based on user role
$sql = "SELECT user_id, fname, lname, email" . ($isSuperAdmin ? ", is_admin" : "") . " FROM users";

$result = $conn->query($sql);

// Check for errors - This is basic error checking; you might want to handle errors more gracefully
if (!$result) {
    die("Error fetching data: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Insights</title>
</head>
<body>
    <?php
        include "inc/head.inc.php";
        include "inc/header.inc.php";
        include "inc/nav.inc.php";
    ?>
        <div role="main" class="row justify-content-center">
            <div class="col-md-10"> <!-- Adjusted for a maximum width -->
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
                <h2 class="text-center">Customer Insights</h2> <!-- Centered Text -->
                <div class="table-responsive"> <!-- Responsive table wrapper -->
                    <table class="table table-bordered mx-auto"> <!-- Centered Table -->
                        <thead>
                            <tr>
                                <th>Member ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <?php if ($isSuperAdmin): ?>
                                    <th>Admin</th>
                                <?php endif; ?>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                <td><?= htmlspecialchars($row['user_id']) ?></td>
                                <td id="fname_<?= $row['user_id'] ?>"><?= htmlspecialchars($row['fname']) ?></td>
                                <td id="lname_<?= $row['user_id'] ?>"><?= htmlspecialchars($row['lname']) ?></td>
                                <td id="email_<?= $row['user_id'] ?>"><?= htmlspecialchars($row['email']) ?></td>
                                <?php if ($isSuperAdmin): ?>
                                    <td id="is_admin_<?= $row['user_id'] ?>"><?= $row['is_admin'] ? 'Yes' : 'No' ?></td>
                                <?php endif; ?>
                                <td id="actions_<?= $row['user_id'] ?>">
                                <button type="button" onclick="editRow(<?= $row['user_id'] ?>)" class="btn btn-primary">Edit</button>
                                <form action="deleteuser.php" method="post" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display: inline-block;">
                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($row['user_id']) ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                                </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <div class="text-center">
            <a href="adminpage.php" class="btn btn-secondary mr-2">Back to Admin</a>
        </div>
                </div>
            </div>
        </div>
    <?php
        // Close the database connection
        $conn->close();
    ?>
</body>
</html>

