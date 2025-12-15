<?php
session_start();

require '../db/connectDB.php';

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $enteredCode = $_POST['code'];
    $enteredCode = (int) $enteredCode;
    $storedCode = $_SESSION['reset_code'] ?? null;

    if ($enteredCode === $storedCode) {
        unset($_SESSION['reset_code']);
        $_SESSION['success'] = "Code verified successfully. You can now reset your password.";
        header("Location: reset-password.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid code. Please try again.";
        header("Location: send-code.php");
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
            <h1>Enter security code</h1>
            <p class="sub-text">Please check your email for a message with your code. Your code is 6 numbers long.</p>
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
                    <label for="code">Enter the code:</label>
                    <input type="number" placeholder="Enter code" name="code" required>
                </div>
                <button type="submit">Continue</button>
            </form>
        </div>
    </div>
</body>

</html>