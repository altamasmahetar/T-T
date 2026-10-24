<?php include 'includes/config.php'; ?>

<?php
if(isset($_POST['reset_request'])) {
    $email = sanitize_input($_POST['email']);
    
    // Check if email exists
    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) == 1) {
        $token = bin2hex(random_bytes(50));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));
        
        // Store token in database
        $update_query = "UPDATE users SET reset_token='$token', reset_expires='$expires' WHERE email='$email'";
        if(mysqli_query($conn, $update_query)) {
            $success = "Password reset link has been sent to your email!";
        }
    } else {
        $error = "No account found with this email!";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="form-container animate-fade-in-up">
        <h2 class="text-center mb-4">Reset Your Password</h2>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label>Email Address</label>
                <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
            </div>
            <button type="submit" name="reset_request" class="btn btn-primary w-100">Send Reset Link</button>
        </form>
        
        <div class="text-center mt-3">
            <a href="login.php">Back to Login</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>