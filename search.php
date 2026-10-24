<?php include 'includes/config.php'; ?>
<?php include 'includes/header.php'; ?>

<section class="search-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h2 class="text-white mb-4">Find Your Perfect Tour</h2>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="search-box animate-fade-in-up">
                    <form action="packages.php" method="GET">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="text" class="form-control" name="destination" placeholder="Where do you want to go?">
                            </div>
                            <div class="col-md-3 mb-3">
                                <select class="form-control" name="duration">
                                    <option value="">Any Duration</option>
                                    <option value="1-3">1-3 Days</option>
                                    <option value="4-7">4-7 Days</option>
                                    <option value="8+">8+ Days</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <select class="form-control" name="price_range">
                                    <option value="">Any Price</option>
                                    <option value="0-10000">Under ₹10,000</option>
                                    <option value="10000-20000">₹10,000-20,000</option>
                                    <option value="20000-50000">₹20,000-50,000</option>
                                </select>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-light btn-lg">
                                <i class="fas fa-search"></i> Search Tours
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Get search parameters
$destination = isset($_GET['destination']) ? sanitize_input($_GET['destination']) : '';
$duration = isset($_GET['duration']) ? sanitize_input($_GET['duration']) : '';
$price_range = isset($_GET['price_range']) ? sanitize_input($_GET['price_range']) : '';

// Build query
$query = "SELECT * FROM tours WHERE 1=1";

if (!empty($destination)) {
    $query .= " AND (destination LIKE '%$destination%' OR title LIKE '%$destination%')";
}

if (!empty($duration)) {
    switch($duration) {
        case '1-3':
            $query .= " AND duration BETWEEN 1 AND 3";
            break;
        case '4-7':
            $query .= " AND duration BETWEEN 4 AND 7";
            break;
        case '8+':
            $query .= " AND duration >= 8";
            break;
    }
}

if (!empty($price_range)) {
    switch($price_range) {
        case '0-10000':
            $query .= " AND price <= 10000";
            break;
        case '10000-20000':
            $query .= " AND price BETWEEN 10000 AND 20000";
            break;
        case '20000-50000':
            $query .= " AND price BETWEEN 20000 AND 50000";
            break;
    }
}

$result = mysqli_query($conn, $query);
?>

<div class="container section-padding">
    <div class="row">
        <div class="col-12">
            <h3 class="mb-4">Search Results</h3>
            
            <?php if(mysqli_num_rows($result) > 0): ?>
            <div class="row">
                <?php while($package = mysqli_fetch_assoc($result)): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card package-card animate-fade-in-up">
                        <div class="card-image">
                            <img src="assets/images/<?php echo $package['image_url']; ?>" class="card-img-top" alt="<?php echo $package['title']; ?>">
                            <div class="card-overlay">
                                <span class="price-tag">₹<?php echo number_format($package['price']); ?></span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $package['title']; ?></h5>
                            <p class="card-text"><?php echo substr($package['description'], 0, 100); ?>...</p>
                            <div class="package-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo $package['destination']; ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo $package['duration']; ?> Days</span>
                            </div>
                            <a href="package-detail.php?id=<?php echo $package['id']; ?>" class="btn btn-primary w-100 mt-3">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="text-center animate-fade-in-up">
                <div class="empty-state">
                    <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                    <h4>No Tours Found</h4>
                    <p>Try adjusting your search criteria</p>
                    <a href="packages.php" class="btn btn-primary">View All Packages</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>