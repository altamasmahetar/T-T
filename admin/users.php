<?php
include 'auth.php';
include '../includes/config.php';
redirectIfNotAdmin();

// Check if user has permission to manage users
if(!canManageUsers()) {
    header("Location: index.php");
    exit();
}

// Handle user status update
if(isset($_POST['update_status'])) {
    $user_id = intval($_POST['user_id']);
    $status = sanitize_input($_POST['status']);
    
    $query = "UPDATE users SET is_active = '$status' WHERE id = '$user_id'";
    if(mysqli_query($conn, $query)) {
        $success = "User status updated successfully!";
    } else {
        $error = "Failed to update user status!";
    }
}

// Handle user role update
if(isset($_POST['update_role'])) {
    $user_id = intval($_POST['user_id']);
    $user_role = sanitize_input($_POST['user_role']);
    
    $query = "UPDATE users SET user_role = '$user_role' WHERE id = '$user_id'";
    if(mysqli_query($conn, $query)) {
        $success = "User role updated successfully!";
    } else {
        $error = "Failed to update user role!";
    }
}

// Handle user deletion
if(isset($_POST['delete_user'])) {
    $user_id = intval($_POST['user_id']);
    
    // First, check if user has any bookings
    $check_bookings = "SELECT COUNT(*) as booking_count FROM bookings WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $check_bookings);
    $booking_data = mysqli_fetch_assoc($result);
    
    if($booking_data['booking_count'] > 0) {
        $error = "Cannot delete user! User has existing bookings.";
    } else {
        $query = "DELETE FROM users WHERE id = '$user_id'";
        if(mysqli_query($conn, $query)) {
            $success = "User deleted successfully!";
        } else {
            $error = "Failed to delete user!";
        }
    }
}

// Search functionality
$search = '';
$where_condition = '';

if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search = sanitize_input($_GET['search']);
    $where_condition = " WHERE name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%'";
}

// Get all users with booking counts
$query = "SELECT u.*, 
          COUNT(b.id) as total_bookings,
          SUM(CASE WHEN b.status = 'confirmed' THEN b.total_amount ELSE 0 END) as total_spent
          FROM users u 
          LEFT JOIN bookings b ON u.id = b.user_id
          $where_condition
          GROUP BY u.id 
          ORDER BY u.created_at DESC";

