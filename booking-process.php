<?php 
include 'includes/config.php'; 
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if(isset($_POST['tour_id'])) {
    $user_id = $_SESSION['user_id'];
    $tour_id = sanitize_input($_POST['tour_id']);
    $travel_date = sanitize_input($_POST['travel_date']);
    $travelers = sanitize_input($_POST['travelers']);
    
    // Get tour details
    $tour_query = "SELECT * FROM tours WHERE id = $tour_id";
    $tour_result = mysqli_query($conn, $tour_query);
    
    if(mysqli_num_rows($tour_result) > 0) {
        $tour = mysqli_fetch_assoc($tour_result);
        $total_amount = $tour['price'] * $travelers;

        // ✅ STORE DATA IN SESSION (instead of booking)
        $_SESSION['booking_data'] = [
            'user_id' => $user_id,
            'tour_id' => $tour_id,
            'travel_date' => $travel_date,
            'travelers' => $travelers,
            'total_amount' => $total_amount
        ];

        // ✅ REDIRECT TO PAYMENT PAGE
        header("Location: payment.php");
        exit();

    } else {
        $error = "Tour package not found!";
    }
} else {
    header("Location: packages.php");
    exit();
}
?>

<?php include 'includes/header.php'; ?>

<div class="container section-padding">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="booking-confirmation animate-fade-in-up">
                
                <?php if(isset($error)): ?>
                <div class="alert alert-danger text-center">
                    <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                    <h3>Booking Failed</h3>
                    <p><?php echo $error; ?></p>
                    <div class="mt-3">
                        <a href="packages.php" class="btn btn-primary me-2">Browse Packages</a>
                        <a href="javascript:history.back()" class="btn btn-outline-primary">Go Back</a>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>