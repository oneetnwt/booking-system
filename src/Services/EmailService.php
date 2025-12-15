<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->configureMail();
    }

    private function configureMail()
    {
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $_ENV['APP_EMAIL'];
        $this->mail->Password = $_ENV['APP_PASSWORD'];
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;
        $this->mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $this->mail->setFrom($_ENV['APP_EMAIL'], $_ENV['APP_NAME']);
        $this->mail->isHTML(true);
    }

    public function sendVerificationEmail($email, $firstname, $lastname, $code)
    {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($email, $firstname . ' ' . $lastname);

            $logoPath = __DIR__ . '/../../public/assets/K&A_Dark.png';
            $iconPath = __DIR__ . '/../../public/assets/K&ALogo.png';

            if (!file_exists($logoPath) || !file_exists($iconPath)) {
                error_log("Email asset files not found: $logoPath or $iconPath");
                throw new Exception("Email asset files not found");
            }

            $logoContentId = md5('logo') . rand(1000, 9999);
            $iconContentId = md5('icon') . rand(1000, 9999);

            $this->mail->addEmbeddedImage($logoPath, $logoContentId, 'logo.png');
            $this->mail->addEmbeddedImage($iconPath, $iconContentId, 'icon.png');

            $this->mail->Subject = 'Verify Email - K&A Resort';
            $this->mail->Body = $this->getVerificationEmailTemplate($firstname, $code, $logoContentId, $iconContentId);
            $this->mail->AltBody = 'Your verification code is: ' . $code;

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Email sending failed: ' . $this->mail->ErrorInfo);
            return false;
        }
    }

    public function sendPasswordResetEmail($email, $firstname, $reset_code)
    {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($email, $firstname);

            $logoPath = __DIR__ . '/../../public/assets/K&A_Dark.png';
            $iconPath = __DIR__ . '/../../public/assets/K&ALogo.png';

            if (!file_exists($logoPath) || !file_exists($iconPath)) {
                error_log("Email asset files not found: $logoPath or $iconPath");
                throw new Exception("Email asset files not found");
            }

            $logoContentId = md5('logo') . rand(1000, 9999);
            $iconContentId = md5('icon') . rand(1000, 9999);

            $this->mail->addEmbeddedImage($logoPath, $logoContentId, 'logo.png');
            $this->mail->addEmbeddedImage($iconPath, $iconContentId, 'icon.png');

            $this->mail->Subject = 'Password Reset Code - K&A Resort';
            $this->mail->Body = $this->getPasswordResetEmailTemplate($firstname, $reset_code, $logoContentId, $iconContentId);
            $this->mail->AltBody = 'Your password reset code is: ' . $reset_code;

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Email sending failed: ' . $this->mail->ErrorInfo);
            return false;
        }
    }

    private function getVerificationEmailTemplate($firstname, $code, $logoContentId, $iconContentId)
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f5f8fa; }
                .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e1e8ed; border-radius: 5px; overflow: hidden; }
                .header { padding: 20px; text-align: left; border-bottom: 1px solid #e1e8ed; }
                .logo { height: 40px; }
                .content { padding: 30px; background-color: #f0f8ff; color: #333333; border-radius: 5px; margin: 20px; border: 1px solid #d1e0ed; }
                .verification-code { background-color: #4285f4; color: white; font-size: 24px; font-weight: bold; padding: 12px 20px; text-align: center; border-radius: 4px; margin: 25px 0; letter-spacing: 10px; }
                .footer { padding: 15px; text-align: center; font-size: 12px; color: #657786; }
                .disclaimer { font-size: 11px; color: #888; text-align: center; margin-top: 15px; }
                a { color: #657786; text-decoration: none; }
                a:hover { text-decoration: underline; }
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
                    <div class="verification-code">' . $code . '</div>
                    <p class="disclaimer">Didn\'t create a K&A Resort account? Ignore this email. Someone may have typed your email address by mistake.</p>
                </div>
                <div class="footer">
                    &copy; 2025 K&A Natural Spring Resort<br>
                    <a href="https://knaresort.com/terms">Terms and Privacy</a> &bull; <a href="https://knaresort.com/support">Support</a>
                </div>
            </div>
        </body>
        </html>';
    }

    private function getPasswordResetEmailTemplate($firstname, $reset_code, $logoContentId, $iconContentId)
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f5f8fa; }
                .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e1e8ed; border-radius: 5px; overflow: hidden; }
                .header { padding: 20px; text-align: left; border-bottom: 1px solid #e1e8ed; }
                .logo { height: 40px; }
                .content { padding: 30px; background-color: #f0f8ff; color: #333333; border-radius: 5px; margin: 20px; border: 1px solid #d1e0ed; }
                .verification-code { background-color: #4285f4; color: white; font-size: 24px; font-weight: bold; padding: 12px 20px; text-align: center; border-radius: 4px; margin: 25px 0; letter-spacing: 10px; }
                .footer { padding: 15px; text-align: center; font-size: 12px; color: #657786; }
                .disclaimer { font-size: 11px; color: #888; text-align: center; margin-top: 15px; }
                a { color: #657786; text-decoration: none; }
                a:hover { text-decoration: underline; }
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
                    <p>We received a request to reset your password for your K&A Resort account. Please use the code below to reset your password.</p>
                    <div class="verification-code">' . $reset_code . '</div>
                    <p class="disclaimer">If you didn\'t request a password reset, please ignore this email. Your account security is important to us.</p>
                </div>
                <div class="footer">
                    &copy; 2025 K&A Natural Spring Resort<br>
                    <a href="https://knaresort.com/terms">Terms and Privacy</a> &bull; <a href="https://knaresort.com/support">Support</a>
                </div>
            </div>
        </body>
        </html>';
    }
}
