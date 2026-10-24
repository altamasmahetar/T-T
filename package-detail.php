<?php 
session_start();
include 'includes/config.php'; 
?>

<?php
if(!isset($_GET['id'])) {
    header("Location: packages.php");
    exit();
}

$package_id = intval($_GET['id']);
$query = "SELECT * FROM tours WHERE id = $package_id";
$result = mysqli_query($conn, $query);

if(!$result || mysqli_num_rows($result) == 0) {
    header("Location: packages.php");
    exit();
}

$package = mysqli_fetch_assoc($result);

// BOOK NOW
if(isset($_POST['book_now']) && isset($_SESSION['user_id'])) {

    $user_id = $_SESSION['user_id'];
    $travel_date = $_POST['travel_date'];
    $travelers = intval($_POST['travelers']);

    $today = date('Y-m-d');

    if($travel_date <= $today) {

        $_SESSION['booking_error'] =
        "Please select a future travel date.";

    } else {

        $total_amount =
        $package['price'] * $travelers;

        $_SESSION['booking_data'] = [

            'user_id' => $user_id,
            'tour_id' => $package_id,
            'travel_date' => $travel_date,
            'travelers' => $travelers,
            'total_amount' => $total_amount

        ];

        header("Location: payment.php");
        exit();

    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container section-padding">
<div class="row">

<div class="col-lg-8">

<div class="package-detail-card">

<img src="assets/images/<?php echo $package['image_url']; ?>" 
class="img-fluid rounded">

<h1 class="mt-3">
<?php echo $package['title']; ?>
</h1>

<p>
<?php echo $package['description']; ?>
</p>

</div>


<!-- ⭐ REVIEWS DISPLAY -->

<div class="package-detail-card mt-4">

<h3>Customer Reviews</h3>

<?php

$review_query = "
SELECT r.*, u.name 
FROM reviews r
JOIN users u ON r.user_id = u.id
WHERE r.tour_id='$package_id'
AND r.status='approved'
ORDER BY r.review_date DESC
";

$review_result = mysqli_query($conn,$review_query);

if(mysqli_num_rows($review_result)>0):

while($review=mysqli_fetch_assoc($review_result)):
?>

<div class="border p-3 mb-3 rounded">

<h5><?php echo $review['title']; ?></h5>

<div class="text-warning mb-2">

<?php
for($i=1;$i<=5;$i++) {

if($i <= $review['rating']) {

echo '<i class="fas fa-star"></i>';

}
else {

echo '<i class="far fa-star"></i>';

}

}
?>

</div>

<p><?php echo $review['comment']; ?></p>

<small class="text-muted">

By <?php echo $review['name']; ?> |

<?php echo date(
'd M Y',
strtotime($review['review_date'])
); ?>

</small>

</div>

<?php endwhile; else: ?>

<p>No reviews yet.</p>

<?php endif; ?>

</div>


<!-- ⭐ ADD REVIEW -->

<div class="package-detail-card mt-4">

<h4>Add Your Review</h4>

<?php if(isset($_SESSION['user_id'])): ?>

<form action="add_review.php" method="POST">

<input type="hidden" 
name="tour_id"
value="<?php echo $package_id; ?>">

<div class="mb-3">

<label>Rating</label>

<select name="rating"
class="form-control"
required>

<option value="">Select Rating</option>
<option value="5">⭐⭐⭐⭐⭐</option>
<option value="4">⭐⭐⭐⭐</option>
<option value="3">⭐⭐⭐</option>
<option value="2">⭐⭐</option>
<option value="1">⭐</option>

</select>

</div>

<div class="mb-3">

<label>Title</label>

<input type="text"
name="title"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Comment</label>

<textarea
name="comment"
class="form-control"
rows="4"
required></textarea>

</div>

<button type="submit"
name="submit_review"
class="btn btn-success">

Submit Review

</button>

</form>

<?php else: ?>

<div class="alert alert-info">

Please <a href="login.php">
login
</a> to add review.

</div>

<?php endif; ?>

</div>

</div>


<!-- RIGHT SIDE BOOKING -->

<div class="col-lg-4">

<div class="booking-sidebar">

<div class="price-box text-center">

<h3>
₹<?php echo number_format($package['price']); ?>
</h3>

<p>per person</p>

<small class="text-light">
+ 5% GST applicable
</small>

</div>

<?php
if(isset($_SESSION['booking_error'])) {

echo '<div class="alert alert-danger">'
. $_SESSION['booking_error'] .
'</div>';

unset($_SESSION['booking_error']);

}
?>

<?php if(isset($_SESSION['user_id'])): ?>

<form method="POST">

<div class="mb-3">

<label>Travel Date</label>

<input type="date"
name="travel_date"
class="form-control"
required
min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">

</div>

<div class="mb-3">

<label>Number of Travelers</label>

<select name="travelers"
class="form-control"
id="travelers"
required>

<option value="1">1 Person</option>
<option value="2">2 Persons</option>
<option value="3">3 Persons</option>
<option value="4">4 Persons</option>
<option value="5">5 Persons</option>
<option value="6">6 Persons</option>

</select>

</div>

<div class="mb-3">

<label>Total Amount</label>

<input type="text"
class="form-control"
id="total_amount"
readonly>

</div>

<button type="submit"
name="book_now"
class="btn btn-primary w-100">

Book Now

</button>

</form>

<?php endif; ?>

</div>

</div>

</div>
</div>


<!-- ⭐ FIXED JAVASCRIPT -->

<script>

document.addEventListener("DOMContentLoaded", function() {

var travelers =
document.getElementById("travelers");

var totalInput =
document.getElementById("total_amount");

var basePrice =
<?php echo (int)$package['price']; ?>;

// initial load
updateTotal();

travelers.addEventListener(
"change",
updateTotal
);

function updateTotal() {

var count =
parseInt(travelers.value);

var total =
basePrice * count;

totalInput.value =
"₹" +
total.toLocaleString("en-IN");

}

});

</script>

<?php include 'includes/footer.php'; ?>