<?php
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Clear remember me cookie
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/'); // Set cookie to expire in the past
}

// Additionally, clear any other cookies if necessary
// Example: Clearing a cookie named 'other_cookie'
if (isset($_COOKIE['other_cookie'])) {
    setcookie('other_cookie', '', time() - 3600, '/'); // Clear this cookie as well
}

// Redirect to the login page or homepage
header("Location: index.php");
exit;
?>
