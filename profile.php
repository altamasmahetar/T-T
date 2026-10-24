<?php
include 'includes/config.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];

/* =========================
   GET USER DATA
========================= */
$user_query = "SELECT * FROM users WHERE id='$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_result);


/* =========================
   PROFILE PHOTO UPDATE
========================= */
if(isset($_POST['update_photo']) && !empty($_FILES['profile_photo']['name'])) {

    $target_dir = "uploads/";

    if(!is_dir($target_dir)){
        mkdir($target_dir, 0777, true);
    }

    $imageFileType = strtolower(pathinfo($_FILES["profile_photo"]["name"], PATHINFO_EXTENSION));
    $allowed_types = ['jpg','jpeg','png','gif','webp'];

    if(in_array($imageFileType, $allowed_types)){

        $photo_name = time() . "_" . basename($_FILES["profile_photo"]["name"]);
        $target_file = $target_dir . $photo_name;

        if(move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)){

            // Delete old photo
            if(!empty($user_data['profile_image']) && file_exists($user_data['profile_image'])){
                unlink($user_data['profile_image']);
            }

            mysqli_query($conn, "UPDATE users SET profile_image='$target_file' WHERE id='$user_id'");

            header("Location: profile.php");
            exit();
        }
    }
}


/* =========================
   PROFILE INFO UPDATE
   (EMAIL REMOVED)
========================= */
if(isset($_POST['update_profile'])) {

    $name  = sanitize_input($_POST['name']);
    $phone = sanitize_input($_POST['phone']);

    if(!preg_match('/^[0-9]{10}$/', $phone)){
        $error = "Phone number must be exactly 10 digits!";
    } 
    else {

        $query = "UPDATE users SET 
                    name='$name',
                    phone='$phone'
                  WHERE id='$user_id'";

        if(mysqli_query($conn, $query)){
            $_SESSION['user_name'] = $name;
            $success = "Profile updated successfully!";
        } 
        else {
            $error = "Failed to update profile!";
        }
    }
}


/* =========================
   PASSWORD CHANGE
========================= */
if(isset($_POST['change_password'])) {

    $current_password = sanitize_input($_POST['current_password']);
    $new_password = sanitize_input($_POST['new_password']);
    $confirm_password = sanitize_input($_POST['confirm_password']);

    $user_pass_query = "SELECT password FROM users WHERE id='$user_id'";
    $user_pass_result = mysqli_query($conn, $user_pass_query);
    $user_pass = mysqli_fetch_assoc($user_pass_result);

    /* CHECK CURRENT PASSWORD */

    if(!password_verify($current_password, $user_pass['password'])) {

        $error_pass = "❌ Current password is incorrect!";

    } 
    else {

        /* CHECK PASSWORD MATCH */

        if($new_password !== $confirm_password){

            $error_pass = "❌ New passwords do not match!";

        } 
        else {

            /* OPTIONAL PASSWORD LENGTH CHECK */

            if(strlen($new_password) < 6){

                $error_pass = "❌ Password must be at least 6 characters!";

            } 
            else {

                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                $update_query = "UPDATE users 
                                 SET password='$hashed_password' 
                                 WHERE id='$user_id'";

                if(mysqli_query($conn, $update_query)){

                    $success_pass = "✅ Password updated successfully!";

                } 
                else {

                    $error_pass = "❌ Failed to update password!";

                }

            }

        }

    }
}

?>

<?php include 'includes/header.php'; ?>


<div class="container section-padding">
<div class="row">


<!-- SIDEBAR -->
<div class="col-lg-4">
<div class="profile-sidebar text-center">

<form method="POST" enctype="multipart/form-data" id="photoForm">

<input type="file"
       name="profile_photo"
       id="photoInput"
       accept="image/*"
       style="display:none;"
       onchange="document.getElementById('photoForm').submit();">

<input type="hidden" name="update_photo" value="1">

<div class="profile-avatar mb-3"
     style="cursor:pointer;"
     onclick="document.getElementById('photoInput').click();">

<?php if(!empty($user_data['profile_image'])): ?>

<img src="<?php echo htmlspecialchars($user_data['profile_image']); ?>"
     width="130"
     height="130"
     style="border-radius:50%; object-fit:cover;">

<?php else: ?>

<i class="fas fa-user-circle fa-6x"></i>

<?php endif; ?>

</div>

<small class="text-muted">Click photo to change</small>

</form>

<hr>

<h4><?php echo htmlspecialchars($user_data['name']); ?></h4>
<p class="text-muted"><?php echo htmlspecialchars($user_data['email']); ?></p>

<?php
$bookings_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE user_id='$user_id'");
$bookings_count = mysqli_fetch_assoc($bookings_count)['count'];
?>

<h5><?php echo $bookings_count; ?></h5>
<span>Bookings</span>

</div>
</div>



<!-- MAIN CONTENT -->
<div class="col-lg-8">

<ul class="nav nav-tabs">

<li class="nav-item">
<a class="nav-link active" data-bs-toggle="tab" href="#profile">Profile Info</a>
</li>

<li class="nav-item">
<a class="nav-link" data-bs-toggle="tab" href="#password">Change Password</a>
</li>

</ul>


<div class="tab-content mt-4">

<!-- PROFILE TAB -->
<div class="tab-pane fade show active" id="profile">

<?php if(isset($success)): ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if(isset($error)): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST">

<div class="row">

<div class="col-md-6 mb-3">
<label>Full Name</label>
<input type="text"
       class="form-control"
       name="name"
       value="<?php echo htmlspecialchars($user_data['name']); ?>"
       required>
</div>

<div class="col-md-6 mb-3">
<label>Email Address</label>

<input type="email"
       class="form-control"
       value="<?php echo htmlspecialchars($user_data['email']); ?>"
       readonly>

</div>

</div>


<div class="mb-3">
<label>Phone Number</label>
<input type="text"
       class="form-control"
       name="phone"
       value="<?php echo htmlspecialchars($user_data['phone']); ?>"
       pattern="[0-9]{10}"
       maxlength="10"
       required>
</div>

<button type="submit"
        name="update_profile"
        class="btn btn-primary">

Update Profile

</button>

</form>

</div>



<!-- PASSWORD TAB -->
<div class="tab-pane fade" id="password">

<?php if(isset($success_pass)): ?>
<div class="alert alert-success"><?php echo $success_pass; ?></div>
<?php endif; ?>

<?php if(isset($error_pass)): ?>
<div class="alert alert-danger"><?php echo $error_pass; ?></div>
<?php endif; ?>

<form method="POST">

<div class="mb-3">
<label>Current Password</label>
<input type="password"
       class="form-control"
       name="current_password"
       required>
</div>

<div class="mb-3">
<label>New Password</label>
<input type="password"
       class="form-control"
       name="new_password"
       required>
</div>

<div class="mb-3">
<label>Confirm New Password</label>
<input type="password"
       class="form-control"
       name="confirm_password"
       required>
</div>

<button type="submit"
        name="change_password"
        class="btn btn-primary">

Change Password

</button>

</form>

</div>

</div>
</div>

</div>
</div>

<?php include 'includes/footer.php'; ?>