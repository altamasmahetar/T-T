<?php include 'includes/config.php'; ?>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="form-container">
        <h2 class="text-center mb-4">Verify OTP</h2>

        <form method="POST">
            <input type="hidden" name="email" value="<?php echo $_GET['email']; ?>">

            <div class="mb-3">
                <input type="text" name="otp" class="form-control" placeholder="Enter OTP" required>
            </div>

            <button type="submit" class="btn btn-success w-100">Verify OTP</button>
        </form>

<?php
if(isset($_POST['otp'])){
    $email = $_POST['email'];
    $otp = $_POST['otp'];

    $query = "
    SELECT * FROM users 
    WHERE email='$email' 
    AND reset_token='$otp' 
    AND reset_expires > NOW()
    ";

    $res = mysqli_query($conn, $query);

    if(mysqli_num_rows($res) > 0){
        header("Location: reset-password.php?email=$email");
        exit();
    } else {
        echo "<div class='alert alert-danger mt-3'>Invalid or expired OTP!</div>";
    }
}
?>
    </div>
</div>