<?php include 'includes/config.php'; ?>
<?php include 'includes/auth.php'; ?>

<?php
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle review submission
if(isset($_POST['submit_review'])) {
    $tour_id = intval($_POST['tour_id']);
    $booking_id = intval($_POST['booking_id']);
    $rating = intval($_POST['rating']);
    $title = sanitize_input($_POST['title']);
    $comment = sanitize_input($_POST['comment']);
    
    // Check if user has already reviewed this booking
    $check_query = "SELECT id FROM reviews WHERE booking_id = '$booking_id' AND user_id = '$user_id'";
    $check_result = mysqli_query($conn, $check_query);
    
    if(mysqli_num_rows($check_result) > 0) {
        $error = "You have already submitted a review for this booking.";
    } else {
        // Insert review
        $query = "INSERT INTO reviews (user_id, tour_id, booking_id, rating, title, comment, status) 
                  VALUES ('$user_id', '$tour_id', '$booking_id', '$rating', '$title', '$comment', 'pending')";
        
        if(mysqli_query($conn, $query)) {
            $success = "Thank you for your review! It will be visible after approval.";
        } else {
            $error = "Failed to submit review. Please try again. Error: " . mysqli_error($conn);
        }
    }
}

// Get user's bookings that can be reviewed
// SIMPLIFIED: Show ALL confirmed bookings for now (remove date restriction)
$bookings_query = "SELECT b.*, t.title, t.destination, t.image_url,
                  (SELECT COUNT(*) FROM reviews WHERE booking_id = b.id AND user_id = b.user_id) as has_review
                   FROM bookings b 
                   JOIN tours t ON b.tour_id = t.id 
                   WHERE b.user_id = '$user_id' 
                   AND b.status = 'confirmed' 
                   ORDER BY b.booking_date DESC";

$bookings_result = mysqli_query($conn, $bookings_query);

// Check for query errors
if(!$bookings_result) {
    die("Database error in bookings query: " . mysqli_error($conn));
}

// Get user's submitted reviews
$reviews_query = "SELECT r.*, t.title as tour_title, t.destination 
                  FROM reviews r 
                  JOIN tours t ON r.tour_id = t.id 
                  WHERE r.user_id = '$user_id' 
                  ORDER BY r.review_date DESC";

$reviews_result = mysqli_query($conn, $reviews_query);

// Check for query errors
if(!$reviews_result) {
    die("Database error in reviews query: " . mysqli_error($conn));
}
?>

<?php include 'includes/header.php'; ?>

