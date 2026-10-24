<?php include 'includes/config.php'; ?>
<?php include 'includes/auth.php'; ?>

<?php
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle cancellation request
if(isset($_POST['cancel_booking'])) {
    $booking_id = intval($_POST['booking_id']);
    $cancellation_reason = sanitize_input($_POST['cancellation_reason']);
    
    // Use prepared statement for security
    $booking_query = "SELECT * FROM bookings WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $booking_query);
    mysqli_stmt_bind_param($stmt, "ii", $booking_id, $user_id);
    mysqli_stmt_execute($stmt);
    $booking_result = mysqli_stmt_get_result($stmt);
    
    if($booking_result && mysqli_num_rows($booking_result) > 0) {
        $booking = mysqli_fetch_assoc($booking_result);
        
        // Calculate refund based on cancellation policy
        $travel_date = new DateTime($booking['booking_date']);
        $current_date = new DateTime();
        $days_diff = $current_date->diff($travel_date)->days;
        
        $refund_amount = 0;
        $cancellation_charge = 0;
        
        if($days_diff >= 30) {
            // 90% refund if cancelled 30+ days before travel
            $refund_amount = $booking['total_amount'] * 0.9;
            $cancellation_charge = $booking['total_amount'] * 0.1;
        } elseif($days_diff >= 15) {
            // 50% refund if cancelled 15-29 days before travel
            $refund_amount = $booking['total_amount'] * 0.5;
            $cancellation_charge = $booking['total_amount'] * 0.5;
        } else {
            // No refund if cancelled less than 15 days before travel
            $refund_amount = 0;
            $cancellation_charge = $booking['total_amount'];
        }
        
        // Update booking status using prepared statement
        $update_query = "UPDATE bookings SET 
                        status = 'cancelled',
                        cancellation_reason = ?,
                        refund_amount = ?,
                        cancelled_at = NOW()
                        WHERE id = ? AND user_id = ?";
        
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "sdii", $cancellation_reason, $refund_amount, $booking_id, $user_id);
        
        if(mysqli_stmt_execute($stmt)) {
            $success = "Booking cancelled successfully!";
            if($refund_amount > 0) {
                $success .= " Refund amount: ₹" . number_format($refund_amount);
            }
        } else {
            $error = "Failed to cancel booking. Please try again.";
        }
    } else {
        $error = "Booking not found!";
    }
}

// Handle review submission
if(isset($_POST['submit_review'])) {
    $tour_id = intval($_POST['tour_id']);
    $booking_id = intval($_POST['booking_id']);
    $rating = intval($_POST['rating']);
    $title = sanitize_input($_POST['title']);
    $comment = sanitize_input($_POST['comment']);
    
    // Check if user has already reviewed this booking
    $check_query = "SELECT id FROM reviews WHERE booking_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "ii", $booking_id, $user_id);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($check_result) > 0) {
        $error = "You have already submitted a review for this booking.";
    } else {
        // Insert review using prepared statement
        $insert_query = "INSERT INTO reviews (user_id, tour_id, booking_id, rating, title, comment, status) 
                        VALUES (?, ?, ?, ?, ?, ?, 'pending')";
        
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "iiiiss", $user_id, $tour_id, $booking_id, $rating, $title, $comment);
        
        if(mysqli_stmt_execute($stmt)) {
            $success = "Review submitted successfully! It will be visible after approval.";
        } else {
            $error = "Failed to submit review. Please try again.";
        }
    }
}

// Get ONLY current user's bookings with review status using prepared statement
$query = "SELECT b.*, t.title, t.destination, t.image_url, 
          (SELECT COUNT(*) FROM reviews WHERE booking_id = b.id AND user_id = ?) as has_review,
          (SELECT status FROM reviews WHERE booking_id = b.id AND user_id = ? LIMIT 1) as review_status
          FROM bookings b 
          JOIN tours t ON b.tour_id = t.id 
          WHERE b.user_id = ? 
          ORDER BY b.created_at DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "iii", $user_id, $user_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Check if query was successful
