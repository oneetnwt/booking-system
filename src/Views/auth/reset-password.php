<?php
session_start();

require __DIR__ . '/../../../vendor/autoload.php';
use App\Config\Database;
$pdo = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm-password'];

    if ($newPassword === $confirmPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashedPassword, $_SESSION['reset_email']]);

        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_code_verified']);

        $_SESSION['success'] = "Your password has been reset successfully. You can now log in with your new password.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "Passwords do not match. Please try again.";
        header("Location: new-password.php");
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
    <link rel="icon" href="../assets/K&ALogo.png">
    <title>Verify Email</title>
</head>

<body>
    <div class="container">
        <div class="form-container">
            <h1>Reset Your Password</h1>
            <p class="sub-text">Enter your new password.</p>
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
                    <label for="email">New Password:</label>
                    <input type="password" id="code" placeholder="Password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="email">Confirm Password:</label>
                    <input type="password" id="code" placeholder="Confirm Password" name="confirm-password" required>
                </div>
                <button type="submit">Reset Password</button>
            </form>
        </div>
    </div>
</body>

</html>