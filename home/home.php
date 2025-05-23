<?php

session_start();

if (isset($_SESSION['booking_details'])) {
    unset($_SESSION['booking_details']);
    unset($_SESSION['room_id']);
}


require __DIR__ . "/../vendor/autoload.php";
require '../db/connectDB.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$secret_key = $_ENV['JWT_SECRET_KEY'];

if (isset($_COOKIE['token'])) {
    $token = $_COOKIE['token'];
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
}

$stmt = $pdo->prepare("SELECT * FROM room ORDER BY room_price ASC LIMIT 3");
$stmt->execute();
$accommodations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT r.*, u.firstname, u.lastname, rm.room_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    JOIN room rm ON r.room_id = rm.id 
    ORDER BY r.created_at DESC 
    LIMIT 2
");
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/home.styles.css">
    <link rel="icon" href="../assets/K&ALogo.png">
    <title>K&A Resort</title>
</head>

<body>
    <header id="navbar">
        <div class="container header-content">
            <div class="logo">
                <img src="../assets/K&A.png" alt="K&A Resort Logo">
            </div>
            <div style="display: flex; gap: 3rem; align-items: center;">
                <nav>
                    <ul class="nav-links">
                        <li><a href="home.php" class="active">Home</a></li>
                        <li><a href="accommodation.php">Accommodation</a></li>
                        <li><a href="reviews.php">Reviews</a></li>
                        <li><a href="#footer">Contact</a></li>
                    </ul>
                </nav>
                <nav class="auth-links">
                    <ul>
                        <?php if (!isset($_COOKIE['token'])): ?>
                            <li><a href="../auth/login.php" class="login-btn">Log in</a></li>
                            <li><a href="../auth/signup.php" class="signup-btn">Sign up</a></li>
                        <?php else: ?>
                            <li><a href="accommodation.php" id="book-btn">Book Now</a></li>
                            <div class="dropdown">
                                <li>
                                    <p style="cursor: pointer">Hi,
                                        <?php echo $decoded->data->firstname . ' ' . $decoded->data->lastname; ?><i
                                            class="fa fa-caret-down" aria-hidden="true" style="margin-left: 0.5rem;"></i>
                                    </p>
                                </li>
                                <?php if ($decoded->data->role === 'admin'): ?>
                                    <div class="dropdown-menu">
                                        <a href="../admin/dashboard.php">Admin</a>
                                        <a href="../profile/profile.php">Edit Profile</a>
                                        <a href="../profile/my-bookings.php">My Bookings</a>
                                        <a href="../auth/logout.php">Log out</a>
                                    </div>
                                <?php else: ?>
                                    <div class="dropdown-menu">
                                        <a href="../profile/profile.php">Edit Profile</a>
                                        <a href="../profile/my-bookings.php">My Bookings</a>
                                        <a href="../auth/logout.php">Log out</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Escape the Ordinary, <br><span>Discover Paradise.</span></h1>
                <p style=" width: 90%;"">Experience a blend of unparalleled hospitality with serene natural beauty
                        at K&A Resort
                        Spring
                        Resort.</p>
                        <a href=" accommodation.php" class="btn btn-primary">Book Now</a>
                    <!-- <a href="#" class="btn btn-outline">Learn More</a> -->
            </div>
        </div>
        <div class="hero-image">
            <img src="../assets/poolside.png" alt="Resort Pool View" style="object-fit: cover;">
        </div>
    </section>

    <!-- Wave Divider -->
    <div class="wave-divider">
        <q>Experience the unforgettable happiness with your family and friends at<br> K&A Natural Spring Resort</q>
    </div>

    <!-- About Section -->
    <section class="about">
        <div class="container about-container">
            <div class="about-content">
                <img src="../assets/K&A_Dark.png" alt="" height="75px">
                <h2>About Us</h2>
                <p>Leave your worries behind and find tranquility at <strong>K&A Natural Spring Resort</strong>, a
                    beautiful escape into
                    nature. Stroll through our green grounds, unwind by the sparkling pool, and enjoy friendly service
                    in our comfortable rooms. A relaxing getaway is waiting for you.</p>
                <!-- <a href="#" class="btn btn-primary">Learn More</a> -->
            </div>
            <div class="about-image">
                <img src="../assets/poolside_night.jpg" alt="Resort Night View">
            </div>
        </div>
    </section>

    <!-- Entrance Rates -->
    <section class="rates" id="rates">
        <div class="container">
            <h2>Entrance Rate</h2>
            <div class="rate-cards">
                <div class="rate-card">
                    <div class="rate-price">₱50</div>
                    <div class="rate-title">Adult and Teens</div>
                    <div class="rate-details">13 years old and above</div>
                </div>
                <div class="rate-card">
                    <div class="rate-price">₱30</div>
                    <div class="rate-title">Kids</div>
                    <div class="rate-details">3 years old and below</div>
                </div>
                <div class="rate-card">
                    <div class="rate-price">₱200</div>
                    <div class="rate-title">Overnight Stay</div>
                    <div class="rate-details">6PM to 6AM <br> (Cottage Charge + Entrance Fee)</div>
                </div>
            </div>
            <p style="text-align: center; margin-top: 20px; color: #666;">Price listed is PHP HEADS <br> Excess hours:
                PHP 20.00 / hour / person</p>
        </div>
    </section>

    <!-- Accommodations -->
    <section class="accommodations">
        <div class="container">
            <div class="section-header">
                <h2>Accommodation</h2>
                <a href="accommodation.php" class="btn">See All →</a>
            </div>
            <div class="room-cards">
                <?php foreach ($accommodations as $accommodation): ?>
                    <div class="room-card">
                        <div class="room-image">
                            <img src="../assets/<?= $accommodation['image_path']; ?>"
                                alt="<?= $accommodation['room_name']; ?>">
                        </div>
                        <div class="room-content">
                            <h3 class="room-title"><?= $accommodation['room_name']; ?></h3>
                            <div class="room-price">₱<?= number_format($accommodation['room_price'], 2) ?></div>
                            <div class="room-details"><?= $accommodation['room_description']; ?></div>
                            <a href="booking-confirmation.php?room_id=<?= $accommodation['id']; ?>" class="btn btn-primary"
                                style="float: bottom">Book Now</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Amenities -->
    <section class="amenities" id="amenities">
        <div class="container">
            <div class="section-header">
                <h2>Amenities</h2>
            </div>
            <div class="amenity-grid">
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../assets/pool.jpg" alt="Swimming Pool">
                    </div>
                    <div class="amenity-content">
                        <h3 class="amenity-title">Swimming Pool</h3>
                        <p class="amenity-description">Three big pools, with dedicated children's pool.</p>
                    </div>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../assets/karaoke.jpg" alt="Karaoke">
                    </div>
                    <div class="amenity-content">
                        <h3 class="amenity-title">Karaoke</h3>
                        <p class="amenity-description">Entertainment room video-singko karaoke system</p>
                    </div>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../assets/poolbar.jpg" alt="Pool Bar">
                    </div>
                    <div class="amenity-content">
                        <h3 class="amenity-title">Mini Pool Bar</h3>
                        <p class="amenity-description">Refreshing drinks and snacks served poolside</p>
                    </div>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../assets/shower.jpg" alt="Shower Area">
                    </div>
                    <div class="amenity-content">
                        <h3 class="amenity-title">Shower Area</h3>
                        <p class="amenity-description">Clean and spacious shower facilities</p>
                    </div>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../assets/grill.jpg" alt="Slide Area">
                    </div>
                    <div class="amenity-content">
                        <h3 class="amenity-title">Grilling Area</h3>
                        <p class="amenity-description">Well-equipped BBQ stations for outdoor cooking</p>
                    </div>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../assets/toilet.jpg" alt="Toilet Facilities">
                    </div>
                    <div class="amenity-content">
                        <h3 class="amenity-title">Toilet Facilities</h3>
                        <p class="amenity-description">Modern and well-maintained restoroom facilities.</p>
                    </div>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../assets/sari.jpg" alt="Self-use Store">
                    </div>
                    <div class="amenity-content">
                        <h3 class="amenity-title">Sari-Sari Store</h3>
                        <p class="amenity-description">Convenience store for items and snacks.</p>
                    </div>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../assets/lifeJackets.jpg" alt="Videoke">
                    </div>
                    <div class="amenity-content">
                        <h3 class="amenity-title">Lifejackets</h3>
                        <p class="amenity-description">Safety equipment available for all ages.</p>
                    </div>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../assets/bonfire.jpg" alt="Bonfire Area">
                    </div>
                    <div class="amenity-content">
                        <h3 class="amenity-title">Bonfire Area</h3>
                        <p class="amenity-description">Evening gathering spot</p>
                    </div>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../assets/pisowifi.jpg" alt="WiFi Access">
                    </div>
                    <div class="amenity-content">
                        <h3 class="amenity-title">WiFi Access</h3>
                        <p class="amenity-description">Piso WiFi is available and have internet access throughout the
                            entire resort.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials">
        <div class="container">
            <div class="section-header">
                <h2>What Our Guests Say</h2>
                <a href="reviews.php" class="btn">See All →</a>
            </div>
            <div class="testimonial-container">
                <?php foreach ($reviews as $review): ?>
                    <div class="testimonial-card">
                        <div class="testimonial-header">
                            <div class="testimonial-avatar">
                                <img src="../assets/K&ALogo.png" alt="Guest Avatar">
                            </div>
                            <div>
                                <div class="testimonial-name">
                                    <?= htmlspecialchars($review['firstname'] . ' ' . $review['lastname']); ?>
                                </div>
                                <div class="rating">
                                    <?php for ($i = 1; $i <= $review['rating']; $i++): ?>
                                        <i class="fas fa-star active"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        <p class="testimonial-text"><?= htmlspecialchars($review['comment']); ?></p>
                        <div><?= number_format($review['rating'], 1); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <div class="map-container">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3949.8376298235517!2d125.1426910750085!3d8.118009291911402!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x32ff01bdff7ed583%3A0xb69441e2cfbe3a30!2sK%20%26%20A%20Resort!5e0!3m2!1sen!2sph!4v1745248157562!5m2!1sen!2sph"
                    width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="https://maps.app.goo.gl/Y5838LU71En3mBZG7" target="_blank" class="btn btn-primary">Driving
                    Directions</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="footer">
        <div class="container footer-container">
            <div class="footer-column">
                <div class="footer-logo">
                    <img src="../assets/K&ALogo.png" alt="K&A Resort Logo">
                </div>
                <p>Escape the ordinary, discover paradise at K&A Natural Spring Resort.</p>
                <div class="footer-social">
                    <a href="https://www.facebook.com/KANaturalSpringResort" target="_blank"><i
                            class="fab fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/kandaresort2021/" target="_blank"><i
                            class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="footer-column footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="accommodation.php">Accommodation</a></li>
                    <li><a href="#amenities">Amenities</a></li>
                    <li><a href="#rates">Rates</a></li>
                </ul>
            </div>
            <div class="footer-column footer-contact">
                <h3>Contact Us</h3>
                <ul style="margin-top: 1rem;">
                    <li><a href="mailto:kandaresort2021@gmail.com"><i class="fa fa-envelope"
                                aria-hidden="true"></i>kandaresort2021@gmail.com</a></li>
                    <li><a href="https://www.facebook.com/KANaturalSpringResort" target="_blank"><i
                                class="fa-brands fa-facebook-f"></i>K&A Natural Spring Resort</a></li>
                    <li><i class="fa fa-phone" aria-hidden="true"></i>+63 985 230 6512</li>
                </ul>
            </div>
        </div>
        <p style="text-align: center; margin-top: 50px; font-weight: 200;">&copy; 2025 K&A Natural Spring Resort. All
            rights reserved.</p>
    </footer>

    <script src="../js/loader.js"></script>
    <script>
        window.addEventListener("scroll", function () {
            const navbar = document.getElementById("navbar");
            const bookBtn = document.getElementById("book-btn");
            if (window.scrollY > 500) {
                navbar.style.backgroundColor = "#102C57";
                if (bookBtn) {
                    bookBtn.style.display = "flex";
                }
            } else {
                if (bookBtn) {
                    bookBtn.style.display = "none";
                }
                navbar.style.backgroundColor = "transparent";
            }
        });
    </script>
</body>

</html>