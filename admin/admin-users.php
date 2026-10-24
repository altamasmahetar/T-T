<?php
include 'auth.php';
include '../includes/config.php';
redirectIfNotAdmin();

// Only super admin can manage admin users
//if(!isSuperAdmin()) {
  //  header("Location: index.php");
    //exit();
//}

// Add new admin user
if(isset($_POST['add_admin'])) {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = sanitize_input($_POST['password']);
    $full_name = sanitize_input($_POST['full_name']);
    $role = sanitize_input($_POST['role']);
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $query = "INSERT INTO admin_users (username, email, password, full_name, role) 
              VALUES ('$username', '$email', '$hashed_password', '$full_name', '$role')";
    
    if(mysqli_query($conn, $query)) {
        $success = "Admin user added successfully!";
    } else {
        $error = "Failed to add admin user!";
    }
}

// Update admin status
if(isset($_POST['update_status'])) {
    $admin_id = intval($_POST['admin_id']);
    $is_active = intval($_POST['is_active']);
    
    $query = "UPDATE admin_users SET is_active = '$is_active' WHERE id = '$admin_id'";
    if(mysqli_query($conn, $query)) {
        $success = "Admin status updated successfully!";
    } else {
        $error = "Failed to update admin status!";
    }
}

// Delete admin user (cannot delete self)
if(isset($_POST['delete_admin'])) {
    $admin_id = intval($_POST['admin_id']);
    
    if($admin_id == $_SESSION['admin_id']) {
        $error = "You cannot delete your own account!";
    } else {
        $query = "DELETE FROM admin_users WHERE id = '$admin_id'";
        if(mysqli_query($conn, $query)) {
            $success = "Admin user deleted successfully!";
        } else {
            $error = "Failed to delete admin user!";
        }
    }
}

// Get all admin users
$admin_users = mysqli_query($conn, "SELECT * FROM admin_users ORDER BY created_at DESC");
$total_admins = mysqli_num_rows($admin_users);

// Get admin statistics
$super_admins = mysqli_query($conn, "SELECT COUNT(*) as count FROM admin_users WHERE role = 'super_admin'");
$super_admin_count = mysqli_fetch_assoc($super_admins)['count'];

$admins = mysqli_query($conn, "SELECT COUNT(*) as count FROM admin_users WHERE role = 'admin'");
$admin_count = mysqli_fetch_assoc($admins)['count'];

$managers = mysqli_query($conn, "SELECT COUNT(*) as count FROM admin_users WHERE role = 'manager'");
$manager_count = mysqli_fetch_assoc($managers)['count'];

$active_admins = mysqli_query($conn, "SELECT COUNT(*) as count FROM admin_users WHERE is_active = TRUE");
$active_admin_count = mysqli_fetch_assoc($active_admins)['count'];
?>

