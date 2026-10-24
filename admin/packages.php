<?php
include 'auth.php';
include '../includes/config.php';
redirectIfNotAdmin();
//checkAdminPermission();

// Add new package
if(isset($_POST['add_package'])) {
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    $destination = sanitize_input($_POST['destination']);
    $duration = sanitize_input($_POST['duration']);
    $price = sanitize_input($_POST['price']);
    $inclusions = sanitize_input($_POST['inclusions']);
    $exclusions = sanitize_input($_POST['exclusions']);
    $itinerary = sanitize_input($_POST['itinerary']);
    
    // Handle image upload
    $image_url = 'default.jpg';
    if(isset($_FILES['package_image']) && $_FILES['package_image']['error'] == 0) {
        $image_name = time() . '_' . $_FILES['package_image']['name'];
        $image_tmp = $_FILES['package_image']['tmp_name'];
        $image_path = "../assets/images/" . $image_name;
        
        // Check if image file is actual image
        $check = getimagesize($image_tmp);
        if($check !== false) {
            if(move_uploaded_file($image_tmp, $image_path)) {
                $image_url = $image_name;
            }
        }
    }
    
    $query = "INSERT INTO tours (title, description, destination, duration, price, inclusions, exclusions, itinerary, image_url) 
             VALUES ('$title', '$description', '$destination', '$duration', '$price', '$inclusions', '$exclusions', '$itinerary', '$image_url')";
    
    if(mysqli_query($conn, $query)) {
        $success = "Package added successfully!";
    } else {
        $error = "Failed to add package!";
    }
}

// Update package
if(isset($_POST['update_package'])) {
    $package_id = intval($_POST['package_id']);
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    $destination = sanitize_input($_POST['destination']);
    $duration = sanitize_input($_POST['duration']);
    $price = sanitize_input($_POST['price']);
    $inclusions = sanitize_input($_POST['inclusions']);
    $exclusions = sanitize_input($_POST['exclusions']);
    $itinerary = sanitize_input($_POST['itinerary']);
    
    // Handle image upload for update
    $image_update = "";
    if(isset($_FILES['package_image']) && $_FILES['package_image']['error'] == 0) {
        $image_name = time() . '_' . $_FILES['package_image']['name'];
        $image_tmp = $_FILES['package_image']['tmp_name'];
        $image_path = "../assets/images/" . $image_name;
        
        $check = getimagesize($image_tmp);
        if($check !== false) {
            if(move_uploaded_file($image_tmp, $image_path)) {
                $image_update = ", image_url = '$image_name'";
            }
        }
    }
    
    $query = "UPDATE tours SET 
              title = '$title',
              description = '$description', 
              destination = '$destination',
              duration = '$duration',
              price = '$price',
              inclusions = '$inclusions',
              exclusions = '$exclusions',
              itinerary = '$itinerary'
              $image_update
              WHERE id = '$package_id'";
    
    if(mysqli_query($conn, $query)) {
        $success = "Package updated successfully!";
    } else {
        $error = "Failed to update package!";
    }
}

// Delete package
if(isset($_POST['delete_package'])) {
    $package_id = intval($_POST['package_id']);
    
    // Check if package has any bookings
    $check_bookings = "SELECT COUNT(*) as booking_count FROM bookings WHERE tour_id = '$package_id'";
    $result = mysqli_query($conn, $check_bookings);
    $booking_data = mysqli_fetch_assoc($result);
    
    if($booking_data['booking_count'] > 0) {
        $error = "Cannot delete package! There are existing bookings for this package.";
    } else {
        $query = "DELETE FROM tours WHERE id = '$package_id'";
        if(mysqli_query($conn, $query)) {
            $success = "Package deleted successfully!";
        } else {
            $error = "Failed to delete package!";
        }
    }
}

// Get package data for editing
$edit_package = null;
if(isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_query = "SELECT * FROM tours WHERE id = '$edit_id'";
    $edit_result = mysqli_query($conn, $edit_query);
    if(mysqli_num_rows($edit_result) > 0) {
        $edit_package = mysqli_fetch_assoc($edit_result);
    }
}

// Get all packages
$packages = mysqli_query($conn, "SELECT * FROM tours ORDER BY created_at DESC");
$total_packages = mysqli_num_rows($packages);
?>

