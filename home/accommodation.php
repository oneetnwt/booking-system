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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room = $_POST['room'];
    $room = (int)$room;

    $_SESSION['room_id'] = $room;

    header("Location: booking-confirmation.php");
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/accommodation.styles.css">
    <script src="../js/loader.js"></script>
    <title>K&A Natural Spring Resort</title>
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
                        <li><a href="home.php">Home</a></li>
                        <li><a href="accommodation.php" class="active">Accommodation</a></li>
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
                                <li><a href="../auth/logout.php">Hi,
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
    <section class="accommodation">
        <div class="accommodation-header">
            <h1>Our Accommodations</h1>
            <p>Discover our rooms and suites designed for your perfect stay</p>
        </div>
        <div class="accommodation-list">
            <!-- Card -->
            <div class="accommodation-card">
                <img src="../assets/cabin.jpg" alt="">
                <div class="accommodation-details">
                    <h1>Upper Kubo</h1>
                    <p>2 Kubos that can accommodate up to 15 persons, featuring a kitchen, dining area, toilet, and
                        shower on the ground floor</p>
                    <div class="amenities">
                        <h3>Kubo #1 Amenities</h3>
                        <ul>
                            <li>2 Queen-size Bed</li>
                            <li>55" TV with Netflix</li>
                            <li>Wall Fan</li>
                            <li>Hot & Cold Water Dispenser</li>
                        </ul>
                    </div>
                    <div class="price">
                        <h2>From ₱5,000.00</h2>
                        <form action="" method="POST">
                            <input type="hidden" name="room" value="1000">
                            <button>Book Now</button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Card -->
            <div class="accommodation-card">
                <img src="../assets/a-house.jpg" alt="">
                <div class="accommodation-details">
                    <h1>A-House</h1>
                    <p>Suitable for 2 persons with maximum up to 4 persons.<br> <strong>+₱200 per person beyond
                            2</strong></p>
                    <div class="amenities">
                        <h3>Room Amenities: </h3>
                        <ul>
                            <li>Private Bathroom</li>
                            <li>Water Heater and Basic Utensils</li>
                            <li>Ground Floor Dining Table</li>
                        </ul>
                    </div>
                    <div class="price">
                        <h2>From ₱2,500.00</h2>
                        <form action="" method="POST">
                            <input type="hidden" name="room" value="1001">
                            <button>Book Now</button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Card -->
            <div class="accommodation-card">
                <img src="../assets/cottage.jpg" alt="">
                <div class="accommodation-details">
                    <h1>Cottage</h1>
                    <p>Fits up to 5-10 persons inside.</p>
                    <div class="amenities">
                        <h3>Cottage Amenities:</h3>
                        <ul>
                            <li>1 Table</li>
                        </ul>
                    </div>
                    <div class="price">
                        <h2>From ₱700 - ₱1,0000</h2>
                        <form action="" method="POST">
                            <input type="hidden" name="room" value="1002">
                            <button>Book Now</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
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
</body>

</html>