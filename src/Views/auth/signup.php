<?php
session_start();

require_once __DIR__ . "/../../../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 3));
$dotenv->load();

$site_key = $_ENV['RECAPTCHA_SITE_KEY'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../assets/K&ALogo.png">
    <link rel="stylesheet" href="../styles/auth.styles.css">
    <script src="../js/loader.js"></script>
    <title>K&A | Create an Account</title>
</head>

<body>
    <div class="container">
        <div class="form-container">
            <div class="left-header">
                <img src="../assets/K&A_Dark.png" alt="" class="">
            </div>
            <div class="login-container">
                <h1>Create an Account</h1>
                <p>Already have an account? <a href="/auth/login">Log in</a></p>
                <form action="/auth/signup" method="POST">
                    <div class="name">
                        <div class="form-group">
                            <label for="">First Name:</label>
                            <input type="text" placeholder="First Name" name="firstname">
                        </div>
                        <div class="form-group">
                            <label for="">Last Name:</label>
                            <input type="text" placeholder="Last Name" name="lastname">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="">Email:</label>
                        <input type="email" placeholder="Enter your email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="">Phone Number:</label>
                        <input type="number" placeholder="Enter your phone number" name="phone_number">
                    </div>
                    <div class="form-group">
                        <label for="">Password:</label>
                        <input type="password" placeholder="Enter your password" name="password">
                    </div>
                    <div class="form-group">
                        <label for="">Confirm Password:</label>
                        <input type="password" placeholder="Re-type your password" name="confirm-password">
                    </div>
                    <div style="margin-bottom: 12px;" class="g-recaptcha" data-sitekey="<?= $site_key ?>"></div>
                    <button>Sign up</button>
                    <?php if (isset($_SESSION['error'])): ?>
                        <p
                            style="color: #F74141; font-weight: 500; text-align: center; margin-bottom: 12px; font-style: italic;">
                            <?= $_SESSION['error'];
                            unset($_SESSION['error']); ?>
                        </p>
                    <?php endif; ?>
                </form>
            </div>
            <div class="footer">
                <a href="">Privacy Policy</a>
                <a href="">Term of Use</a>
            </div>
        </div>
        <div class="right-banner">
            <div class="right-banner-img"></div>
        </div>
    </div>
    <script src="https://www.google.com/recaptcha/api.js"></script>
</body>

</html>