<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Packages - TravelExplorer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color), #34495e);
            min-height: 100vh;
            color: white;
            position: fixed;
            width: 280px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
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
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .navbar-admin {
            background: white;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 15px 0;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
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
        
        .package-card {
            background: white;
            border-radius: 15px;
            padding: 0;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: none;
            overflow: hidden;
        }
        
        .package-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .package-image {
            height: 180px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            position: relative;
            overflow: hidden;
        }
        
        .package-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .package-content {
            padding: 20px;
        }
        
        .price-tag {
            background: linear-gradient(135deg, var(--success-color), #2ecc71);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
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
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
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
        
        .action-buttons .btn {
            margin: 2px;
            border-radius: 8px;
            font-weight: 500;
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
        
        .image-preview {
            width: 100%;
            height: 200px;
            border: 2px dashed #ddd;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 15px;
        }
        
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
        
        .current-image {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
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
                            <i class="fas fa-suitcase me-2"></i> Tour Packages Management
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
                            <h2 class="mb-1">Tour Packages</h2>
                            <p class="text-muted mb-0">Manage all tour packages and itineraries</p>
                        </div>
                        <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#addPackageModal">
                            <i class="fas fa-plus me-2"></i> Add New Package
                        </button>
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
                            <div class="stats-card">
                                <h3><?php echo $total_packages; ?></h3>
                                <p>Total Packages</p>
                                <i class="fas fa-suitcase fa-2x"></i>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stats-card" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                                <h3>
                                    <?php
                                    $featured_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM tours WHERE featured = TRUE");
                                    echo mysqli_fetch_assoc($featured_count)['count'];
                                    ?>
                                </h3>
                                <p>Featured Packages</p>
                                <i class="fas fa-star fa-2x"></i>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stats-card" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                                <h3>
                                    <?php
                                    $active_tours = mysqli_query($conn, "SELECT COUNT(*) as count FROM tours");
                                    echo mysqli_fetch_assoc($active_tours)['count'];
                                    ?>
                                </h3>
                                <p>Active Tours</p>
                                <i class="fas fa-map-marked-alt fa-2x"></i>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="stats-card" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                                <h3>
                                    ₹<?php
                                    $total_value = mysqli_query($conn, "SELECT SUM(price) as total FROM tours");
                                    echo number_format(mysqli_fetch_assoc($total_value)['total'] ?? 0);
                                    ?>
                                </h3>
                                <p>Total Package Value</p>
                                <i class="fas fa-rupee-sign fa-2x"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Packages Table -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-list me-2 text-primary"></i> All Packages 
                                <span class="badge bg-primary ms-2"><?php echo $total_packages; ?> packages</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if($total_packages > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Package</th>
                                            <th>Destination</th>
                                            <th>Duration</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($package = mysqli_fetch_assoc($packages)): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="../assets/images/<?php echo $package['image_url']; ?>" class="current-image" alt="<?php echo $package['title']; ?>">
                                                    <div>
                                                        <strong class="d-block"><?php echo $package['title']; ?></strong>
                                                        <small class="text-muted"><?php echo substr($package['description'], 0, 50); ?>...</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                                    <?php echo $package['destination']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-primary"><?php echo $package['duration']; ?> Days</span>
                                            </td>
                                            <td>
                                                <span class="price-tag">₹<?php echo number_format($package['price']); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i> Active
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="packages.php?edit_id=<?php echo $package['id']; ?>" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Edit Package">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="package_id" value="<?php echo $package['id']; ?>">
                                                        <button type="submit" name="delete_package" class="btn btn-danger btn-sm" 
                                                                data-bs-toggle="tooltip" title="Delete Package"
                                                                onclick="return confirm('Are you sure you want to delete this package?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                    <a href="../package-detail.php?id=<?php echo $package['id']; ?>" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="View Details" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-suitcase-rolling"></i>
                                <h4>No Packages Found</h4>
                                <p class="text-muted">Get started by adding your first tour package.</p>
                                <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#addPackageModal">
                                    <i class="fas fa-plus me-2"></i> Add First Package
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Package Modal -->
    <div class="modal fade" id="addPackageModal" tabindex="-1" aria-labelledby="addPackageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPackageModalLabel">
                        <i class="fas fa-plus-circle me-2"></i> Add New Tour Package
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Package Title</label>
                                    <input type="text" class="form-control" name="title" placeholder="e.g., Goa Beach Paradise" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Destination</label>
                                    <input type="text" class="form-control" name="destination" placeholder="e.g., Goa, India" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Duration (Days)</label>
                                    <input type="number" class="form-control" name="duration" placeholder="e.g., 4" required min="1">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Price (₹)</label>
                                    <input type="number" class="form-control" name="price" placeholder="e.g., 12500" required min="0">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Image Upload -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Package Image</label>
                            <input type="file" class="form-control" name="package_image" accept="image/*">
                            <small class="text-muted">Upload a beautiful image for your package (JPEG, PNG, JPG)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Package Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Describe the package in detail..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Inclusions</label>
                            <textarea class="form-control" name="inclusions" rows="2" placeholder="Hotel Stay, Breakfast, Airport Transfer, Water Sports" required></textarea>
                            <small class="text-muted">Separate inclusions with commas</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Exclusions</label>
                            <textarea class="form-control" name="exclusions" rows="2" placeholder="Flight Tickets, Lunch, Dinner, Personal Expenses" required></textarea>
                            <small class="text-muted">Separate exclusions with commas</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Itinerary</label>
                            <textarea class="form-control" name="itinerary" rows="4" placeholder="Day 1: Arrival and Beach Visit|Day 2: Water Sports|Day 3: Local Sightseeing|Day 4: Departure" required></textarea>
                            <small class="text-muted">Separate days with | symbol</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_package" class="btn btn-gradient">
                            <i class="fas fa-save me-2"></i> Save Package
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Package Modal -->
    <?php if($edit_package): ?>
    <div class="modal fade show" id="editPackageModal" tabindex="-1" aria-labelledby="editPackageModalLabel" aria-hidden="false" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPackageModalLabel">
                        <i class="fas fa-edit me-2"></i> Edit Tour Package
                    </h5>
                    <a href="packages.php" class="btn-close" aria-label="Close"></a>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="package_id" value="<?php echo $edit_package['id']; ?>">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Package Title</label>
                                    <input type="text" class="form-control" name="title" value="<?php echo $edit_package['title']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Destination</label>
                                    <input type="text" class="form-control" name="destination" value="<?php echo $edit_package['destination']; ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Duration (Days)</label>
                                    <input type="number" class="form-control" name="duration" value="<?php echo $edit_package['duration']; ?>" required min="1">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Price (₹)</label>
                                    <input type="number" class="form-control" name="price" value="<?php echo $edit_package['price']; ?>" required min="0">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Image Upload for Edit -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Package Image</label>
                            <div class="image-preview mb-2">
                                <img src="../assets/images/<?php echo $edit_package['image_url']; ?>" alt="Current Image" id="currentImagePreview">
                            </div>
                            <input type="file" class="form-control" name="package_image" accept="image/*" onchange="previewImage(this, 'editImagePreview')">
                            <small class="text-muted">Upload a new image to replace the current one</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Package Description</label>
                            <textarea class="form-control" name="description" rows="3" required><?php echo $edit_package['description']; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Inclusions</label>
                            <textarea class="form-control" name="inclusions" rows="2" required><?php echo $edit_package['inclusions']; ?></textarea>
                            <small class="text-muted">Separate inclusions with commas</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Exclusions</label>
                            <textarea class="form-control" name="exclusions" rows="2" required><?php echo $edit_package['exclusions']; ?></textarea>
                            <small class="text-muted">Separate exclusions with commas</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Itinerary</label>
                            <textarea class="form-control" name="itinerary" rows="4" required><?php echo $edit_package['itinerary']; ?></textarea>
                            <small class="text-muted">Separate days with | symbol</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <a href="packages.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" name="update_package" class="btn btn-gradient">
                            <i class="fas fa-save me-2"></i> Update Package
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

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

        // Image preview function
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                
                reader.readAsDataURL(file);
            }
        }

        // Auto-show edit modal if edit_id is present
        <?php if($edit_package): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const editModal = new bootstrap.Modal(document.getElementById('editPackageModal'));
            editModal.show();
        });
        <?php endif; ?>
    </script>
</body>
</html>