<?php

session_start();

if (isset($_SESSION['booking_details'])) {
    unset($_SESSION['booking_details']);
    unset($_SESSION['room_id']);
}


require __DIR__ . "/../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$secret_key = $_ENV['JWT_SECRET_KEY'];

if (isset($_COOKIE['token'])) {
    $token = $_COOKIE['token'];
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
}
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
                        <li><a href="#">Gallery</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </nav>
                <nav class="auth-links">
                    <ul>
                        <?php if (!isset($_COOKIE['token'])): ?>
                            <li><a href="../auth/login.php" class="login-btn">Log in</a></li>
                            <li><a href="../auth/signup.php" class="signup-btn">Sign up</a></li>
                        <?php else: ?>
                            <li><a href="#" id="book-btn">Book Now</a></li>
                            <div class="dropdown">
                                <li><a href="">Hi,
                                        <?php echo $decoded->data->firstname . ' ' . $decoded->data->lastname; ?><i
                                            class="fa fa-caret-down" aria-hidden="true"
                                            style="margin-left: 0.5rem;"></i></a></li>
                                <div class="dropdown-menu">
                                    <a href="">Edit Profile</a>
                                    <a href="">My Bookings</a>
                                    <a href="../auth/logout.php">Log out</a>
                                </div>
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
                        <a href=" ../auth/login.php" class="btn btn-primary">Book Now</a>
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
                <p>Have your summer kicked-off and thoroughly enjoyed at K&A Natural Spring Resort. A tranquil paradise
                    nestled in San Antonio, Quezon City, where 97% chlorine-free pool, shaded by the sprawling pools and
                    trees to ensure tranquil leisure in an undisturbed environment.</p>
                <a href="#" class="btn btn-primary">Learn More</a>
            </div>
            <div class="about-image">
                <img src="../assets/poolside_night.jpg" alt="Resort Night View">
            </div>
        </div>
    </section>

    <!-- Entrance Rates -->
    <section class="rates">
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
                <div class="room-card">
                    <div class="room-image">
                        <img src="../assets/cabin.jpg" alt="Upper Kubo">
                    </div>
                    <div class="room-content">
                        <h3 class="room-title">Upper Kubo</h3>
                        <div class="room-price">₱1,500</div>
                        <div class="room-details">Perfect for families, group of friends, stunning pool views —
                            accommodating 10-15 people.</div>
                        <a href="#" class="btn btn-primary">Book Now</a>
                    </div>
                </div>
                <div class="room-card">
                    <div class="room-image">
                        <img src="../assets/a-house.jpg" alt="A-House">
                    </div>
                    <div class="room-content">
                        <h3 class="room-title">A-House</h3>
                        <div class="room-price">₱2,500</div>
                        <div class="room-details">Good for 2 persons maximum. <br> Aircon + CR</div>
                        <a href="#" class="btn btn-primary">Book Now</a>
                    </div>
                </div>
                <div class="room-card">
                    <div class="room-image">
                        <img src="../assets/cottage.jpg" alt="Cottage">
                    </div>
                    <div class="room-content">
                        <h3 class="room-title">Cottage</h3>
                        <div class="room-price">From ₱500</div>
                        <div class="room-details">Different types available to suit your needs.</div>
                        <a href="#" class="btn btn-primary">Book Now</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Amenities -->
    <section class="amenities">
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
                        <p class="amenity-description">Natural spring water, 97% chlorine-free</p>
                    </div>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../assets/karaoke.jpg" alt="Karaoke">
                    </div>
                    <div class="amenity-content">
                        <h3 class="amenity-title">Karaoke</h3>
                        <p class="amenity-description">Modern karaoke system</p>
                    </div>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../assets/poolbar.jpg" alt="Pool Bar">
                    </div>
                    <div class="amenity-content">
                        <h3 class="amenity-title">Pool Bar</h3>
                        <p class="amenity-description">Refreshing drinks by the pool</p>
                    </div>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../assets/shower.jpg" alt="Shower Area">
                    </div>
                    <div class="amenity-content">
                        <h3 class="amenity-title">Shower Area</h3>
                        <p class="amenity-description">Clean and modern facilities</p>
                    </div>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../assets/grill.jpg" alt="Slide Area">
                    </div>
                    <div class="amenity-content">
                        <h3 class="amenity-title">Slide Area</h3>
                        <p class="amenity-description">Fun for all ages</p>
                    </div>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../assets/toilet.jpg" alt="Toilet Facilities">
                    </div>
                    <div class="amenity-content">
                        <h3 class="amenity-title">Toilet Facilities</h3>
                        <p class="amenity-description">Clean and well-maintained</p>
                    </div>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../assets/sari.jpg" alt="Self-use Store">
                    </div>
                    <div class="amenity-content">
                        <h3 class="amenity-title">Self-use Store</h3>
                        <p class="amenity-description">Convenience items available</p>
                    </div>
                </div>
                <div class="amenity-item">
                    <div class="amenity-image">
                        <img src="../assets/lifeJackets.jpg" alt="Videoke">
                    </div>
                    <div class="amenity-content">
                        <h3 class="amenity-title">Videoke</h3>
                        <p class="amenity-description">Entertainment for guests</p>
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
                        <img src="../assets/wifi.png" alt="WiFi Access">
                    </div>
                    <div class="amenity-content">
                        <h3 class="amenity-title">WiFi Access</h3>
                        <p class="amenity-description">Stay connected during your visit</p>
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
                <a href="#" class="btn">See All →</a>
            </div>
            <div class="testimonial-container">
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <img src="../assets/Danz.png" alt="Guest Avatar">
                        </div>
                        <div>
                            <div class="testimonial-name">Donna J.</div>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="testimonial-text">"Perfect getaway! We really enjoyed our stay here. The natural spring
                        pools were amazing, very relaxing. The staff was friendly. The setting was beautiful with all
                        the trees providing shade. We'll definitely be coming back!"</p>
                    <div>5.0</div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <img src="../assets/Jeff.png" alt="Guest Avatar">
                        </div>
                        <div>
                            <div class="testimonial-name">Jeff Ivan</div>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="testimonial-text">"Great place for a summer day! The pools are cool and refreshing. Kids
                        had a blast on the slides. The cottages were clean and comfortable. Food services available
                        on-site made our stay convenient. Would recommend to families!"</p>
                    <div>5.0</div>
                </div>
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
                <a href="#" class="btn btn-primary">Driving Directions</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container footer-container">
            <div class="footer-column">
                <div class="footer-logo">
                    <img src="../assets/K&ALogo.png" alt="K&A Resort Logo">
                </div>
                <p>Escape the ordinary, discover paradise at K&A Natural Spring Resort.</p>
                <div class="footer-social">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="footer-column footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#">Accommodation</a></li>
                    <li><a href="#">Amenities</a></li>
                    <li><a href="#">Rates</a></li>
                    <li><a href="#">Gallery</a></li>
                </ul>
            </div>
            <div class="footer-column footer-links">
                <h3>Help</h3>
                <ul>
                    <li><a href="#">FAQs</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">Refund Policy</a></li>
                </ul>
            </div>
            <div class="footer-column footer-contact">
                <h3>Contact Us</h3>
                <ul></ul>
            </div>
        </div>
        <p style="text-align: center; margin-top: 50px; font-weight: 200;">&copy; 2025 K&A Natural Spring Resort. All
            rights reserved.</p>
    </footer>

    <script src="../js/loader.js"></script>
    <script>
        window.addEventListener("scroll", function() {
            const navbar = document.getElementById("navbar");
            const bookBtn = document.getElementById("book-btn");
            if (window.scrollY > 500) {
                navbar.style.backgroundColor = "#003366";
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