<?php

// JWT token decoding and reviews data should be handled by the controller
// The view receives $decoded and $reviews from HomeController->reviews()

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/home.styles.css">
    <link rel="icon" href="../assets/K&ALogo.png">
    <title>Reviews - K&A Resort</title>
    <style>
        .reviews-section {
            padding: 6rem 0 4rem 0;
            background-color: #f8f9fa;
        }

        .reviews-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .review-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .review-card:hover {
            transform: translateY(-5px);
        }

        .review-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .review-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
        }

        .review-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .review-info {
            flex: 1;
        }

        .review-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .review-room {
            font-size: 0.9rem;
            color: #666;
        }

        .review-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0.5rem 0;
        }

        .stars {
            color: #ffd700;
        }

        .review-date {
            font-size: 0.8rem;
            color: #888;
            margin-top: 0.5rem;
        }

        .review-text {
            color: #444;
            line-height: 1.6;
            margin-top: 1rem;
        }

        .section-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .section-title h2 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .section-title p {
            color: #666;
            font-size: 1.1rem;
        }
    </style>
</head>

<body>
    <header id="navbar" style="background-color: #102C57">
        <div class="container header-content">
            <div class="logo">
                <img src="../assets/K&A.png" alt="K&A Resort Logo">
            </div>
            <div style="display: flex; gap: 3rem; align-items: center;">
                <nav>
                    <ul class="nav-links">
                        <li><a href="/">Home</a></li>
                        <li><a href="/accommodation">Accommodation</a></li>
                        <li><a href="/reviews" class="active">Reviews</a></li>
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
                                <li>
                                    <p style="cursor: pointer">Hi,
                                        <?php echo $decoded->data->firstname . ' ' . $decoded->data->lastname; ?><i
                                            class="fa fa-caret-down" aria-hidden="true" style="margin-left: 0.5rem;"></i>
                                    </p>
                                </li>
                                <?php if ($decoded->data->role === 'admin'): ?>
                                    <div class="dropdown-menu">
                                        <a href="/admin/dashboard">Admin</a>
                                        <a href="/profile">Edit Profile</a>
                                        <a href="/profile/my-bookings">My Bookings</a>
                                        <a href="/auth/logout">Log out</a>
                                    </div>
                                <?php else: ?>
                                    <div class="dropdown-menu">
                                        <a href="/profile">Edit Profile</a>
                                        <a href="/profile/my-bookings">My Bookings</a>
                                        <a href="/auth/logout">Log out</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <section class="reviews-section">
        <div class="reviews-container">
            <div class="section-title">
                <h2>Guest Reviews</h2>
                <p>Read what our guests have to say about their stay at K&A Resort</p>
            </div>
            <div class="reviews-grid">
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div class="review-avatar">
                                <img src="../assets/K&ALogo.png" alt="Guest Avatar">
                            </div>
                            <div class="review-info">
                                <div class="review-name">
                                    <?= htmlspecialchars($review['firstname'] . ' ' . $review['lastname']); ?>
                                </div>
                                <div class="review-room"><?= htmlspecialchars($review['room_name']); ?></div>
                            </div>
                        </div>
                        <div class="review-rating">
                            <div class="stars">
                                <?php for ($i = 1; $i <= $review['rating']; $i++): ?>
                                    <i class="fas fa-star active"></i>
                                <?php endfor; ?>
                            </div>
                            <span><?= number_format($review['rating'], 1); ?></span>
                        </div>
                        <div class="review-date">
                            <?= date('F d, Y', strtotime($review['created_at'])); ?>
                        </div>
                        <p class="review-text"><?= htmlspecialchars($review['comment']); ?></p>
                    </div>
                <?php endforeach; ?>
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
                    <li><a href="/">Home</a></li>
                    <li><a href="/accommodation">Accommodation</a></li>
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
</body>

</html>