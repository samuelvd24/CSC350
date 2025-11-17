<?php
// logout.php
session_start();

// Clear session array
$_SESSION = [];

// Unset all session variables (extra safe)
session_unset();

// Destroy the session data on the server
session_destroy();

// Also remove the session cookie (extra safe)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Redirect to home or login page
header("Location: index.php");  // or "login.html"
exit;
