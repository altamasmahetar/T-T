<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirectIfNotAdmin() {

    if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {

        // destroy session
        session_unset();
        session_destroy();

        header("Location: login.php");
        exit();
    }
}

function canManageTours() {
    return isset($_SESSION['admin_role']) && in_array($_SESSION['admin_role'], ['admin', 'manager', 'super_admin']);
}

function canManageBookings() {
    return isset($_SESSION['admin_role']) && in_array($_SESSION['admin_role'], ['admin', 'manager', 'booking_manager', 'super_admin']);
}

function canManageUsers() {
    return isset($_SESSION['admin_role']) && in_array($_SESSION['admin_role'], ['admin', 'super_admin']);
}

// super admin check
function isSuperAdmin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin';
}

// admin check
function isAdmin() {
    return isset($_SESSION['admin_role']) && in_array($_SESSION['admin_role'], ['admin', 'super_admin']);
}

// manage admin users
function canManageAdminUsers() {
    return isSuperAdmin();
}
?>