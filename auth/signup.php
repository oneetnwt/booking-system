<?php
session_start();

if(isset($_COOKIE['token'])){
    header("Location: ../home/home.php");
    exit();
}

require_once __DIR__ . "/../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$site_key = $_ENV['RECAPTCHA_SITE_KEY'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/auth.styles.css">
    <title>Signup</title>
</head>

<body>
    <div class="container">
        <div class="form-container">
            <div class="left-header">
                <img src="../assets/K&A_Dark.png" alt="">
            </div>
            <div class="login-container">
                <h1>Create an Account</h1>
                <p>Already have an account? <a href="login.php">Login</a></p>
                <form action="signup_validate.php" method="POST">
                    <div class="name">
                        <div class="form-group">
                            <label for="firstname">First Name:</label>
                            <input type="text" placeholder="First Name" name="firstname">
                        </div>
                        <div class="form-group">
                            <label for="firstname">Last Name:</label>
                            <input type="text" placeholder="First Name" name="lastname">
                        </div>
                    </div>
                    <label for="email">Email:</label>
                    <input type="email" placeholder="Email" name="email">
                    <label for="phone_number">Phone Number:</label>
                    <input type="number" placeholder="Phone Number" name="phone_number">
                    <dlabel for="password">Password:</dlabel>
                    <input type="password" placeholder="Password" name="password">
                    <label for="confirm-password">Confirm Password:</label>
                    <input type="password" placeholder="Confirm Password" name="confirm-password">
                    <div style="margin: 0.5rem 0;" class="g-recaptcha" data-sitekey="<?= $site_key ?>"></div>
                    <button>Sign up</button>
                </form>
                <?php if (isset($_SESSION['success'])): ?>
                    <p
                        style="color: #77DD77; font-weight: 500; text-align: center; margin-bottom: 12px; font-style: italic;">
                        <?= $_SESSION['success'];
                        unset($_SESSION['success']); ?>
                    </p>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <p
                        style="color: #F74141; font-weight: 500; text-align: center; margin-bottom: 12px; font-style: italic;">
                        <?= $_SESSION['error'];
                        unset($_SESSION['error']); ?>
                    </p>
                <?php endif; ?>
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