if(!$result) {
    die("Database error: " . mysqli_error($conn));
}
?>

<?php include 'includes/header.php'; ?>

<div class="container section-padding">
    <div class="row">
        <div class="col-12">
            <h1 class="section-title animate-fade-in">My Bookings</h1>
            
            <?php if(isset($success)): ?>
            <div class="alert alert-success animate-fade-in-up">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
            <div class="alert alert-danger animate-fade-in-up">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <?php if(mysqli_num_rows($result) > 0): ?>
            <div class="row">
                <?php while($booking = mysqli_fetch_assoc($result)): 
                    $can_review = ($booking['status'] == 'completed' && $booking['has_review'] == 0);
                    $has_review = $booking['has_review'] > 0;
                    $review_status = $booking['review_status'];
                ?>
                <div class="col-lg-6 mb-4">
                    <div class="booking-card animate-fade-in-up">
                        <div class="booking-header">
                            <div class="booking-id">Booking #<?php echo $booking['id']; ?></div>
                            <div class="booking-status">
                                <span class="status-badge status-<?php echo $booking['status']; ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <img src="assets/images/<?php echo $booking['image_url']; ?>" class="img-fluid rounded" alt="<?php echo $booking['title']; ?>">
                            </div>
                            <div class="col-md-8">
                                <div class="booking-details">
                                    <h5><?php echo $booking['title']; ?></h5>
                                    <p class="text-muted"><?php echo $booking['destination']; ?></p>
                                    
                                    <div class="booking-meta">
                                        <div class="meta-item">
                                            <strong>Travel Date:</strong> <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?>
                                        </div>
                                        <div class="meta-item">
                                            <strong>Travelers:</strong> <?php echo $booking['travelers']; ?> persons
                                        </div>
                                        <div class="meta-item">
                                            <strong>Amount:</strong> ₹<?php echo number_format($booking['total_amount']); ?>
                                        </div>
                                        <div class="meta-item">
                                            <strong>Booked On:</strong> <?php echo date('M d, Y', strtotime($booking['created_at'])); ?>
                                        </div>
                                    </div>
                                    
                                    <?php if($booking['status'] == 'cancelled'): ?>
<div class="cancellation-info mt-3">
    <div class="alert alert-warning">
        <strong>Cancelled:</strong> 
        
        <?php 
        if(!empty($booking['cancelled_at']) 
           && $booking['cancelled_at'] != '0000-00-00 00:00:00') {
            
            echo date('M d, Y', strtotime($booking['cancelled_at']));
        
        } else {
            
            echo "Date not available";
        
        }
        ?>

        <?php if($booking['refund_amount'] > 0): ?>
            <br><strong>Refund Amount:</strong> ₹<?php echo number_format($booking['refund_amount']); ?>
        <?php endif; ?>

        <?php if(!empty($booking['cancellation_reason'])): ?>
            <br><strong>Reason:</strong> <?php echo $booking['cancellation_reason']; ?>
        <?php endif; ?>

    </div>
</div>
<?php endif; ?>
                                    <!-- Review Status -->
                                    <?php if($has_review): ?>
                                    <div class="review-status mt-3">
                                        <div class="alert alert-info">
                                            <i class="fas fa-star"></i> 
                                            <strong>Review Status:</strong> 
                                            <span class="text-capitalize"><?php echo $review_status; ?></span>
                                            <?php if($review_status == 'approved'): ?>
                                                <br><small>Your review is now visible to other users.</small>
                                            <?php elseif($review_status == 'pending'): ?>
                                                <br><small>Your review is awaiting approval.</small>
                                            <?php elseif($review_status == 'rejected'): ?>
                                                <br><small>Your review was not approved.</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
