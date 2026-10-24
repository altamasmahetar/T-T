<?php 
include 'includes/config.php';

if(isset($_POST['email'], $_POST['password'])) {

    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    mysqli_query($conn, "
    UPDATE users 
    SET password='$password', reset_token=NULL, reset_expires=NULL 
    WHERE email='$email'
    ");

    $success = "Password updated successfully!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Password Updated</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            background: #eef2f5;
            font-family: Arial, sans-serif;
        }

        .center-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
            background: #f7f9fb;
            padding: 40px 60px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            width: 450px;
        }

        .success-box {
            background: #cfe8d5;
            color: #1e6b3a;
            padding: 15px;
            border-radius: 10px;
            border-left: 5px solid #2e8b57;
            font-weight: 500;
        }

        .btn-login {
            margin-top: 30px;
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            background: linear-gradient(135deg, #3a7bd5, #2c5364);
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>

<body>

<div class="center-wrapper">
    <div class="card">

        <?php if(isset($success)): ?>
            <div class="success-box">
                <?php echo $success; ?>
            </div>

            <a href="login.php" class="btn-login">
                Go to Login
            </a>
        <?php endif; ?>

    </div>
</div>

</body>
</html>