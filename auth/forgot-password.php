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
            $mail->Subject = "Password Reset Code";
            $mail->Body = "
          <p>Hello, This is your password reset code</p>

          <div>{$reset_code}</div>
        ";

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