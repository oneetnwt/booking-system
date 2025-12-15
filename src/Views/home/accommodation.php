<?php

if (isset($_SESSION['booking_details'])) {
    unset($_SESSION['booking_details']);
    unset($_SESSION['room_id']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room = $_POST['room'];
    $room = (int) $room;

    $_SESSION['room_id'] = $room;

    header("Location: /booking/confirmation");
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
    <link rel="icon" href="../assets/K&ALogo.png">
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
                        <li><a href="/">Home</a></li>
                        <li><a href="/accommodation" class="active">Accommodation</a></li>
                        <li><a href="/reviews">Reviews</a></li>
                        <li><a href="#footer">Contact</a></li>
                    </ul>
                </nav>
                <nav class="auth-links">
                    <ul>
                        <?php if (!isset($_COOKIE['token'])): ?>
                            <li><a href="/auth/login" class="login-btn">Log in</a></li>
                            <li><a href="/auth/signup" class="signup-btn">Sign up</a></li>
                        <?php else: ?>
                            <li><a href="/accommodation" id="book-btn">Book Now</a></li>
                            <div class="dropdown">
                                <li><a href="/auth/logout">Hi,
                                        <?php echo $decoded->data->firstname . ' ' . $decoded->data->lastname; ?><i
                                            class="fa fa-caret-down" aria-hidden="true"
                                            style="margin-left: 0.5rem;"></i></a></li>
                                <div class="dropdown-menu">
                                    <a href="/profile">Edit Profile</a>
                                    <a href="/profile/my-bookings">My Bookings</a>
                                    <a href="/auth/logout">Log out</a>
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
            <?php foreach ($accommodations as $accommodation): ?>
                <div class="accommodation-card">
                    <img src="../assets/<?= $accommodation['image_path']; ?>" alt="<?= $accommodation['room_name']; ?>">
                    <div class="accommodation-details">
                        <h1><?= $accommodation['room_name']; ?></h1>
                        <p><?= $accommodation['room_description']; ?></p>
                        <div class="price">
                            <h2>₱<?= number_format($accommodation['room_price'], 2); ?></h2>
                            <form action="/accommodation" method="POST">
                                <input type="hidden" name="room" value="<?= $accommodation['id']; ?>">
                                <button>Book Now</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
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
                    <li><a href="home.php">Accommodation</a></li>
                    <li><a href="home.php">Amenities</a></li>
                    <li><a href="home.php">Rates</a></li>
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
</body>

</html>