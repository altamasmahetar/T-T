<?php
include 'includes/config.php';

if(isset($_GET['tour_id'])) {

$tour_id = intval($_GET['tour_id']);

$review_query = "
SELECT r.*, u.name 
FROM reviews r
JOIN users u ON r.user_id = u.id
WHERE r.tour_id='$tour_id'
AND r.status='approved'
ORDER BY r.review_date DESC
";

$review_result = mysqli_query($conn,$review_query);

if(mysqli_num_rows($review_result)>0) {

while($review=mysqli_fetch_assoc($review_result)) {

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

By <?php echo $review['name']; ?>

|

<?php echo date('d M Y',
strtotime($review['review_date'])); ?>

</small>

</div>

<?php
}

}
else {

echo "<p>No reviews yet.</p>";

}

}
?>