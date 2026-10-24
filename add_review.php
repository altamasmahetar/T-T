<?php
session_start();
include 'includes/config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if(isset($_POST['submit_review'])) {

    $user_id = $_SESSION['user_id'];
    $tour_id = intval($_POST['tour_id']);
    $rating = intval($_POST['rating']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    // Booking check (user ne package book kiya ho)
    $checkBooking = mysqli_query($conn,
        "SELECT id FROM bookings 
         WHERE user_id='$user_id' 
         AND tour_id='$tour_id' 
         LIMIT 1");

    if(mysqli_num_rows($checkBooking) > 0) {

        $booking = mysqli_fetch_assoc($checkBooking);
        $booking_id = $booking['id'];

        $query = "INSERT INTO reviews 
        (user_id, tour_id, booking_id, rating, title, comment, status, review_date) 
        VALUES 
        ('$user_id','$tour_id','$booking_id','$rating','$title','$comment','approved',NOW())";

        mysqli_query($conn, $query);

        header("Location: package-detail.php?id=".$tour_id);
        exit();

    } else {

        $_SESSION['review_error'] = "You can review only booked packages.";
        header("Location: package-detail.php?id=".$tour_id);
        exit();

    }
}
?>