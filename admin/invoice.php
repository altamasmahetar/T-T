<?php
include '../includes/config.php';

if(!isset($_GET['id'])) {
    die("Invalid Booking ID");
}

$id = intval($_GET['id']);

$query = "SELECT b.*, u.name, u.email, u.phone, t.title, t.destination 
          FROM bookings b
          JOIN users u ON b.user_id = u.id
          JOIN tours t ON b.tour_id = t.id
          WHERE b.id = $id";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if(!$data) {
    die("Booking not found");
}

if($data['status'] != 'confirmed') {
    die("Invoice only for confirmed bookings");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Invoice #<?php echo $data['id']; ?></title>
    <style>
        body {
            font-family: Arial;
            background: #f4f4f4;
        }
        .invoice-box {
            width: 700px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
        }
        h2 {
            text-align: center;
        }
        .btn {
            margin-top: 20px;
            padding: 10px 15px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="invoice-box">
    <h2>TravelExplorer Invoice</h2>

    <p><strong>Booking ID:</strong> #<?php echo $data['id']; ?></p>
    <p><strong>Name:</strong> <?php echo $data['name']; ?></p>
    <p><strong>Email:</strong> <?php echo $data['email']; ?></p>
    <p><strong>Phone:</strong> <?php echo $data['phone']; ?></p>

    <hr>

    <p><strong>Tour:</strong> <?php echo $data['title']; ?></p>
    <p><strong>Destination:</strong> <?php echo $data['destination']; ?></p>
    <p><strong>Date:</strong> <?php echo $data['booking_date']; ?></p>
    <p><strong>Travelers:</strong> <?php echo $data['travelers']; ?></p>

    <hr>

    <h3>Total Amount: ₹<?php echo number_format($data['total_amount']); ?></h3>

    <button onclick="window.print()" class="btn">Print / Save PDF</button>
</div>

</body>
</html>