<div class="container section-padding">
    <div class="row">
        <div class="col-12">
            <h1 class="section-title animate-fade-in">My Reviews</h1>
            
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
            
            <!-- Debug Information (remove after testing) -->
            <div class="alert alert-info animate-fade-in-up">
                <h6>Debug Information:</h6>
                <p><strong>User ID:</strong> <?php echo $user_id; ?></p>
                <p><strong>Bookings Found:</strong> <?php echo mysqli_num_rows($bookings_result); ?></p>
                <?php 
                // Reset pointer and show booking details
                mysqli_data_seek($bookings_result, 0);
                while($debug = mysqli_fetch_assoc($bookings_result)): 
                ?>
                <p><small>Booking #<?php echo $debug['id']; ?>: <?php echo $debug['title']; ?> (<?php echo $debug['booking_date']; ?>) - Has Review: <?php echo $debug['has_review'] ? 'Yes' : 'No'; ?></small></p>
                <?php endwhile; ?>
            </div>
            
            <!-- Bookings Available for Review -->
            <div class="mb-5">
                <h3 class="mb-4">Bookings Available for Review</h3>
                
                <?php 
                // Reset pointer for main display
                mysqli_data_seek($bookings_result, 0);
                $available_bookings = 0;
                ?>
                
                <?php while($booking = mysqli_fetch_assoc($bookings_result)): ?>
                    <?php if(!$booking['has_review']): ?>
                    <?php $available_bookings++; ?>
                    <div class="col-lg-6 mb-4">
                        <div class="booking-card animate-fade-in-up">
                            <div class="row">
                                <div class="col-md-4">
                                    <img src="assets/images/<?php echo $booking['image_url']; ?>" 
                                         class="img-fluid rounded" 
                                         alt="<?php echo $booking['title']; ?>">
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
                                                <strong>Status:</strong> 
                                                <span class="badge bg-success">Confirmed</span>
                                            </div>
                                        </div>
                                        
                                        <div class="booking-actions mt-3">
                                            <button class="btn btn-primary btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#reviewModal<?php echo $booking['id']; ?>">
                                                <i class="fas fa-star"></i> Write Review
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Review Modal -->
                    <div class="modal fade" id="reviewModal<?php echo $booking['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Write Review for <?php echo $booking['title']; ?></h5>
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
                
                <?php if($available_bookings == 0): ?>
                <div class="text-center animate-fade-in-up">
                    <div class="empty-state">
                        <i class="fas fa-calendar-check fa-3x mb-3 text-muted"></i>
                        <h4>No Bookings Available for Review</h4>
                        <p class="text-muted">You don't have any bookings that can be reviewed yet, or you've already reviewed all your bookings.</p>
                        <a href="packages.php" class="btn btn-primary">Explore Packages</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- My Submitted Reviews -->
            <div>
                <h3 class="mb-4">My Submitted Reviews</h3>
                
                <?php if(mysqli_num_rows($reviews_result) > 0): ?>
                <div class="row">
                    <?php while($review = mysqli_fetch_assoc($reviews_result)): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="review-card animate-fade-in-up">
                            <div class="review-header">
                                <h5><?php echo $review['tour_title']; ?></h5>
                                <div class="review-rating">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'active' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            
                            <div class="review-meta">
                                <span class="review-date">
                                    <?php echo date('M d, Y', strtotime($review['review_date'])); ?>
                                </span>
                                <span class="review-status status-<?php echo $review['status']; ?>">
                                    <?php echo ucfirst($review['status']); ?>
                                </span>
                            </div>
                            
                            <h6 class="review-title"><?php echo $review['title']; ?></h6>
                            <p class="review-comment"><?php echo $review['comment']; ?></p>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="text-center animate-fade-in-up">
                    <div class="empty-state">
                        <i class="fas fa-star fa-3x mb-3 text-muted"></i>
                        <h4>No Reviews Submitted</h4>
                        <p class="text-muted">You haven't submitted any reviews yet.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
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

.review-card {
    background: #fff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 15px;
}

.review-header h5 {
    margin: 0;
    flex: 1;
}

.review-rating {
    color: #ffc107;
}

.review-rating .fa-star {
    margin-right: 2px;
}

.review-rating .fa-star.active {
    color: #ffc107;
}

.review-rating .fa-star:not(.active) {
    color: #ddd;
}

.review-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    font-size: 0.9rem;
}

.review-date {
    color: #6c757d;
}

.review-status {
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.review-status.status-pending {
    background: #fff3cd;
    color: #856404;
}

.review-status.status-approved {
    background: #d1edff;
    color: #0c5460;
}

.review-status.status-rejected {
    background: #f8d7da;
    color: #721c24;
}

.review-title {
    font-weight: 600;
    margin-bottom: 10px;
    color: #333;
}

.review-comment {
    color: #555;
    line-height: 1.6;
    margin: 0;
}

.empty-state {
    padding: 40px 20px;
    text-align: center;
}

.empty-state .fas {
    color: #6c757d;
    margin-bottom: 20px;
}

.booking-card {
    background: #fff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
}

.booking-meta {
    font-size: 0.9rem;
}

.meta-item {
    margin-bottom: 5px;
}

.booking-actions {
    display: flex;
    gap: 8px;
}
</style>

<?php include 'includes/footer.php'; ?>