<?php
include 'auth.php';
include '../includes/config.php';
redirectIfNotAdmin();

// Mark all as read
if(isset($_POST['mark_all_read'])) {
    $query = "UPDATE admin_notifications SET is_read = TRUE";
    mysqli_query($conn, $query);
    $success = "All notifications marked as read!";
}

// Mark single as read
if(isset($_GET['mark_read'])) {
    $notification_id = intval($_GET['mark_read']);
    $query = "UPDATE admin_notifications SET is_read = TRUE WHERE id = '$notification_id'";
    mysqli_query($conn, $query);
}

// Get notifications
$notifications = mysqli_query($conn, "SELECT * FROM admin_notifications ORDER BY created_at DESC");
$total_notifications = mysqli_num_rows($notifications);
$unread_notifications = mysqli_query($conn, "SELECT COUNT(*) as count FROM admin_notifications WHERE is_read = FALSE");
$unread_count = mysqli_fetch_assoc($unread_notifications)['count'];
?>