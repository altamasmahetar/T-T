<?php
include 'auth.php';
include '../includes/config.php';
redirectIfNotAdmin();

// Mark message as read
if(isset($_GET['mark_read'])) {
    $message_id = intval($_GET['mark_read']);
    $query = "UPDATE contact_messages SET status = 'read' WHERE id = '$message_id'";
    mysqli_query($conn, $query);
}

// Delete message
if(isset($_POST['delete_message'])) {
    $message_id = intval($_POST['message_id']);
    $query = "DELETE FROM contact_messages WHERE id = '$message_id'";
    mysqli_query($conn, $query);
    $success = "Message deleted successfully!";
}

// Get all contact messages
$messages = mysqli_query($conn, "SELECT * FROM contact_messages ORDER BY created_at DESC");
$total_messages = mysqli_num_rows($messages);
$unread_messages = mysqli_query($conn, "SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'");
$unread_count = mysqli_fetch_assoc($unread_messages)['count'];
?>

<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - TravelExplorer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #17a2b8;
            --purple-color: #9b59b6;
            --teal-color: #1abc9c;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color), #34495e);
            min-height: 100vh;
            color: white;
            position: fixed;
            width: 280px;
            box-shadow: 2px 0 20px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 15px 25px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            transition: all 0.4s ease;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }
        
        .sidebar .nav-link::before {
            content: '';
            position: absolute;
            left: -100%;
            top: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            transition: all 0.4s ease;
            z-index: -1;
        }
        
        .sidebar .nav-link:hover::before,
        .sidebar .nav-link.active::before {
            left: 0;
        }
        
        .sidebar .nav-link:hover, 
        .sidebar .nav-link.active {
            color: white;
            transform: translateX(8px);
            box-shadow: -5px 0 15px rgba(52, 152, 219, 0.3);
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 12px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover i,
        .sidebar .nav-link.active i {
            transform: scale(1.1);
        }
        
        .main-content {
            margin-left: 280px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }
        
        .navbar-admin {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            padding: 15px 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .message-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-left: 4px solid #3498db;
            transition: all 0.3s ease;
        }
        
        .message-card.unread {
            border-left-color: #e74c3c;
            background: #fffafa;
        }
        
        .message-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .badge-unread {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
        }
        
        .stats-card {
            color: white;
            padding: 30px 25px;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stats-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            transition: all 0.6s ease;
        }
        
        .stats-card:hover::before {
            transform: rotate(45deg) translate(50%, 50%);
        }
        
        .stats-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-4 text-center border-bottom border-secondary">
                    <h4 class="mb-2"><i class="fas fa-plane floating"></i> Admin Panel</h4>
                    <small class="text-light">TravelExplorer</small>
                </div>
                <div class="p-3 border-bottom border-secondary">
                    <div class="text-center">
                        <i class="fas fa-user-circle fa-2x mb-2 text-warning pulse"></i>
                        <h6 class="mb-1"><?php echo $_SESSION['admin_name'] ?? 'Admin'; ?></h6>
                        <small class="text-light"><?php echo ucfirst($_SESSION['admin_role'] ?? 'admin'); ?></small>
                    </div>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link" href="packages.php">
                        <i class="fas fa-suitcase"></i> Tour Packages
                    </a>
                    <a class="nav-link" href="bookings.php">
                        <i class="fas fa-bookmark"></i> Bookings
                    </a>
                    <a class="nav-link" href="tours_reviews.php">
                        <i class="fas fa-star"></i> Users Reviews
                    </a>
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-users"></i> Users
                    </a>
                    <a class="nav-link" href="admin-users.php">
                        <i class="fas fa-user-shield"></i> Admin Users
                    </a>
                    <a class="nav-link active" href="contact-messages.php">
                        <i class="fas fa-envelope"></i> Contact Messages
                    </a>
                    
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content p-0">
                <!-- Top Navbar -->
                <nav class="navbar navbar-admin">
                    <div class="container-fluid px-4">
                        <span class="navbar-brand mb-0 h4">
                            <i class="fas fa-envelope me-2"></i> Contact Messages
                        </span>
                        <div class="d-flex align-items-center">
                            <span class="navbar-text me-3">
                                <i class="fas fa-user me-1"></i> 
                                <?php echo $_SESSION['admin_name'] ?? 'Admin'; ?>
                            </span>
                            <a href="../index.php" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fas fa-external-link-alt me-1"></i> Main Site
                            </a>
                            <a href="../logout.php" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-sign-out-alt me-1"></i> Logout
                            </a>
                        </div>
                    </div>
                </nav>

                <!-- Dashboard Content -->
                <div class="container-fluid p-4">
                    <!-- Header Section -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="mb-1">Contact Messages</h2>
                            <p class="text-muted mb-0">Manage customer inquiries and feedback</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary fs-6 p-2">Total: <?php echo $total_messages; ?></span>
                            <?php if($unread_count > 0): ?>
                            <span class="badge badge-unread fs-6 p-2 ms-2">Unread: <?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-4 col-md-6">
                            <div class="stats-card" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                                <h3 class="fw-bold"><?php echo $total_messages; ?></h3>
                                <p class="mb-0">Total Messages</p>
                                <i class="fas fa-envelope fa-2x mt-2"></i>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="stats-card" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                                <h3 class="fw-bold"><?php echo $unread_count; ?></h3>
                                <p class="mb-0">Unread Messages</p>
                                <i class="fas fa-envelope-open fa-2x mt-2"></i>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="stats-card" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                                <h3 class="fw-bold"><?php echo $total_messages - $unread_count; ?></h3>
                                <p class="mb-0">Read Messages</p>
                                <i class="fas fa-check-circle fa-2x mt-2"></i>
                            </div>
                        </div>
                    </div>

                    <?php if(isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <?php if($total_messages > 0): ?>
                    <div class="row">
                        <?php while($message = mysqli_fetch_assoc($messages)): ?>
                        <div class="col-lg-6">
                            <div class="message-card <?php echo $message['status'] == 'unread' ? 'unread' : ''; ?>">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="mb-1"><?php echo $message['name']; ?></h5>
                                        <p class="text-muted mb-0"><?php echo $message['email']; ?></p>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?php echo $message['status'] == 'unread' ? 'danger' : 'success'; ?>">
                                            <?php echo ucfirst($message['status']); ?>
                                        </span>
                                        <small class="text-muted d-block mt-1">
                                            <?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                                
                                <h6 class="text-primary mb-3"><?php echo $message['subject']; ?></h6>
                                <p class="mb-3"><?php echo nl2br($message['message']); ?></p>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <?php if($message['status'] == 'unread'): ?>
                                        <a href="?mark_read=<?php echo $message['id']; ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-check me-1"></i> Mark Read
                                        </a>
                                        <?php endif; ?>
                                        <a href="mailto:<?php echo $message['email']; ?>?subject=Re: <?php echo urlencode($message['subject']); ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-reply me-1"></i> Reply
                                        </a>
                                    </div>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                        <button type="submit" name="delete_message" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Are you sure you want to delete this message?')">
                                            <i class="fas fa-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-envelope fa-4x text-muted mb-3"></i>
                        <h4>No Messages Yet</h4>
                        <p class="text-muted">No contact messages have been received yet.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Add floating animation to stats cards
        document.addEventListener('DOMContentLoaded', function() {
            const statsCards = document.querySelectorAll('.stats-card');
            statsCards.forEach((card, index) => {
                card.style.animationDelay = (index * 0.2) + 's';
            });
        });
    </script>
</body>
</html>