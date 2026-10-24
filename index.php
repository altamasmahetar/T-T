<?php include 'includes/config.php'; ?>
<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center min-vh-100">
            <div class="col-lg-6">
                <h1 class="hero-title animate-fade-in-up">Discover Amazing Places</h1>
                <p class="hero-subtitle animate-fade-in-up delay-1">Explore the world with our curated tour packages. Best prices guaranteed!</p>
                <div class="hero-buttons animate-fade-in-up delay-2">
                    <a href="packages.php" class="btn btn-primary btn-lg me-3">View Packages</a>
                    <a href="#features" class="btn btn-outline-light btn-lg">Learn More</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image animate-float">
                    <img src="assets/images/travel-hero.png" alt="Travel" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Search Section -->
<section class="search-section bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="search-box animate-fade-in-up">
                    <h4 class="text-center mb-4">Find Your Dream Vacation</h4>
                    <form action="search.php" method="GET">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="destination" placeholder="Where to?">
                            </div>
                            <div class="col-md-3">
                                <select class="form-control" name="duration">
                                    <option value="">Duration</option>
                                    <option value="1-3">1-3 Days</option>
                                    <option value="4-7">4-7 Days</option>
                                    <option value="8+">8+ Days</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-control" name="price_range">
                                    <option value="">Budget</option>
                                    <option value="0-10000">Under ₹10k</option>
                                    <option value="10000-20000">₹10k-20k</option>
                                    <option value="20000-50000">₹20k-50k</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Packages -->
<section class="section-padding" id="features">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="section-title animate-fade-in">Featured Tour Packages</h2>
                <p class="section-subtitle">Most popular destinations chosen by travelers</p>
            </div>
        </div>
        <div class="row">
            <?php
            $query = "SELECT * FROM tours LIMIT 3";
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
</section>

<!-- Features Section -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6 text-center mb-4">
                <div class="feature-box animate-bounce">
                    <i class="fas fa-shield-alt"></i>
                    <h4>Safe Travel</h4>
                    <p>Your safety is our priority</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 text-center mb-4">
                <div class="feature-box animate-bounce delay-1">
                    <i class="fas fa-rupee-sign"></i>
                    <h4>Best Price</h4>
                    <p>Guaranteed best prices</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 text-center mb-4">
                <div class="feature-box animate-bounce delay-2">
                    <i class="fas fa-headset"></i>
                    <h4>24/7 Support</h4>
                    <p>Round the clock support</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 text-center mb-4">
                <div class="feature-box animate-bounce delay-3">
                    <i class="fas fa-map-marked-alt"></i>
                    <h4>Various Tours</h4>
                    <p>Multiple destinations</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>