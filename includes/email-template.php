<?php
// Email template functions

function sendBookingConfirmation($booking_id, $user_email, $user_name) {
    $subject = "Booking Confirmation - TravelExplorer";
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .footer { text-align: center; padding: 20px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>TravelExplorer</h2>
                <p>Booking Confirmation</p>
            </div>
            <div class='content'>
                <h3>Dear $user_name,</h3>
                <p>Your booking has been confirmed successfully!</p>
                <p><strong>Booking ID:</strong> #$booking_id</p>
                <p>We're excited to help you create wonderful memories.</p>
                <p>Our team will contact you shortly with more details.</p>
            </div>
            <div class='footer'>
                <p>Thank you for choosing TravelExplorer!</p>
                <p>Contact: info@travelexplorer.com | +91 98765 43210</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: TravelExplorer <noreply@travelexplorer.com>" . "\r\n";
    
    return mail($user_email, $subject, $message, $headers);
}
?>

<?php
// Add to includes/email-template.php

function sendCancellationEmail($booking_id, $user_email, $user_name, $refund_amount, $tour_title) {
    $subject = "Booking Cancellation Confirmation - TravelExplorer";
    
    $refund_text = $refund_amount > 0 ? 
        "Refund Amount: ₹" . number_format($refund_amount) . " (will be processed within 7-10 working days)" : 
        "No refund applicable as per cancellation policy";
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .footer { text-align: center; padding: 20px; color: #666; }
            .refund-info { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>TravelExplorer</h2>
                <p>Booking Cancellation Confirmation</p>
            </div>
            <div class='content'>
                <h3>Dear $user_name,</h3>
                <p>Your booking has been cancelled successfully.</p>
                
                <div class='booking-details'>
                    <p><strong>Booking ID:</strong> #$booking_id</p>
                    <p><strong>Tour Package:</strong> $tour_title</p>
                    <p><strong>Cancellation Date:</strong> " . date('M d, Y') . "</p>
                </div>
                
                <div class='refund-info'>
                    <h4>Refund Information</h4>
                    <p>$refund_text</p>
                </div>
                
                <p>We hope to serve you better in future.</p>
            </div>
            <div class='footer'>
                <p>Thank you for choosing TravelExplorer!</p>
                <p>Contact: support@travelexplorer.com | +91 98765 43210</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: TravelExplorer <noreply@travelexplorer.com>" . "\r\n";
    
    return mail($user_email, $subject, $message, $headers);
}
?>