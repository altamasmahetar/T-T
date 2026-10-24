<?php


// SESSION START
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// PREVENT BROWSER CACHE (important for logout security)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

include '../includes/config.php';
include 'auth.php';

// CHECK ADMIN LOGIN
redirectIfNotAdmin();

// extra safety
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}


// Get additional statistics for the dashboard
$today_bookings = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE DATE(created_at) = CURDATE()");
$today_bookings_count = mysqli_fetch_assoc($today_bookings)['count'];

$weekly_revenue = mysqli_query($conn, "SELECT SUM(total_amount) as revenue FROM bookings WHERE status='confirmed' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$weekly_revenue_amount = mysqli_fetch_assoc($weekly_revenue)['revenue'] ?? 0;

$new_users = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$new_users_count = mysqli_fetch_assoc($new_users)['count'];
?>

<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TravelExplorer</title>
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
        
        .stats-users { 
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .stats-tours { 
            background: linear-gradient(135deg, #f093fb, #f5576c);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .stats-bookings { 
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .stats-revenue { 
            background: linear-gradient(135deg, #43e97b, #38f9d7);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .admin-welcome {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
        }
        
        .admin-welcome::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px 15px 0 0 !important;
            border: none;
            padding: 20px 25px;
        }
        
        .table th {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 15px;
            font-weight: 600;
        }
        
        .table td {
            padding: 15px;
            vertical-align: middle;
            border-color: #f1f2f6;
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover td {
            background: rgba(52, 152, 219, 0.05);
            transform: scale(1.01);
        }
        
        .progress {
            height: 8px;
            border-radius: 10px;
            background: #f1f2f6;
        }
        
        .progress-bar {
            border-radius: 10px;
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
        
        .quick-stats {
            background: rgba(255,255,255,0.9);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            transition: all 0.3s ease;
        }
        
        .stat-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
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
                    <a class="nav-link active" href="index.php">
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

                    <a class="nav-link" href="contact-messages.php">
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
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard Overview
                        </span>
                        <div class="d-flex align-items-center">
                            <span class="navbar-text me-3">
                                <i class="fas fa-user me-1"></i> 
                                <?php echo $_SESSION['admin_name'] ?? 'Admin'; ?>
                            </span>
                            <a href="../index.php" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fas fa-external-link-alt me-1"></i> Main Site
                            </a>
                            <a href="logout.php" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-sign-out-alt me-1"></i> Logout
                            </a>
                        </div>
                    </div>
                </nav>

                <!-- Dashboard Content -->
                <div class="container-fluid p-4">
                    <!-- Welcome Card -->
                    <div class="admin-welcome">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="mb-2">Welcome back, <?php echo $_SESSION['admin_name'] ?? 'Admin'; ?>! 👋</h2>
                                <p class="mb-0 opacity-75">Here's what's happening with your travel business today.</p>
                                <div class="mt-3">
                                    <span class="badge bg-light text-dark me-2">
                                        <i class="fas fa-calendar me-1"></i> <?php echo date('F j, Y'); ?>
                                    </span>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-clock me-1"></i> <?php echo date('h:i A'); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <i class="fas fa-chart-line fa-5x opacity-25 floating"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="stat-item">
                                <div class="stat-number text-primary">
                                    <i class="fas fa-bolt me-2"></i><?php echo $today_bookings_count; ?>
                                </div>
                                <div class="stat-label">Today's Bookings</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stat-item">
                                <div class="stat-number text-success">
                                    <i class="fas fa-rupee-sign me-2"></i><?php echo number_format($weekly_revenue_amount); ?>
                                </div>
                                <div class="stat-label">Weekly Revenue</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stat-item">
                                <div class="stat-number text-warning">
                                    <i class="fas fa-user-plus me-2"></i><?php echo $new_users_count; ?>
                                </div>
                                <div class="stat-label">New Users (7 days)</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stat-item">
                                <div class="stat-number text-info">
                                    <i class="fas fa-star me-2"></i>
                                    <?php
                                    $featured_tours = mysqli_query($conn, "SELECT COUNT(*) as count FROM tours WHERE featured = TRUE");
                                    echo mysqli_fetch_assoc($featured_tours)['count'];
                                    ?>
                                </div>
                                <div class="stat-label">Featured Tours</div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="stats-card stats-users floating">
                                <h3 class="fw-bold">
                                    <?php
                                    $users_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
                                    echo mysqli_fetch_assoc($users_count)['count'];
                                    ?>
                                </h3>
                                <p class="mb-0">Total Users</p>
                                <i class="fas fa-users fa-2x mt-2"></i>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stats-card stats-tours floating" style="animation-delay: 0.2s;">
                                <h3 class="fw-bold">
                                    <?php
                                    $tours_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM tours");
                                    echo mysqli_fetch_assoc($tours_count)['count'];
                                    ?>
                                </h3>
                                <p class="mb-0">Tour Packages</p>
                                <i class="fas fa-suitcase fa-2x mt-2"></i>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stats-card stats-bookings floating" style="animation-delay: 0.4s;">
                                <h3 class="fw-bold">
                                    <?php
                                    $bookings_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings");
                                    echo mysqli_fetch_assoc($bookings_count)['count'];
                                    ?>
                                </h3>
                                <p class="mb-0">Total Bookings</p>
                                <i class="fas fa-bookmark fa-2x mt-2"></i>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stats-card stats-revenue floating" style="animation-delay: 0.6s;">
                                <h3 class="fw-bold">
                                    ₹<?php
                                    $revenue_result = mysqli_query($conn, "SELECT SUM(total_amount) as revenue FROM bookings WHERE status='confirmed'");
                                    $revenue = mysqli_fetch_assoc($revenue_result)['revenue'] ?? 0;
                                    echo number_format($revenue);
                                    ?>
                                </h3>
                                <p class="mb-0">Total Revenue</p>
                                <i class="fas fa-rupee-sign fa-2x mt-2"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity & Statistics -->
                    <div class="row mt-4">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-clock me-2"></i> Recent Bookings
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $recent_bookings = mysqli_query($conn, 
                                        "SELECT b.*, u.name, t.title, t.destination 
                                         FROM bookings b 
                                         JOIN users u ON b.user_id = u.id 
                                         JOIN tours t ON b.tour_id = t.id 
                                         ORDER BY b.created_at DESC LIMIT 6");
                                    ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Booking ID</th>
                                                    <th>Customer</th>
                                                    <th>Tour</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while($booking = mysqli_fetch_assoc($recent_bookings)): ?>
                                                <tr>
                                                    <td>
                                                        <strong>#<?php echo $booking['id']; ?></strong>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                                                <i class="fas fa-user text-white" style="font-size: 0.8rem;"></i>
                                                            </div>
                                                            <div>
                                                                <strong class="d-block"><?php echo $booking['name']; ?></strong>
                                                                <small class="text-muted"><?php echo $booking['destination']; ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?php echo $booking['title']; ?></td>
                                                    <td>
                                                        <strong class="text-success">₹<?php echo number_format($booking['total_amount']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php 
                                                            echo $booking['status'] == 'confirmed' ? 'success' : 
                                                                 ($booking['status'] == 'pending' ? 'warning' : 
                                                                 ($booking['status'] == 'cancelled' ? 'danger' : 'secondary')); 
                                                        ?>">
                                                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                                            <?php echo ucfirst($booking['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?php echo date('M j, g:i A', strtotime($booking['created_at'])); ?>
                                                        </small>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-chart-pie me-2"></i> Booking Statistics
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $confirmed_bookings = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE status='confirmed'");
                                    $confirmed = mysqli_fetch_assoc($confirmed_bookings)['count'];
                                    
                                    $pending_bookings = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE status='pending'");
                                    $pending = mysqli_fetch_assoc($pending_bookings)['count'];
                                    
                                    $cancelled_bookings = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE status='cancelled'");
                                    $cancelled = mysqli_fetch_assoc($cancelled_bookings)['count'];
                                    
                                    $total_bookings = $confirmed + $pending + $cancelled;
                                    ?>
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fw-semibold">Confirmed</span>
                                            <span><?php echo $confirmed; ?> (<?php echo $total_bookings > 0 ? round(($confirmed/$total_bookings)*100) : 0; ?>%)</span>
                                        </div>
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-success" style="width: <?php echo $total_bookings > 0 ? ($confirmed/$total_bookings)*100 : 0; ?>%"></div>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fw-semibold">Pending</span>
                                            <span><?php echo $pending; ?> (<?php echo $total_bookings > 0 ? round(($pending/$total_bookings)*100) : 0; ?>%)</span>
                                        </div>
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-warning" style="width: <?php echo $total_bookings > 0 ? ($pending/$total_bookings)*100 : 0; ?>%"></div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fw-semibold">Cancelled</span>
                                            <span><?php echo $cancelled; ?> (<?php echo $total_bookings > 0 ? round(($cancelled/$total_bookings)*100) : 0; ?>%)</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-danger" style="width: <?php echo $total_bookings > 0 ? ($cancelled/$total_bookings)*100 : 0; ?>%"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Summary Stats -->
                                    <div class="mt-4 p-3 bg-light rounded">
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="text-success">
                                                    <div class="h5 mb-1"><?php echo $confirmed; ?></div>
                                                    <small>Confirmed</small>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-warning">
                                                    <div class="h5 mb-1"><?php echo $pending; ?></div>
                                                    <small>Pending</small>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-danger">
                                                    <div class="h5 mb-1"><?php echo $cancelled; ?></div>
                                                    <small>Cancelled</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add floating animation to stats cards
        document.addEventListener('DOMContentLoaded', function() {
            const statsCards = document.querySelectorAll('.stats-card');
            statsCards.forEach((card, index) => {
                card.style.animationDelay = (index * 0.2) + 's';
            });
            
            // Update current time every minute
            function updateTime() {
                const now = new Date();
                const timeElement = document.querySelector('.badge.bg-light.text-dark:last-child');
                if (timeElement) {
                    const timeString = now.toLocaleTimeString('en-US', { 
                        hour: 'numeric', 
                        minute: '2-digit',
                        hour12: true 
                    });
                    timeElement.innerHTML = `<i class="fas fa-clock me-1"></i> ${timeString}`;
                }
            }
            
            setInterval(updateTime, 60000);
        });
    </script>
</body>
</html>