<?php
include 'includes/config.php';

if(!isset($_GET['id'])) {
    die("Invalid Invoice ID");
}

$booking_id = intval($_GET['id']);

// Fetch booking + tour data
$query = "SELECT b.*, t.title, t.destination 
          FROM bookings b 
          JOIN tours t ON b.tour_id = t.id 
          WHERE b.id = $booking_id";

$result = mysqli_query($conn, $query);

if(!$result || mysqli_num_rows($result) == 0) {
    die("Invoice not found");
}

$booking = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Invoice #<?php echo $booking['id']; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .invoice-box {
            background: #fff;
            padding: 30px;
            margin: 40px auto;
            max-width: 800px;
            border: 1px solid #ddd;
        }
        .invoice-header {
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
        }
        .total {
            font-size: 20px;
            font-weight: bold;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>

<div class="invoice-box">

    <div class="invoice-header d-flex justify-content-between">
        <div>
            <h3>Travel Agency</h3>
            <p>India</p>
        </div>
        <div>
            <h4>Invoice</h4>
            <p>#<?php echo $booking['id']; ?></p>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col">
            <strong>Booking Date:</strong><br>
            <?php echo date('d M Y', strtotime($booking['created_at'])); ?>
        </div>
        <div class="col">
            <strong>Travel Date:</strong><br>
            <?php echo date('d M Y', strtotime($booking['booking_date'])); ?>
        </div>
    </div>

    <hr>

    <h5>Package Details</h5>
    <p><strong>Package:</strong> <?php echo $booking['title']; ?></p>
    <p><strong>Destination:</strong> <?php echo $booking['destination']; ?></p>
    <p><strong>Travelers:</strong> <?php echo $booking['travelers']; ?></p>

    <hr>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Description</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Tour Package</td>
                <td>₹<?php echo number_format($booking['total_amount']); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="text-end total">
        Total: ₹<?php echo number_format($booking['total_amount']); ?>
    </div>

    <hr>

    <p><strong>Status:</strong> <?php echo ucfirst($booking['status']); ?></p>

    <div class="mt-4 no-print">
        <button onclick="window.print()" class="btn btn-primary">
            🖨 Print
        </button>
        <a href="booking.php" class="btn btn-secondary">Back</a>
    </div>

</div>

</body>
</html>