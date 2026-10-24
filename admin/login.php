<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include '../includes/config.php';

// Redirect if already logged in
if(isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

// Handle admin login
if(isset($_POST['admin_login'])) {

    // 🔧 FIX 1: sanitize हटाकर trim use
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM admin_users 
              WHERE username = '$username' 
              AND is_active = 1";

    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) == 1) {

        $admin = mysqli_fetch_assoc($result);

        $stored_password = $admin['password'];

        // 🔧 FIX 2: Stable password verification
        if(password_verify($password, $stored_password)) {

            // Update last login
            $update_query = "UPDATE admin_users 
                             SET last_login = NOW() 
                             WHERE id = ".$admin['id'];

            mysqli_query($conn, $update_query);

            session_regenerate_id(true);

            // Set admin session
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['admin_name'] = $admin['full_name'];

            header("Location: index.php");
            exit();

        } 
        else {

            $error = "Invalid username or password!";

        }

    } 
    else {

        $error = "Invalid username or password!";

    }

}
?>

<!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Login - TravelExplorer</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
body {
background: linear-gradient(135deg,#667eea 0%,#764ba2 100%);
min-height:100vh;
display:flex;
align-items:center;
}

.login-container {
background:white;
border-radius:15px;
box-shadow:0 10px 30px rgba(0,0,0,0.3);
overflow:hidden;
max-width:400px;
width:100%;
}

.login-header {
background:linear-gradient(135deg,#2c3e50,#3498db);
color:white;
padding:30px 20px;
text-align:center;
}

.login-body {
padding:30px;
}

.btn-admin {
background:linear-gradient(135deg,#2c3e50,#3498db);
border:none;
color:white;
padding:12px;
border-radius:10px;
font-weight:600;
}

.demo-credentials .card {
backdrop-filter:blur(10px);
border:1px solid rgba(255,255,255,0.2);
}
</style>
</head>

<body>

<div class="container">
<div class="row justify-content-center">

<div class="col-md-6 col-lg-4">

<div class="login-container">

<div class="login-header">

<i class="fas fa-lock fa-3x mb-3"></i>

<h3>Admin Panel</h3>

<p class="mb-0">
TravelExplorer Administration
</p>

</div>

<div class="login-body">

<?php if(isset($error)): ?>

<div class="alert alert-danger alert-dismissible fade show">

<i class="fas fa-exclamation-circle"></i>

<?php echo $error; ?>

<button type="button"
class="btn-close"
data-bs-dismiss="alert"></button>

</div>

<?php endif; ?>

<form method="POST" id="loginForm">

<div class="mb-3">

<label class="form-label">
Username
</label>

<div class="input-group">

<span class="input-group-text">
<i class="fas fa-user"></i>
</span>

<input type="text"
class="form-control"
name="username"
placeholder="Enter username"
required
autocomplete="username">

</div>

</div>

<div class="mb-3">

<label class="form-label">
Password
</label>

<div class="input-group">

<span class="input-group-text">
<i class="fas fa-lock"></i>
</span>

<input type="password"
class="form-control"
name="password"
placeholder="Enter password"
required
autocomplete="current-password">

</div>

</div>

<button type="submit"
name="admin_login"
class="btn btn-admin w-100">

<i class="fas fa-sign-in-alt"></i>

Login to Admin Panel

</button>

</form>

</div>

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

document.addEventListener('DOMContentLoaded', function() {

var alert = document.querySelector('.alert');

if(alert) {

setTimeout(function() {

var bsAlert = new bootstrap.Alert(alert);

bsAlert.close();

}, 5000);

}

if(window.history.replaceState) {

window.history.replaceState(null,null,window.location.href);

}

});

</script>

</body>
</html>