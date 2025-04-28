<?php

session_start();

require '../../db/connectDB.php';
require __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../../");
$dotenv->load();

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $phone = $_POST['phone_number'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

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
        $mail->Subject = 'Verification Code';
        $mail->Body = "Your verification code is: <b>$code</b>";
        $mail->AltBody = "Your verification code is: $code";

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}