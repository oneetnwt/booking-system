<?php session_start() ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/login.styles.css">
    <title>K&A Natural Spring Resort</title>
</head>

<body>
    <div class="header">
        <div class="logo">
            <img src="../../../assets/K&ALogo.png" alt="K&A Natural Spring Resort Logo" class="logo-circle">
            <img src="../../../assets/K&A.png" alt="K&A Natural Spring Resort Logo" class="resort-name">
        </div>
    </div>

    <div class="main-content">
        <div class="image-container">
            <div class="resort-image">
                <img src="../../../assets/cabin-view.jpg" alt="Resort Pool View">
                <div class="image-caption">View From Upper Kubo</div>
            </div>
        </div>

        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <h2>Welcome Back</h2>
                    <p>New to K&A Natural Spring Resort? <a href="signup.php" id="signupLink">Sign up</a></p>
                </div>

                <form id="loginForm">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" placeholder="Enter your email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" placeholder="Enter your password" required>
                    </div>

                    <div class="forgot-password">
                        <a href="#" id="forgotPasswordLink">Forgot your password?</a>
                    </div>

                    <button type="submit" class="login-button">Login</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>