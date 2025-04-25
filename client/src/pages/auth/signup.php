<?php
session_start();

require_once __DIR__ . "/../../../../server/vendor/autoload.php";

// Load environment variables from the server directory
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../../../../server");
$dotenv->load();

$site_key = $_ENV['RECAPTCHA_SITE_KEY'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/signup.styles.css">
    <title>K&A Natural Spring Resort - Sign Up</title>
</head>

<body>
    <div class="logo">
        <img src="../../../assets/K&ALogo.png" alt="K&A Logo">
        <img src="../../../assets/K&A.png" alt="" class="logo-text">
    </div>

    <div class="container">
        <div class="form-container">
            <h1>Create An Account</h1>
            <p class="login-text">Already have an account? <a href="login.php">Login</a></p>
            <?php if (isset($_SESSION['error'])): ?>
                <p style="color: #F74141; font-weight: 500; text-align: center; margin-bottom: 12px; font-style: italic;">
                    <?= $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </p>
            <?php endif; ?>
            <form id="signup-form" action="../../../../server/auth/signup.auth.php" method="POST">
                <div class="name-group">
                    <div class="form-group">
                        <label for="first-name">First Name</label>
                        <input type="text" id="first-name" placeholder="Enter your first name" name="firstname"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="last-name">Last Name</label>
                        <input type="text" id="last-name" placeholder="Enter your last name" name="lastname" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" placeholder="Enter your email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" placeholder="Enter your phone number" name="phone_number" required>
                </div>

                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" placeholder="Enter your password" name="password" required>
                </div>

                <div class="form-group last-form-group">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" id="confirm-password" placeholder="Confirm Password" name="confirm_password"
                        required>
                </div>
                <div style="margin-bottom: 12px;" class="g-recaptcha" data-sitekey="<?= $site_key ?>"></div>
                <button type="submit">Sign Up</button>
            </form>
        </div>

        <div class="image-container">
            <img src="../../../assets/a-house-night.jpg" alt="A-House Night View">
            <div class="image-caption">A-House Night View</div>
        </div>
    </div>
    <script src="https://www.google.com/recaptcha/api.js"></script>
</body>

</html>