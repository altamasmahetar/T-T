<?php 
include '../includes/config.php';
include 'auth.php'; //Add authentication
//redirectIfNotAdmin();
?>

<?php
// Handle review status update
if(isset($_POST['update_status'])) {
    $review_id = intval($_POST['review_id']);
    $status = sanitize_input($_POST['status']);
    
    $update_query = "UPDATE reviews SET status = '$status' WHERE id = $review_id";
    
    if(mysqli_query($conn, $update_query)) {
        $success = "Review status updated successfully!";
    } else {
        $error = "Failed to update review status: " . mysqli_error($conn);
    }
}

// Handle review deletion
if(isset($_POST['delete_review'])) {
    $review_id = intval($_POST['review_id']);
    
    $delete_query = "DELETE FROM reviews WHERE id = $review_id";
    
    if(mysqli_query($conn, $delete_query)) {
        $success = "Review deleted successfully!";
    } else {
        $error = "Failed to delete review: " . mysqli_error($conn);
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$tour_filter = isset($_GET['tour_id']) ? intval($_GET['tour_id']) : '';

// Build query with filters
$reviews_query = "SELECT r.*, u.name as user_name, u.email, t.title as tour_title, t.destination 
                  FROM reviews r 
                  JOIN users u ON r.user_id = u.id 
                  JOIN tours t ON r.tour_id = t.id 
                  WHERE 1=1";

if(!empty($status_filter)) {
    $reviews_query .= " AND r.status = '$status_filter'";
}

if(!empty($tour_filter)) {
    $reviews_query .= " AND r.tour_id = $tour_filter";
}

$reviews_query .= " ORDER BY r.review_date DESC";

$reviews_result = mysqli_query($conn, $reviews_query);

// Get all tours for filter
$tours_query = "SELECT id, title FROM tours ORDER BY title";
$tours_result = mysqli_query($conn, $tours_query);

// Statistics
$stats_query = "SELECT 
                COUNT(*) as total_reviews,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_reviews,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_reviews,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_reviews
                FROM reviews";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
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
        
        .review-card {
            border-left: 4px solid #007bff;
            transition: all 0.3s ease;
        }
        .review-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .review-card.pending {
            border-left-color: #ffc107;
        }
        .review-card.approved {
            border-left-color: #28a745;
        }
        .review-card.rejected {
            border-left-color: #dc3545;
        }
        .rating-stars {
            color: #ffc107;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 4px 8px;
        }
        .stats-card {
            border-radius: 10px;
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .action-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
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
                    <a class="nav-link active" href="tours_reviews.php">
                        <i class="fas fa-star"></i> Users Reviews
                    </a>
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-users"></i> Users
                    </a>
                    <a class="nav-link" href="admin-users.php">
                        <i class="fas fa-user-shield"></i> Admin Users
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
                            <i class="fas fa-star me-2"></i> Manage Reviews
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

                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Manage Reviews</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <a href="tours_reviews.php" class="btn btn-sm btn-outline-secondary">All Reviews</a>
                            </div>
                        </div>
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

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card stats-card bg-primary text-white mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Reviews</div>
                                            <div class="h5 mb-0"><?php echo $stats['total_reviews']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-star fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card stats-card bg-warning text-white mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">Pending</div>
                                            <div class="h5 mb-0"><?php echo $stats['pending_reviews']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card stats-card bg-success text-white mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">Approved</div>
                                            <div class="h5 mb-0"><?php echo $stats['approved_reviews']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card stats-card bg-danger text-white mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">Rejected</div>
                                            <div class="h5 mb-0"><?php echo $stats['rejected_reviews']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-times-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Filters</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tour</label>
                                    <select name="tour_id" class="form-select">
                                        <option value="">All Tours</option>
                                        <?php 
                                        // Reset pointer for tours
                                        mysqli_data_seek($tours_result, 0);
                                        while($tour = mysqli_fetch_assoc($tours_result)): 
                                        ?>
                                        <option value="<?php echo $tour['id']; ?>" <?php echo $tour_filter == $tour['id'] ? 'selected' : ''; ?>>
                                            <?php echo $tour['title']; ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                                    <a href="tours_reviews.php" class="btn btn-outline-secondary">Reset</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Reviews List -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Customer Reviews</h5>
                        </div>
                        <div class="card-body">
                            <?php if(mysqli_num_rows($reviews_result) > 0): ?>
                                <div class="row">
                                    <?php while($review = mysqli_fetch_assoc($reviews_result)): ?>
                                    <div class="col-lg-6 mb-4">
                                        <div class="card review-card h-100 <?php echo $review['status']; ?>">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <h6 class="card-title mb-1"><?php echo $review['tour_title']; ?></h6>
                                                        <p class="text-muted small mb-0"><?php echo $review['destination']; ?></p>
                                                    </div>
                                                    <span class="badge status-badge bg-<?php 
                                                        echo $review['status'] == 'approved' ? 'success' : 
                                                             ($review['status'] == 'pending' ? 'warning' : 'danger'); 
                                                    ?>">
                                                        <?php echo ucfirst($review['status']); ?>
                                                    </span>
                                                </div>

                                                <div class="rating-stars mb-2">
                                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                    <?php endfor; ?>
                                                    <small class="text-muted ms-1">(<?php echo $review['rating']; ?>/5)</small>
                                                </div>

                                                <h6 class="mb-2"><?php echo $review['title']; ?></h6>
                                                <p class="card-text"><?php echo nl2br($review['comment']); ?></p>

                                                <div class="reviewer-info mt-3">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong><?php echo $review['user_name']; ?></strong>
                                                            <small class="text-muted d-block"><?php echo $review['email']; ?></small>
                                                        </div>
                                                        <small class="text-muted">
                                                            <?php echo date('M d, Y h:i A', strtotime($review['review_date'])); ?>
                                                        </small>
                                                    </div>
                                                </div>

                                                <div class="action-buttons mt-3">
                                                    <?php if($review['status'] != 'approved'): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                        <input type="hidden" name="status" value="approved">
                                                        <button type="submit" name="update_status" class="btn btn-success btn-sm">
                                                            <i class="fas fa-check"></i> Approve
                                                        </button>
                                                    </form>
                                                    <?php endif; ?>

                                                    <?php if($review['status'] != 'rejected'): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                        <input type="hidden" name="status" value="rejected">
                                                        <button type="submit" name="update_status" class="btn btn-warning btn-sm">
                                                            <i class="fas fa-times"></i> Reject
                                                        </button>
                                                    </form>
                                                    <?php endif; ?>

                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this review?');">
                                                        <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                        <button type="submit" name="delete_review" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-comment-slash fa-4x text-muted mb-3"></i>
                                    <h4>No Reviews Found</h4>
                                    <p class="text-muted">No reviews match your current filters.</p>
                                    <a href="tours_reviews.php" class="btn btn-primary">View All Reviews</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </main>
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
    </script>
</body>
</html>