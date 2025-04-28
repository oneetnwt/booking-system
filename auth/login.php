<?php 
session_start();

if(isset($_COOKIE['token'])){
    header("Location: ../home/home.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/auth.styles.css">
    <title>Login</title>
</head>
<body>
    <div class="container">
        <div class="left-banner">
            <div class="left-banner-img"></div>
        </div>
        <div class="form-container">
            <div class="right-header">
                <img src="../assets/K&A_Dark.png" alt="">
            </div>
            <div class="login-container">
                <h1>Welcome Back</h1>
                <p>New to K&A Natural Spring Resort? <a href="signup.php">Sign up</a></p>
                <form action="login_validate.php" method="POST">
                    <label for="email">Email:</label>
                    <input type="email" name="email" placeholder="johndoe@gmail.com">
                    <label for="password">Password:</label>
                    <input type="password" name="password" placeholder="Enter the password">
                    <a href="">Forgot Password?</a>
                    <button>Log in</button>
                </form>
            </div>
            <div class="footer">
                <a href="">Privacy Policy</a>
                <a href="">Term of Use</a>
            </div>
        </div>
    </div>
</body>
</html>