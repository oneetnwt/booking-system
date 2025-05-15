<?php

session_start();

require '../db/connectDB.php';
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $phone = $_POST['phone_number'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    $recaptchaSecret = $_ENV['RECAPTCHA_SECRET_KEY'];
    $recaptchaResponse = $_POST['g-recaptcha-response'];

    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptchaSecret}&response={$recaptchaResponse}");
    $captchaSuccess = json_decode($verify);

    if (!$captchaSuccess->success) {
        $_SESSION['error'] = "Recaptcha Verification Failed";
        header('Location: signup.php');
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Password do not match";
        header('Location: signup.php');
        exit();
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long";
        header('Location: signup.php');
        exit();
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $_SESSION['error'] = "Password must contain at least one uppercase letter";
        header('Location: signup.php');
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Email exists";
        header('Location: signup.php');
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone_number = :phone_number");
    $stmt->bindParam(":phone_number", $phone);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Phone Number exists";
        header('Location: signup.php');
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $code = rand(100000, 999999);

    $_SESSION['pending_user'] = [
        'firstname' => $firstname,
        'lastname' => $lastname,
        'email' => $email,
        'phone' => $phone,
        'password' => $hashed_password,
        'code' => $code
    ];

    if (send_mail($email, $firstname, $lastname, $code)) {
        header("Location: verify.php");
    } else {
        $_SESSION['error'] = "Failed to send verification email";
        header('Location: signup.php');
    }
}

function send_mail($email, $firstname, $lastname, $code)
{
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();

    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['APP_EMAIL'];
        $mail->Password = $_ENV['APP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        //Recipients
        $mail->setFrom($_ENV['APP_EMAIL'], $_ENV['APP_NAME']);
        $mail->addAddress($email, $firstname . " " . $lastname);

        //Content
        $mail->isHTML(true);
        // Embed images - these will be referenced in the HTML with Content-IDs
        $logoPath = '../assets/K&A_Dark.png';  // Path to your logo file
        $iconPath = '../assets/K&ALogo.png';  // Path to your icon file

        // Add embedded images with Content-IDs
        $logoContentId = md5('logo') . rand(1000, 9999); // Create unique identifier for logo
        $iconContentId = md5('icon') . rand(1000, 9999); // Create unique identifier for icon

        // Add the images as embedded attachments
        $mail->addEmbeddedImage($logoPath, $logoContentId, 'logo.png');
        $mail->addEmbeddedImage($iconPath, $iconContentId, 'icon.png');

        $mail->Subject = 'Verify Email - K&A Resort';
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
                    <p>Hi ' . htmlspecialchars($firstname) . ',</p>
                    <p>You have successfully created an account at K&A Resort. Please use the code below to verify your email.</p>
                    
                    <div class="verification-code">
                        ' . $code . '
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
        $mail->AltBody = "Your verification code is: $code";

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}