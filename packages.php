<?php include 'includes/config.php'; ?>
<?php include 'includes/header.php'; ?>

<div class="container section-padding">
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="section-title animate-fade-in">All Tour Packages</h1>
            <p class="section-subtitle">Choose from our carefully curated packages</p>
        </div>
    </div>

    <div class="row">
        <?php
        $query = "SELECT * FROM tours";
        $result = mysqli_query($conn, $query);
        while($row = mysqli_fetch_assoc($result)):
        ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card package-card animate-fade-in-up">
                <div class="card-image">
                    <img src="assets/images/<?php echo $row['image_url']; ?>" class="card-img-top" alt="<?php echo $row['title']; ?>">
                    <div class="card-overlay">
                        <span class="price-tag">₹<?php echo number_format($row['price']); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $row['title']; ?></h5>
                    <p class="card-text"><?php echo substr($row['description'], 0, 100); ?>...</p>
                    <div class="package-meta">
                        <span><i class="fas fa-map-marker-alt"></i> <?php echo $row['destination']; ?></span>
                        <span><i class="fas fa-clock"></i> <?php echo $row['duration']; ?> Days</span>
                    </div>
                    <a href="package-detail.php?id=<?php echo $row['id']; ?>" class="btn btn-primary w-100 mt-3">View Details</a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>