<div class="booking-actions mt-3">
    <!-- Cancellation Button for confirmed bookings -->
    <?php if($booking['status'] == 'confirmed'): ?>
    <button class="btn btn-danger btn-sm" 
            data-bs-toggle="modal" 
            data-bs-target="#cancelModal<?php echo $booking['id']; ?>">
        <i class="fas fa-times"></i> Cancel Booking
    </button>
    <?php endif; ?>
    
    <!-- Review Button for completed bookings without review -->
    <?php if($can_review): ?>
    <button class="btn btn-primary btn-sm" 
            data-bs-toggle="modal" 
            data-bs-target="#reviewModal<?php echo $booking['id']; ?>">
        <i class="fas fa-star"></i> Write Review
    </button>
    <?php endif; ?>
    
    <!-- ✅ INVOICE BUTTON (NEW) -->
    <?php if($booking['status'] == 'confirmed'): ?>
    <a href="invoice.php?id=<?php echo $booking['id']; ?>" 
       target="_blank" class="btn btn-success btn-sm">
        <i class="fas fa-file-invoice"></i> Invoice
    </a>
    <?php endif; ?>
    
    <!-- View Details Button -->
    <a href="package-detail.php?id=<?php echo $booking['tour_id']; ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-eye"></i> View Package
    </a>
</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cancellation Modal -->
                <?php if($booking['status'] == 'confirmed'): ?>
                <div class="modal fade" id="cancelModal<?php echo $booking['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Cancel Booking #<?php echo $booking['id']; ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Cancellation Reason</label>
                                        <textarea class="form-control" name="cancellation_reason" rows="3" required placeholder="Please tell us why you are cancelling..."></textarea>
                                    </div>
                                    
                                    <!-- Cancellation Policy -->
                                    <div class="cancellation-policy alert alert-info">
                                        <h6>Cancellation Policy:</h6>
                                        <?php
                                        $travel_date = new DateTime($booking['booking_date']);
                                        $current_date = new DateTime();
                                        $days_diff = $current_date->diff($travel_date)->days;
                                        ?>
                                        <ul class="small mb-0">
                                            <li>30+ days before travel: 90% refund</li>
                                            <li>15-29 days before travel: 50% refund</li>
                                            <li>Less than 15 days: No refund</li>
                                            <li class="mt-2"><strong>Days remaining: <?php echo $days_diff; ?> days</strong></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" name="cancel_booking" class="btn btn-danger">Confirm Cancellation</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Review Modal -->
                <?php if($can_review): ?>
                <div class="modal fade" id="reviewModal<?php echo $booking['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Write Review</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="tour_id" value="<?php echo $booking['tour_id']; ?>">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Rating *</label>
                                        <div class="rating-input">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                            <input type="radio" id="rating<?php echo $booking['id'].$i; ?>" 
                                                   name="rating" value="<?php echo $i; ?>" required>
                                            <label for="rating<?php echo $booking['id'].$i; ?>">
                                                <i class="fas fa-star"></i>
                                            </label>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Review Title *</label>
                                        <input type="text" class="form-control" name="title" 
                                               required placeholder="Brief summary of your experience">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Your Review *</label>
                                        <textarea class="form-control" name="comment" rows="4" 
                                                  required placeholder="Share your experience in detail..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="text-center animate-fade-in-up">
                <div class="empty-bookings">
                    <i class="fas fa-suitcase-rolling fa-3x mb-3 text-muted"></i>
                    <h3>No Bookings Yet</h3>
                    <p class="text-muted">You haven't made any bookings yet.</p>
                    <a href="packages.php" class="btn btn-primary">Explore Packages</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.rating-input {
    display: flex;
    gap: 5px;
    margin-bottom: 10px;
}

.rating-input input {
    display: none;
}

.rating-input label {
    cursor: pointer;
    color: #ddd;
    font-size: 1.5rem;
    transition: color 0.2s;
}

.rating-input input:checked ~ label,
.rating-input label:hover,
.rating-input label:hover ~ label {
    color: #ffc107;
}

.rating-input input:checked + label {
    color: #ffc107;
}

.booking-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.booking-actions .btn {
    margin-bottom: 5px;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-confirmed {
    background: #d1edff;
    color: #0c5460;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.status-completed {
    background: #d1f7e4;
    color: #155724;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}
</style>

<?php include 'includes/footer.php'; ?>