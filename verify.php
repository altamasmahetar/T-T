<?php
session_start();
include 'includes/config.php';

if(!isset($_SESSION['booking_data'])) {
    header("Location: packages.php");
    exit();
}

$data = $_SESSION['booking_data'];

// ✅ Payment Method Check
$method = $_POST['payment_method'] ?? 'card';

if($method == "card") {

    // ===== CARD DATA =====
    $card = $_POST['card_number'] ?? '';
    $expiry = $_POST['expiry'] ?? '';
    $cvv = $_POST['cvv'] ?? '';

    // Basic validation (server side)
    if(!preg_match('/^[0-9]{16}$/', $card)) {
        die("Invalid Card Number");
    }

    if(!preg_match('/^[0-9]{3}$/', $cvv)) {
        die("Invalid CVV");
    }

    if(empty($expiry)) {
        die("Invalid Expiry Date");
    }

} else {

    // ===== UPI DATA =====
    $upi = $_POST['upi_id'] ?? '';
    $pin = $_POST['upi_pin'] ?? '';

    // UPI validation
    if(!preg_match('/^[a-zA-Z0-9.\-_]{2,}@[a-zA-Z]{2,}$/', $upi)) {
        die("Invalid UPI ID");
    }

    // PIN validation
    if(!preg_match('/^[0-9]{4,6}$/', $pin)) {
        die("Invalid UPI PIN");
    }

}

// ✅ INSERT BOOKING AFTER "PAYMENT SUCCESS"
$query = "INSERT INTO bookings (user_id, tour_id, booking_date, travelers, total_amount, status, payment_method) 
VALUES (
    '{$data['user_id']}',
    '{$data['tour_id']}',
    '{$data['travel_date']}',
    '{$data['travelers']}',
    '{$data['total_amount']}',
    'pending',
    '$method'
)";

if(mysqli_query($conn, $query)) {
    unset($_SESSION['booking_data']);
    header("Location: booking.php?success=1");
    exit();
} else {
    echo "Payment successful but booking failed!";
}
?>