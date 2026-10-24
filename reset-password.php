<?php include 'includes/config.php'; ?>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="form-container">
        <h2 class="text-center mb-4">Reset Password</h2>

        <form method="POST" action="update-password.php">
            <input type="hidden" name="email" value="<?php echo $_GET['email']; ?>">

            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="New Password" required>
            </div>

            <button type="submit" class="btn btn-danger w-100">Update Password</button>
        </form>
    </div>
</div>