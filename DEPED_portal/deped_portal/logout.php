<?php
// logout.php - Handles the logout process

// Initialize session
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: admin_login.php");
exit;
?>  