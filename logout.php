<?php
require_once 'session_config.php';

// Unset all session variables
$_SESSION = array();

// Destroy the session.
session_destroy();

// Redirect to login page
header("Location: login_mdb.php");
exit;
?>