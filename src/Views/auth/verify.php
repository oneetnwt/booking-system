<?php
session_start();

if (!$_SESSION['pending_user']) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $input_code = $_POST['code'];
    $stored = $_SESSION['pending_user'];

    if ($stored && $input_code == $stored['code']) {
        require '../db/connectDB.php';
        $stmt = $pdo->prepare("INSERT INTO users (firstname, lastname, email, phone_number, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $stored['firstname'],
            $stored['lastname'],
            $stored['email'],
            $stored['phone'],
            $stored['password']
        ]);

        unset($_SESSION['pending_user']);
        $_SESSION['success'] = "User created successfully";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid Code";
        header("Location: verify.php");
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
            <h1>Email Confirmation</h1>
            <p class="sub-text">Please enter the code we sent to your email.</p>
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
            <form id="signup-form" action="" method="POST">
                <div class="form-group">
                    <label for="code">Enter Code:</label>
                    <input type="number" id="code" placeholder="Enter the code" name="code" required>
                </div>
                <button type="submit">Verify</button>
            </form>
        </div>
    </div>
</body>

</html>