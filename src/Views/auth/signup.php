<?php

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                            <input type="text" placeholder="First Name" name="firstname" value="<?= htmlspecialchars($_SESSION['form_data']['firstname'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="">Last Name:</label>
                            <input type="text" placeholder="Last Name" name="lastname" value="<?= htmlspecialchars($_SESSION['form_data']['lastname'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="">Email:</label>
                        <input type="email" placeholder="Enter your email" name="email" value="<?= htmlspecialchars($_SESSION['form_data']['email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="">Phone Number:</label>
                        <input type="number" placeholder="Enter your phone number" name="phone_number" value="<?= htmlspecialchars($_SESSION['form_data']['phone_number'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="">Password:</label>
                        <div class="password-container">
                            <input type="password" placeholder="Enter your password" name="password" id="password">
                            <span class="password-toggle" id="togglePassword" onclick="togglePassword('password', 'togglePassword')">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="">Confirm Password:</label>
                        <div class="password-container">
                            <input type="password" placeholder="Re-type your password" name="confirm-password" id="confirm-password">
                            <span class="password-toggle" id="toggleConfirmPassword" onclick="togglePassword('confirm-password', 'toggleConfirmPassword')">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    <div style="margin-bottom: 12px;" class="g-recaptcha" data-sitekey="<?= $site_key ?>"></div>
                    <button type="submit" id="signup-btn">Sign up</button>
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
    <script>
        // Function to toggle password visibility
        function togglePassword(inputId, toggleId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(toggleId).querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Function to show loading animation on signup button
        document.querySelector('form').addEventListener('submit', function() {
            const signupBtn = document.getElementById('signup-btn');
            const originalText = signupBtn.innerHTML;

            // Disable button and show loading state
            signupBtn.disabled = true;
            signupBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing up...';

            // Re-enable button after 5 seconds in case of error
            setTimeout(function() {
                if (signupBtn.disabled) {
                    signupBtn.disabled = false;
                    signupBtn.innerHTML = originalText;
                }
            }, 5000);
        });
    </script>
    <style>
        .password-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-container input {
            width: 100%;
            padding-right: 40px;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            cursor: pointer;
            color: #777;
        }

        .password-toggle:hover {
            color: #333;
        }

        #signup-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>
</body>

</html>