$result = mysqli_query($conn, $query);
$total_users = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - TravelExplorer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            min-height: 100vh;
            color: white;
            position: fixed;
            width: 250px;
        }
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 15px 20px;
            border-bottom: 1px solid #34495e;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: #3498db;
            color: white;
        }
        .main-content {
            margin-left: 250px;
            background: #f8f9fa;
            min-height: 100vh;
        }
        .navbar-admin {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .user-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .user-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-right: 15px;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
        }
        .badge-premium {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: black;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-3 text-center border-bottom border-secondary">
                    <h4><i class="fas fa-plane"></i> Admin Panel</h4>
                    <small class="text-light">TravelExplorer</small>
                </div>
                <div class="p-3 border-bottom border-secondary">
                    <div class="text-center">
                        <i class="fas fa-user-circle fa-2x mb-2"></i>
                        <h6 class="mb-1"><?php echo $_SESSION['admin_name'] ?? 'Admin'; ?></h6>
                        <small class="text-light"><?php echo ucfirst($_SESSION['admin_role'] ?? 'admin'); ?></small>
                    </div>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <?php if(canManageTours()): ?>
                    <a class="nav-link" href="packages.php">
                        <i class="fas fa-suitcase"></i> Tour Packages
                    </a>
                    <?php endif; ?>
                    <?php if(canManageBookings()): ?>
                    <a class="nav-link" href="bookings.php">
                        <i class="fas fa-bookmark"></i> Bookings
                    </a>
                    <?php endif; ?>
                    <?php if(canManageUsers()): ?>
                    <a class="nav-link active" href="users.php">
                        <i class="fas fa-users"></i> Users
                    </a>
                    <a class="nav-link" href="admin-users.php">
                        <i class="fas fa-user-shield"></i> Admin Users
                    </a>
                    <?php endif; ?>
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content p-0">
                <!-- Top Navbar -->
                <nav class="navbar navbar-admin">
                    <div class="container-fluid">
                        <span class="navbar-brand mb-0 h6">
                            <i class="fas fa-users"></i> Manage Users
                        </span>
                        <div class="d-flex">
                            <span class="navbar-text me-3">
                                <i class="fas fa-user"></i> 
                                <?php echo $_SESSION['admin_name'] ?? 'Admin'; ?>
                            </span>
                            <a href="../index.php" class="btn btn-sm btn-outline-primary me-2">
                                <i class="fas fa-external-link-alt"></i> Main Site
                            </a>
                            <a href="logout.php" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </nav>

                <!-- Dashboard Content -->
                <div class="container-fluid p-4">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>User Management</h2>
                        <div class="d-flex">
                            <!-- Search Form -->
                            <form method="GET" class="me-3">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Search users..." value="<?php echo $search; ?>">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <?php if(!empty($search)): ?>
                                    <a href="users.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php if(isset($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="stats-card">
                                <h3><?php echo $total_users; ?></h3>
                                <p>Total Users</p>
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stats-card" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                                <h3>
                                    <?php
                                    $active_users = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE is_active = TRUE");
                                    echo mysqli_fetch_assoc($active_users)['count'];
                                    ?>
                                </h3>
                                <p>Active Users</p>
                                <i class="fas fa-user-check fa-2x"></i>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stats-card" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                                <h3>
                                    <?php
                                    $agents_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE user_role = 'agent'");
                                    echo mysqli_fetch_assoc($agents_count)['count'];
                                    ?>
                                </h3>
                                <p>Travel Agents</p>
                                <i class="fas fa-user-tie fa-2x"></i>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stats-card" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                                <h3>
                                    <?php
                                    $new_users = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE created_at >= CURDATE() - INTERVAL 7 DAY");
                                    echo mysqli_fetch_assoc($new_users)['count'];
                                    ?>
                                </h3>
                                <p>New Users (7 days)</p>
                                <i class="fas fa-user-plus fa-2x"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-list"></i> Users List 
                                <span class="badge bg-primary"><?php echo $total_users; ?> users</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if($total_users > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>User ID</th>
                                            <th>User Info</th>
                                            <th>Contact</th>
                                            <th>Bookings</th>
                                            <th>Total Spent</th>
                                            <th>Status</th>
                                            <th>Role</th>
                                            <th>Joined</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($user = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td>#<?php echo $user['id']; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="user-avatar">
                                                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <strong><?php echo $user['name']; ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo $user['email']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php echo $user['phone'] ?: 'N/A'; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo $user['total_bookings']; ?> bookings</span>
                                            </td>
                                            <td>
                                                <strong>₹<?php echo number_format($user['total_spent'] ?: 0); ?></strong>
                                            </td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 120px;">
                                                        <option value="1" <?php echo $user['is_active'] ? 'selected' : ''; ?>>Active</option>
                                                        <option value="0" <?php echo !$user['is_active'] ? 'selected' : ''; ?>>Inactive</option>
                                                    </select>
                                                    <input type="hidden" name="update_status" value="1">
                                                </form>
                                            </td>
                                            <td>
                                                <?php if(isSuperAdmin()): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <select name="user_role" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 120px;">
                                                        <option value="user" <?php echo $user['user_role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                                        <option value="agent" <?php echo $user['user_role'] == 'agent' ? 'selected' : ''; ?>>Agent</option>
                                                    </select>
                                                    <input type="hidden" name="update_role" value="1">
                                                </form>
                                                <?php else: ?>
                                                <span class="badge <?php echo $user['user_role'] == 'agent' ? 'badge-premium' : 'bg-secondary'; ?>">
                                                    <?php echo ucfirst($user['user_role']); ?>
                                                </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if(isSuperAdmin() && $user['total_bookings'] == 0): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" name="delete_user" class="btn btn-sm btn-danger" 
                                                                onclick="return confirm('Are you sure you want to delete this user?')"
                                                                title="Delete User">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h4>No Users Found</h4>
                                <p class="text-muted">
                                    <?php echo !empty($search) ? 'Try adjusting your search criteria' : 'No users registered yet.'; ?>
                                </p>
                                <?php if(!empty($search)): ?>
                                <a href="users.php" class="btn btn-primary">Clear Search</a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- User Activity Chart Placeholder -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-chart-line"></i> User Registration Trend (Last 30 Days)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="text-center py-4">
                                        <i class="fas fa-chart-bar fa-2x text-muted mb-3"></i>
                                        <p class="text-muted">User registration chart will be displayed here</p>
                                        <small class="text-muted">This would typically show a graph of user registrations over time</small>
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
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="search"]');
            if(searchInput) {
                searchInput.focus();
            }
        });
    </script>
</body>
</html>