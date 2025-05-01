<?php
session_start();

if (isset($_COOKIE['token'])) {
    header("Location: ../home/home.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../assets/K&ALogo.png">
    <link rel="stylesheet" href="../styles/auth.styles.css">
    <script src="../js/loader.js"></script>
    <title>K&A | Log in</title>
    <title>Document</title>
</head>

<body>
    <div class="container">
        <div class="left-banner">
            <div class="left-banner-img"></div>
        </div>
        <div class="form-container">
            <div class="right-header">
                <img src="../assets/K&A_Dark.png" alt="" class="">
            </div>
            <div class="login-container">
                <h1>Welcome Back!</h1>
                <p>New to K&A Natural Spring Resort? <a href="signup.php">Sign up</a></p>
                <form action="login.auth.php" method="POST">
                    <div class="form-group">
                        <label for="">Email:</label>
                        <input type="email" placeholder="Enter your email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="">Confirm Password:</label>
                        <input type="password" placeholder="Enter your password" name="password">
                    </div>
                    <a href="forgot-password.php">Forgot Password?</a>
                    <button>Log in</button>
                </form>
                <?php if (isset($_SESSION['error'])): ?>
                    <p
                        style="color: #F74141; font-weight: 500; text-align: center; margin-bottom: 12px; font-style: italic;">
                        <?= $_SESSION['error'];
                        unset($_SESSION['error']); ?>
                    </p>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <p
                        style="color: #4CAF50; font-weight: 500; text-align: center; margin-bottom: 12px; font-style: italic;">
                        <?= $_SESSION['success'];
                        unset($_SESSION['success']); ?>
                    </p>
                <?php endif; ?>

                <a href="../google-auth/google-login.php">Sign up with Google</a>
            </div>
            <div class="footer">
                <a href="">Privacy Policy</a>
                <a href="">Term of Use</a>
            </div>
        </div>
    </div>
</body>

</html>