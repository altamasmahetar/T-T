<?php
include 'includes/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$email = sanitize_input($_POST['email']);

// Check user
$result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

if(mysqli_num_rows($result) > 0){

    $otp = rand(100000, 999999);

    mysqli_query($conn, "
        UPDATE users 
        SET reset_token='$otp', reset_expires=DATE_ADD(NOW(), INTERVAL 5 MINUTE)
        WHERE email='$email'
    ");

    // SMTP MAIL
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'altu292005@gmail.com';
        $mail->Password = 'nmehdxaemuhdrngu';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('altu292005@gmail.com', 'Travel Website');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body = "<h2>Your OTP is: $otp</h2><p>Valid for 5 minutes</p>";

        $mail->send();

        header("Location: verify-otp.php?email=$email");
        exit();

    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
    }

} else {
    echo "Email not found!";
}