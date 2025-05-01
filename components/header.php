<header id="navbar">
    <div class="container header-content">
        <div class="logo">
            <img src="../assets/K&A.png" alt="K&A Resort Logo">
        </div>
        <div style="display: flex; gap: 3rem; align-items: center;">
            <nav>
                <ul class="nav-links">
                    <li><a href="#" class="active">Home</a></li>
                    <li><a href="../home/accommodation.php">Accommodation</a></li>
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
                            <li><a href="../auth/logout.php">Hi, <?php echo $decoded->data->firstname . ' ' . $decoded->data->lastname; ?><i class="fa fa-caret-down" aria-hidden="true" style="margin-left: 0.5rem;"></i></a></li>
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