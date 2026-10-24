<?php
require('vendor/autoload.php');

use Razorpay\Api\Api;

session_start();

// 🔥 apni REAL keys daal
$api = new Api('rzp_test_ABC123xyz', 'abcd1234secretkey');

$data = $_SESSION['booking_data'];

// Create order
$order = $api->order->create([
    'receipt' => rand(1000,9999),
    'amount' => $data['total_amount'] * 100,
    'currency' => 'INR'
]);

echo json_encode($order);