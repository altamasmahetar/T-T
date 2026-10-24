<?php
session_start();

// UNSET ALL SESSION
$_SESSION = [];

// DESTROY SESSION
session_destroy();

// DELETE SESSION COOKIE 🔥 (VERY IMPORTANT)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// REDIRECT
header("Location: login.php");
exit();
?>