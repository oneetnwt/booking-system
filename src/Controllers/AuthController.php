<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\JwtService;
use App\Services\EmailService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController
{
    private $pdo;
    private $jwtService;
    private $emailService;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
        $this->jwtService = new JwtService();
        $this->emailService = new EmailService();
    }

    public function showLogin()
    {
        session_start();
        
        if (isset($_COOKIE['token'])) {
            header("Location: /home");
            exit();
        }
        
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    public function login()
    {
        session_start();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /auth/login");
            exit();
        }

        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $token = $this->jwtService->generateToken([
                'user_id' => $user['id'],
                'firstname' => $user['firstname'],
                'lastname' => $user['lastname'],
                'role' => $user['role']
            ]);

            setcookie("token", $token, time() + 3600, "/", "", true, true);

            header("Location: /home");
            exit();
        } else {
            $_SESSION['error'] = "User not found";
            header("Location: /auth/login");
            exit();
        }
    }

    public function showSignup()
    {
        session_start();
        unset($_SESSION['form_data']);
        require_once __DIR__ . '/../Views/auth/signup.php';
    }

    public function signup()
    {
        session_start();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /auth/signup");
            exit();
        }

        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $phone = $_POST['phone_number'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm-password'];

        $_SESSION['form_data'] = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'phone_number' => $phone
        ];

        $recaptchaSecret = $_ENV['RECAPTCHA_SECRET_KEY'];
        $recaptchaResponse = $_POST['g-recaptcha-response'];

        $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptchaSecret}&response={$recaptchaResponse}");
        $captchaSuccess = json_decode($verify);

        if (!$captchaSuccess->success) {
            $_SESSION['error'] = 'Recaptcha Verification Failed';
            header('Location: /auth/signup');
            exit();
        }

        if ($password !== $confirm_password) {
            $_SESSION['error'] = 'Password do not match';
            header('Location: /auth/signup');
            exit();
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = 'Password must be at least 8 characters long';
            header('Location: /auth/signup');
            exit();
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $_SESSION['error'] = 'Password must contain at least one uppercase letter';
            header('Location: /auth/signup');
            exit();
        }

        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = 'Email exists';
            header('Location: /auth/signup');
            exit();
        }

        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE phone_number = :phone_number');
        $stmt->bindParam(':phone_number', $phone);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = 'Phone Number exists';
            header('Location: /auth/signup');
            exit();
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $code = rand(100000, 999999);

        unset($_SESSION['form_data']);

        $_SESSION['pending_user'] = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'phone' => $phone,
            'password' => $hashed_password,
            'code' => $code
        ];

        if ($this->emailService->sendVerificationEmail($email, $firstname, $lastname, $code)) {
            header('Location: /auth/verify');
            exit();
        } else {
            $_SESSION['error'] = 'Failed to send verification email';
            header('Location: /auth/signup');
            exit();
        }
    }

    public function showVerify()
    {
        session_start();
        require_once __DIR__ . '/../Views/auth/verify.php';
    }

    public function showForgotPassword()
    {
        session_start();
        require_once __DIR__ . '/../Views/auth/forgot-password.php';
    }

    public function forgotPassword()
    {
        session_start();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /auth/forgot-password");
            exit();
        }

        $email = $_POST['email'];

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($user) {
            $reset_code = rand(100000, 999999);

            $_SESSION['reset_code'] = $reset_code;
            $_SESSION['reset_email'] = $email;

            if ($this->emailService->sendPasswordResetEmail($email, $user['firstname'], $reset_code)) {
                $_SESSION['email_sent'] = "true";
                $_SESSION['success'] = "Verification code has been sent to your email";
                header("Location: /auth/send-code");
                exit();
            } else {
                $_SESSION['Error'] = "Message could not be sent";
                header("Location: /auth/forgot-password");
                exit();
            }
        } else {
            $_SESSION['Error'] = "No user found with that email";
            header("Location: /auth/forgot-password");
            exit();
        }
    }

    public function showSendCode()
    {
        session_start();
        require_once __DIR__ . '/../Views/auth/send-code.php';
    }

    public function showResetPassword()
    {
        session_start();
        require_once __DIR__ . '/../Views/auth/reset-password.php';
    }

    public function logout()
    {
        setcookie("token", "", time() - 3600, "/", "", true, true);
        header("Location: /home");
        exit();
    }
}