<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admin Users - TravelExplorer</title>
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
            --orange-color: #e67e22;
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
        
        .stats-super { 
            background: linear-gradient(135deg, var(--danger-color), #c0392b);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .stats-admin { 
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .stats-manager { 
            background: linear-gradient(135deg, var(--warning-color), #f39c12);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .stats-active { 
            background: linear-gradient(135deg, var(--success-color), #27ae60);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .admin-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.4s ease;
        }
        
        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            border-color: rgba(52, 152, 219, 0.3);
        }
        
        .admin-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 2rem;
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4);
            margin: 0 auto 20px;
            transition: all 0.3s ease;
            border: 4px solid white;
        }
        
        .admin-avatar:hover {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 12px 30px rgba(52, 152, 219, 0.6);
        }
        
        .role-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .role-super { 
            background: linear-gradient(135deg, var(--danger-color), #c0392b);
            color: white;
        }
        
        .role-admin { 
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            color: white;
        }
        
        .role-manager { 
            background: linear-gradient(135deg, var(--warning-color), #f39c12);
            color: white;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-active { 
            background: linear-gradient(135deg, var(--success-color), #2ecc71);
            color: white;
        }
        
        .status-inactive { 
            background: linear-gradient(135deg, #95a5a6, #7f8c8d);
            color: white;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.4s ease;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }
        
        .btn-gradient:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.6);
            color: white;
        }
        
        .form-control {
            border: 2px solid rgba(0,0,0,0.1);
            border-radius: 15px;
            padding: 12px 20px;
            transition: all 0.4s ease;
            background: rgba(255,255,255,0.8);
            font-weight: 500;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.3rem rgba(52, 152, 219, 0.15);
            background: white;
            transform: translateY(-2px);
        }
        
        .table th {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 20px 15px;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table td {
            padding: 20px 15px;
            vertical-align: middle;
            border-color: rgba(0,0,0,0.05);
            background: rgba(255,255,255,0.8);
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover td {
            background: rgba(52, 152, 219, 0.05);
            transform: scale(1.01);
        }
        
        .action-btn {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .action-btn:hover {
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
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
                            <i class="fas fa-user-shield me-2"></i> Admin Users Management
                        </span>
                        <div class="d-flex align-items-center">
                            <span class="navbar-text me-3">
                                <i class="fas fa-user me-1"></i> 
                                <?php echo $_SESSION['admin_name'] ?? 'Admin'; ?>
                            </span>
                            <a href="../index.php" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fas fa-external-link-alt me-1"></i> Main Site
                            </a>
                        </div>
                    </div>
                </nav>

                <!-- Dashboard Content -->
                <div class="container-fluid p-4">
                    <!-- Header Section -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="mb-1 fw-bold">Admin Users</h2>
                            <p class="text-muted mb-0">Manage system administrators and their permissions</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary fs-6 px-3 py-2">
                                <i class="fas fa-users me-1"></i> Total: <?php echo $total_admins; ?> admins
                            </span>
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
                            <div class="stats-card stats-super floating">
                                <h3 class="fw-bold"><?php echo $super_admin_count; ?></h3>
                                <p class="mb-0">Super Admins</p>
                                <i class="fas fa-crown fa-2x mt-2"></i>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stats-card stats-admin floating" style="animation-delay: 0.2s;">
                                <h3 class="fw-bold"><?php echo $admin_count; ?></h3>
                                <p class="mb-0">Admins</p>
                                <i class="fas fa-user-tie fa-2x mt-2"></i>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stats-card stats-manager floating" style="animation-delay: 0.4s;">
                                <h3 class="fw-bold"><?php echo $manager_count; ?></h3>
                                <p class="mb-0">Managers</p>
                                <i class="fas fa-user-cog fa-2x mt-2"></i>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stats-card stats-active floating" style="animation-delay: 0.6s;">
                                <h3 class="fw-bold"><?php echo $active_admin_count; ?></h3>
                                <p class="mb-0">Active</p>
                                <i class="fas fa-user-check fa-2x mt-2"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Add Admin User Card -->
                    <div class="admin-card">
                        <div class="card-header bg-transparent border-0 pb-3">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="fas fa-user-plus me-2 text-success"></i> Add New Admin User
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Username</label>
                                            <input type="text" class="form-control" name="username" placeholder="Enter unique username" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Email Address</label>
                                            <input type="email" class="form-control" name="email" placeholder="Enter email address" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Full Name</label>
                                            <input type="text" class="form-control" name="full_name" placeholder="Enter full name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Role</label>
                                            <select class="form-control" name="role" required>
                                                <option value="admin">Admin</option>
                                                <option value="manager">Manager</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Password</label>
                                    <input type="password" class="form-control" name="password" placeholder="Enter secure password" required>
                                    <small class="text-muted">Use a strong password with mix of characters</small>
                                </div>
                                <button type="submit" name="add_admin" class="btn btn-gradient">
                                    <i class="fas fa-user-plus me-2"></i> Add Admin User
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Admin Users List -->
                    <div class="admin-card">
                        <div class="card-header bg-transparent border-0 pb-3">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="fas fa-list me-2 text-primary"></i> Admin Users List
                                <span class="badge bg-primary ms-2 fs-6"><?php echo $total_admins; ?></span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if($total_admins > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Admin</th>
                                            <th>Contact Info</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Last Login</th>
                                            <th>Joined</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($admin = mysqli_fetch_assoc($admin_users)): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="admin-avatar me-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                                        <?php echo strtoupper(substr($admin['full_name'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <strong class="d-block"><?php echo $admin['full_name']; ?></strong>
                                                        <small class="text-muted">@<?php echo $admin['username']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong class="d-block"><?php echo $admin['email']; ?></strong>
                                                    <small class="text-muted">ID: #<?php echo $admin['id']; ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="role-badge role-<?php echo $admin['role']; ?>">
                                                    <i class="fas fa-<?php echo $admin['role'] == 'super_admin' ? 'crown' : ($admin['role'] == 'admin' ? 'user-tie' : 'user-cog'); ?> me-1"></i>
                                                    <?php echo ucfirst($admin['role']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                                    <select name="is_active" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 120px;">
                                                        <option value="1" <?php echo $admin['is_active'] ? 'selected' : ''; ?>>Active</option>
                                                        <option value="0" <?php echo !$admin['is_active'] ? 'selected' : ''; ?>>Inactive</option>
                                                    </select>
                                                    <input type="hidden" name="update_status" value="1">
                                                </form>
                                            </td>
                                            <td>
                                                <small class="<?php echo $admin['last_login'] ? 'text-success' : 'text-muted'; ?>">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?php echo $admin['last_login'] ? date('M d, Y H:i', strtotime($admin['last_login'])) : 'Never logged in'; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y', strtotime($admin['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-warning action-btn" data-bs-toggle="tooltip" title="Edit Admin">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php if($admin['id'] != $_SESSION['admin_id']): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                                        <button type="submit" name="delete_admin" class="btn btn-sm btn-danger action-btn" 
                                                                data-bs-toggle="tooltip" title="Delete Admin"
                                                                onclick="return confirm('Are you sure you want to delete this admin user?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                    <?php else: ?>
                                                    <button class="btn btn-sm btn-secondary action-btn" data-bs-toggle="tooltip" title="Cannot delete yourself" disabled>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
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
                                <i class="fas fa-user-shield fa-4x text-muted mb-3"></i>
                                <h4>No Admin Users Found</h4>
                                <p class="text-muted">Start by adding your first admin user.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
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