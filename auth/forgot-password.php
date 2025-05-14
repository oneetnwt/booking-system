<?php
require '../vendor/autoload.php';
require '../db/connectDB.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $reset_code = rand(100000, 999999);

        $_SESSION['reset_code'] = $reset_code;
        $_SESSION['reset_email'] = $email;

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = 'true';
            $mail->Username = $_ENV['APP_EMAIL'];
            $mail->Password = $_ENV['APP_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($_ENV['APP_EMAIL'], $_ENV['APP_NAME']);
            $mail->addAddress($email, "THIS IS YOUR CLIENT");

            $mail->isHTML(true);
            $mail->Subject = "Verify Your Email - K&A Resort";

            // Embed images - these will be referenced in the HTML with Content-IDs
            $logoPath = '../assets/K&A_Dark.png';  // Path to your logo file
            $iconPath = '../assets/K&ALogo.png';  // Path to your icon file

            // Add embedded images with Content-IDs
            $logoContentId = md5('logo') . rand(1000, 9999); // Create unique identifier for logo
            $iconContentId = md5('icon') . rand(1000, 9999); // Create unique identifier for icon

            // Add the images as embedded attachments
            $mail->addEmbeddedImage($logoPath, $logoContentId, 'logo.png');
            $mail->addEmbeddedImage($iconPath, $iconContentId, 'icon.png');


            $mail->Body = '
          <!DOCTYPE html>
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #f5f8fa;
                }
                .email-container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    border: 1px solid #e1e8ed;
                    border-radius: 5px;
                    overflow: hidden;
                }
                .header {
                    padding: 20px;
                    text-align: left;
                    border-bottom: 1px solid #e1e8ed;
                }
                .logo {
                    height: 40px;
                }
                .content {
                    padding: 30px;
                    background-color: #f0f8ff;
                    color: #333333;
                    border-radius: 5px;
                    margin: 20px;
                    border: 1px solid #d1e0ed;
                }
                .verification-code {
                    background-color: #4285f4;
                    color: white;
                    font-size: 24px;
                    font-weight: bold;
                    padding: 12px 20px;
                    text-align: center;
                    border-radius: 4px;
                    margin: 25px 0;
                    letter-spacing: 10px;
                }
                .footer {
                    padding: 15px;
                    text-align: center;
                    font-size: 12px;
                    color: #657786;
                }
                .disclaimer {
                    font-size: 11px;
                    color: #888;
                    text-align: center;
                    margin-top: 15px;
                }
                a {
                    color: #657786;
                    text-decoration: none;
                }
                a:hover {
                    text-decoration: underline;
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="header">
                    <img src="cid:' . $logoContentId . '" alt="K&A Natural Spring Resort" class="logo">
                    <span style="float:right;"><img src="cid:' . $iconContentId . '" alt="K&A" height="30"></span>
                </div>
                <div class="content">
                    <p>Hi ' . htmlspecialchars($user['firstname']) . ',</p>
                    <p>You have successfully created an account at K&A Resort. Please use the code below to verify your email.</p>
                    
                    <div class="verification-code">
                        ' . $reset_code . '
                    </div>
                    
                    <p class="disclaimer">Didn\'t create a K&A Resort account? Ignore this email. Someone may have typed your email address by mistake.</p>
                </div>
                <div class="footer">
                    &copy; 2025 K&A Natural Spring Resort<br>
                    <a href="https://knaresort.com/terms">Terms and Privacy</a> &bull; <a href="https://knaresort.com/support">Support</a>
                </div>
            </div>
        </body>
        </html>
        ';

            $mail->AltBody = "Hello, This is your password reset code: {$reset_code}";
            $mail->send();

            $_SESSION['email_sent'] = "true";
            $_SESSION['success'] = "Verification code has been sent to your email";
            header("Location: send-code.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['Error'] = "Message could not be sent";
            header("Location: forgot-password.php");
            exit();
        }
    } else {
        $_SESSION['Error'] = "No user found with that email";
        header("Location: forgot-password.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/verify.styles.css">
    <title>Verify Email</title>
</head>

<body>
    <div class="container">
        <div class="form-container">
            <h1>Find your account</h1>
            <p class="sub-text">Please enter your email to search for your account.</p>
            <?php if (isset($_SESSION['success'])): ?>
                <p style="color: #77DD77; font-weight: 500; text-align: center; margin-bottom: 12px; font-style: italic;">
                    <?= $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                </p>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <p style="color: #F74141; font-weight: 500; text-align: center; margin-bottom: 12px; font-style: italic;">
                    <?= $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </p>
            <?php endif; ?>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="email">Enter email:</label>
                    <input type="text" id="code" placeholder="Enter the email" name="email" required>
                </div>
                <button type="submit">Search</button>
            </form>
        </div>
    </div>
</body>

</html>