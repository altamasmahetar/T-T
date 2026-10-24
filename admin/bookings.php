<?php
include 'auth.php';
include '../includes/config.php';
 redirectIfNotAdmin();
// checkAdminPermission();

// Update booking status
if(isset($_POST['update_status'])) {
    $booking_id = sanitize_input($_POST['booking_id']);
    $status = sanitize_input($_POST['status']);
    
    $query = "UPDATE bookings SET status = '$status' WHERE id = '$booking_id'";
    if(mysqli_query($conn, $query)) {
        $success = "Booking status updated successfully!";
    } else {
        $error = "Failed to update status!";
    }
}

// Search and filter functionality
$search = '';
$status_filter = '';
$where_conditions = [];

if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search = sanitize_input($_GET['search']);
    $where_conditions[] = "(u.name LIKE '%$search%' OR u.email LIKE '%$search%' OR t.title LIKE '%$search%')";
}

if(isset($_GET['status']) && !empty($_GET['status'])) {
    $status_filter = sanitize_input($_GET['status']);
    $where_conditions[] = "b.status = '$status_filter'";
}

$where_clause = '';
if(!empty($where_conditions)) {
    $where_clause = " WHERE " . implode(' AND ', $where_conditions);
}

// Get all bookings with user and tour details
$bookings_query = "SELECT b.*, u.name as user_name, u.email, u.phone, t.title as tour_title, t.destination 
                   FROM bookings b 
                   JOIN users u ON b.user_id = u.id 
                   JOIN tours t ON b.tour_id = t.id 
                   $where_clause
                   ORDER BY b.created_at DESC";

$bookings = mysqli_query($conn, $bookings_query);
$total_bookings = mysqli_num_rows($bookings);

// Get booking statistics
$confirmed_bookings = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE status='confirmed'");
$confirmed_count = mysqli_fetch_assoc($confirmed_bookings)['count'];

$pending_bookings = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE status='pending'");
$pending_count = mysqli_fetch_assoc($pending_bookings)['count'];

$cancelled_bookings = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE status='cancelled'");
$cancelled_count = mysqli_fetch_assoc($cancelled_bookings)['count'];

$total_revenue = mysqli_query($conn, "SELECT SUM(total_amount) as revenue FROM bookings WHERE status='confirmed'");
$revenue = mysqli_fetch_assoc($total_revenue)['revenue'] ?? 0;
?>

