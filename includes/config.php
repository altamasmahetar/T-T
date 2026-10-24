<?php

// START SESSION (har page ke liye)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ERROR REPORTING (DEV MODE)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// PREVENT BACK BUTTON LOGIN ISSUE 🔥
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// DATABASE CONNECTION
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'travel_website';

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// SET CHARSET
mysqli_set_charset($conn, 'utf8');

// SANITIZE INPUT
function sanitize_input($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

// CHECK LOGIN
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// REDIRECT IF NOT LOGIN
function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// REDIRECT IF ALREADY LOGIN
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header("Location: my-bookings.php"); // 🔥 FIXED (important)
        exit();
    }
}
?>