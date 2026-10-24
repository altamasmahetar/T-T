<?php include 'includes/config.php'; ?>

<?php
if(isset($_POST['register'])) {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $password = sanitize_input($_POST['password']);
    $confirm_password = sanitize_input($_POST['confirm_password']);
    
    // ✅ Phone Validation (Only numbers & exactly 10 digits)
    if(!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Phone number must be exactly 10 digits and contain only numbers!";
    }
    // Password match check
    elseif($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if email already exists
        $check_query = "SELECT id FROM users WHERE email='$email'";
        $check_result = mysqli_query($conn, $check_query);
        
        if(mysqli_num_rows($check_result) > 0) {
            $error = "Email already registered!";
        } else {

            // ✅ Password hash
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // ✅ Default values
            $role = "user";
            $is_active = 1;

            // ✅ FIXED QUERY (correct columns + variables)
            $query = "INSERT INTO users (name, email, phone, password, user_role, is_active) 
                      VALUES ('$name', '$email', '$phone', '$hashed_password', '$role', '$is_active')";
            
            if(mysqli_query($conn, $query)) {
                header("Location: index.php");
                exit();
            } else {
                $error = "Registration failed! Please try again.";
            }
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="form-container animate-fade-in-up">
        <h2 class="text-center mb-4">Create New Account</h2>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <input type="text" class="form-control" name="name" placeholder="Full Name" required>
            </div>

            <div class="mb-3">
                <input type="email" class="form-control" name="email" placeholder="Email Address" required>
            </div>

            <!-- ✅ Phone Field Fixed -->
            <div class="mb-3">
                <input type="text" 
                       class="form-control" 
                       name="phone" 
                       placeholder="Phone Number"
                       maxlength="10"
                       pattern="[0-9]{10}"
                       required>
            </div>

            <div class="mb-3">
                <input type="password" class="form-control" name="password" placeholder="Password" required>
            </div>

            <div class="mb-3">
                <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" required>
            </div>

            <button type="submit" name="register" class="btn btn-primary w-100">Register</button>
        </form>
        
        <div class="text-center mt-3">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</div>

<!-- ✅ JavaScript Validation -->
<script>
document.querySelector("input[name='phone']").addEventListener("input", function() {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
});
</script>

<?php include 'includes/footer.php'; ?>