<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - TravelExplorer</title>
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
        }
        
        body {
            background: #f8f9fa;
            overflow-x: hidden;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color), #34495e);
            min-height: 100vh;
            color: white;
            position: fixed;
            width: 280px;
            left: 0;
            top: 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 15px 25px;
            border-bottom: 1px solid #34495e;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        .main-content {
            margin-left: 280px;
            padding: 20px;
            min-height: 100vh;
            width: calc(100% - 280px);
        }
        
        .navbar-admin {
            background: white;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 15px 0;
        }
        
        .stats-card {
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-confirmed { background: linear-gradient(135deg, var(--success-color), #2ecc71); }
        .stats-pending { background: linear-gradient(135deg, var(--warning-color), #f1c40f); }
        .stats-cancelled { background: linear-gradient(135deg, var(--danger-color), #c0392b); }
        .stats-revenue { background: linear-gradient(135deg, #667eea, #764ba2); }
        
        .booking-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            border-left: 4px solid var(--secondary-color);
            transition: all 0.3s ease;
        }
        
        .booking-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
        }
        
        .booking-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
            color: white;
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .modal-header .btn-close {
            filter: invert(1);
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
        }
        
        .search-box {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .amount-badge {
            background: linear-gradient(135deg, var(--success-color), #2ecc71);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .sidebar {
                width: 250px;
            }
            .main-content {
                margin-left: 250px;
                width: calc(100% - 250px);
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                width: 100%;
                min-height: auto;
            }
            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar p-0">
        <div class="p-4 text-center border-bottom border-secondary">
            <h4 class="mb-2"><i class="fas fa-plane"></i> Admin Panel</h4>
            <small class="text-light">TravelExplorer</small>
        </div>
        <div class="p-3 border-bottom border-secondary">
            <div class="text-center">
                <i class="fas fa-user-circle fa-2x mb-2 text-warning"></i>
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
            <a class="nav-link active" href="bookings.php">
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
            
            <a class="nav-link" href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">All Bookings</h2>
                <p class="text-muted mb-0">Manage and track all customer bookings</p>
            </div>
            <div class="text-end">
                <span class="badge bg-primary fs-6">Total: <?php echo $total_bookings; ?> bookings</span>
            </div>
        </div>

        <!-- Alerts -->
        <?php if(isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if(isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stats-card stats-confirmed">
                    <h3><?php echo $confirmed_count; ?></h3>
                    <p>Confirmed</p>
                    <i class="fas fa-check-circle fa-2x"></i>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card stats-pending">
                    <h3><?php echo $pending_count; ?></h3>
                    <p>Pending</p>
                    <i class="fas fa-clock fa-2x"></i>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card stats-cancelled">
                    <h3><?php echo $cancelled_count; ?></h3>
                    <p>Cancelled</p>
                    <i class="fas fa-times-circle fa-2x"></i>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card stats-revenue">
                    <h3>₹<?php echo number_format($revenue); ?></h3>
                    <p>Total Revenue</p>
                    <i class="fas fa-rupee-sign fa-2x"></i>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="search-box">
            <form method="GET">
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="search" placeholder="Search by customer name, email, or tour..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" name="status">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-gradient w-100">
                            <i class="fas fa-search me-2"></i> Filter
                        </button>
                    </div>
                </div>
                <?php if(!empty($search) || !empty($status_filter)): ?>
                <div class="mt-3">
                    <a href="bookings.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Clear Filters
                    </a>
                    <small class="text-muted ms-2">
                        <?php echo $total_bookings; ?> results found
                    </small>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Bookings Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2 text-primary"></i> Bookings List
                </h5>
            </div>
            <div class="card-body">
                <?php if($total_bookings > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Booking Details</th>
                                <th>Customer</th>
                                <th>Tour Info</th>
                                <th>Travel Details</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($booking = mysqli_fetch_assoc($bookings)): ?>
                            <tr>
                                <td>
                                    <div>
                                        <strong class="d-block">#<?php echo $booking['id']; ?></strong>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y', strtotime($booking['created_at'])); ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-3">
                                            <?php echo strtoupper(substr($booking['user_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <strong class="d-block"><?php echo htmlspecialchars($booking['user_name']); ?></strong>
                                            <small class="text-muted d-block"><?php echo htmlspecialchars($booking['email']); ?></small>
                                            <small class="text-muted"><?php echo htmlspecialchars($booking['phone']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong class="d-block"><?php echo htmlspecialchars($booking['tour_title']); ?></strong>
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                        <?php echo htmlspecialchars($booking['destination']); ?>
                                    </small>
                                </td>
                                <td>
                                    <div>
                                        <strong class="d-block">
                                            <i class="fas fa-calendar me-1 text-primary"></i>
                                            <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?>
                                        </strong>
                                        <small class="text-muted">
                                            <i class="fas fa-users me-1"></i>
                                            <?php echo $booking['travelers']; ?> travelers
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <span class="amount-badge">
                                        ₹<?php echo number_format($booking['total_amount']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="booking-status status-<?php echo $booking['status']; ?>">
                                        <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                                data-bs-target="#statusModal<?php echo $booking['id']; ?>"
                                                data-bs-toggle="tooltip" title="Update Status">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Send Email">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                   

                                    </div>
                                </td>
                            </tr>

                            <!-- Status Update Modal -->
                            <div class="modal fade" id="statusModal<?php echo $booking['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="fas fa-edit me-2"></i> Update Booking Status
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Booking #<?php echo $booking['id']; ?></label>
                                                    <p class="text-muted mb-3">
                                                        <strong>Customer:</strong> <?php echo htmlspecialchars($booking['user_name']); ?><br>
                                                        <strong>Tour:</strong> <?php echo htmlspecialchars($booking['tour_title']); ?>
                                                    </p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Select Status</label>
                                                    <select class="form-control" name="status" required>
                                                        <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="confirmed" <?php echo $booking['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                        <option value="cancelled" <?php echo $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" name="update_status" class="btn btn-gradient">
                                                    <i class="fas fa-save me-2"></i> Update Status
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-bookmark"></i>
                    <h4>No Bookings Found</h4>
                    <p class="text-muted">
                        <?php echo (!empty($search) || !empty($status_filter)) ? 
                            'Try adjusting your search criteria' : 
                            'No bookings have been made yet.'; ?>
                    </p>
                    <?php if(!empty($search) || !empty($status_filter)): ?>
                    <a href="bookings.php" class="btn btn-gradient">
                        <i class="fas fa-times me-2"></i> Clear Filters
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>