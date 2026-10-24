<?php include 'includes/config.php'; ?>

<?php
// Handle contact form submission
if(isset($_POST['send_message'])) {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $subject = sanitize_input($_POST['subject']);
    $message = sanitize_input($_POST['message']);
    
    // Insert into contact_messages table
    $query = "INSERT INTO contact_messages (name, email, subject, message) 
              VALUES ('$name', '$email', '$subject', '$message')";
    
    if(mysqli_query($conn, $query)) {
        // Create admin notification
        $notification_query = "INSERT INTO admin_notifications (type, title, message, reference_id) 
                              VALUES ('contact', 'New Contact Message', 'You have received a new contact message from $name', LAST_INSERT_ID())";
        mysqli_query($conn, $notification_query);
        
        $success = "Your message has been sent successfully! We'll get back to you soon.";
    } else {
        $error = "Failed to send message. Please try again.";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container section-padding">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="text-center mb-5">
                <h1 class="section-title animate-fade-in">Contact Us</h1>
                <p class="section-subtitle">Get in touch with our travel experts</p>
            </div>

            <?php if(isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show animate-fade-in-up" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show animate-fade-in-up" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-6">
                    <div class="contact-info animate-fade-in-up">
                        <h4>Get In Touch</h4>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h5>Address</h5>
                                <p>123 Travel Street, Mumbai, India 400001</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <h5>Phone</h5>
                                <p>+91 98765 43210</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h5>Email</h5>
                                <p>info@travelexplorer.com</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h5>Working Hours</h5>
                                <p>Mon - Sun: 9:00 AM - 8:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="contact-form animate-fade-in-up delay-1">
                        <h4>Send Message</h4>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Your Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Your Email</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Subject</label>
                                <input type="text" class="form-control" name="subject" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Your Message</label>
                                <textarea class="form-control" name="message" rows="5" required></textarea>
                            </div>
                            <button type="submit" name="send_message" class="btn btn-primary w-100">
                                <i class="fas fa-paper-plane me-2"></i> Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>