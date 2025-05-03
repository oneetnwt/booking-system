<?php

require '../vendor/autoload.php';
require '../db/connectDB.php';

use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

session_start();

if (!isset($_COOKIE['token'])) {
    header("Location: ../auth/login.php");
    exit();
}

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$secret_key = $_ENV['JWT_SECRET_KEY'];
$token = $_COOKIE['token'];
$decoded = JWT::decode($token, new Key($secret_key, 'HS256'));

$user_id = $decoded->data->user_id;

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

function maskPhoneNumber($phoneNumber)
{
    // Remove any non-digit characters
    $cleanNumber = preg_replace('/\D/', '', $phoneNumber);

    // Check if we have at least 2 digits
    if (strlen($cleanNumber) < 2) {
        return $cleanNumber;
    }

    // Calculate how many digits to mask
    $maskedLength = strlen($cleanNumber) - 2;

    // Create the masked part with asterisks
    $maskedPart = str_repeat('*', $maskedLength);

    // Get the last 2 digits
    $visiblePart = substr($cleanNumber, -2);

    // Combine masked part and visible part
    return $maskedPart . $visiblePart;
}

// Get the masked phone number for display
$originalPhoneNumber = $user['phone_number'] ?? '';
$maskedPhoneNumber = !empty($originalPhoneNumber) ? maskPhoneNumber($originalPhoneNumber) : '';

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $firstname = isset($_POST['firstname']) ? $_POST['firstname'] : $user['firstname'];
    $lastname = isset($_POST['lastname']) ? $_POST['lastname'] : $user['lastname'];
    $email = isset($_POST['email']) ? $_POST['email'] : $user['email'];
    $phone_number = isset($_POST['phone_number']) ? $_POST['phone_number'] : $user['phone_number'];

    try {
        $stmt = $pdo->prepare("UPDATE users SET firstname = ?, lastname = ?, email = ?, phone_number = ? WHERE id = ?");
        $stmt->execute([$firstname, $lastname, $email, $phone_number, $user_id]);

        $_SESSION['success'] = "User data updated.";
        header("Location: profile.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Error in updating user data";
        header("Location: profile.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/profile.styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>K&A Natural Spring Resort</title>
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <div class="sidebar-content">
                <div class="profile">
                    <img src="../assets/K&ALogo.png" height="75" width="75" alt="">
                    <div class="name">
                        <h3>
                            <?= $user['firstname'] . ' ' . $user['lastname'] ?>
                        </h3>
                        <p>
                            <?= $user['email'] ?>
                        </p>
                    </div>
                </div>
                <nav>
                    <ul class="navlink">
                        <li class="active">
                            <a href="profile.php">
                                <i class="fas fa-user-edit"></i>Edit Profile
                            </a>
                        </li>
                        <li>
                            <a href="my-bookings.php">
                                <i class="fas fa-tasks"></i>My Bookings
                            </a>
                        </li>
                        <li>
                            <a href="../home/home.php">
                                <i class="fa-solid fa-house"></i>Back to Home
                            </a>
                        </li>
                    </ul>
                </nav>
                <a href="../auth/logout.php" id="logout"><i class="fa fa-sign-out" aria-hidden="true"></i>Log out</a>
            </div>
        </div>
        <div class="main">
            <div class="main-content">
                <div class="main-header">
                    <h3>My Profile</h3>
                    <p>Manage and protect your account</p>
                </div>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="">First Name:</label>
                        <input type="text" name="firstname" value="<?= htmlspecialchars($user['firstname']) ?>"
                            onfocus="this.select()">
                    </div>
                    <div class="form-group">
                        <label for="">Last Name:</label>
                        <input type="text" name="lastname" value="<?= htmlspecialchars($user['lastname']) ?>"
                            onfocus="this.select()">
                    </div>
                    <div class="form-group">
                        <label for="">Email:</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                            onfocus="this.select()">
                    </div>
                    <div class="form-group">
                        <label for="">Phone Number:</label>
                        <input type="text" name="phone_number_masked"
                            value="<?= htmlspecialchars($maskedPhoneNumber) ?>"
                            onfocus="this.value='<?= htmlspecialchars($user['phone_number']) ?>'; document.getElementById('real_phone').value='<?= htmlspecialchars($user['phone_number']) ?>'; this.select();">
                        <input type="hidden" name="phone_number" id="real_phone"
                            value="<?= htmlspecialchars($user['phone_number']) ?>">
                    </div>
                    <button>Save</button>
                </form>
                <?php if (isset($_SESSION['error'])): ?>
                    <p
                        style="color: #F74141; font-weight: 500; text-align: center; margin-bottom: 12px; font-style: italic;">
                        <?= $_SESSION['error'];
                        unset($_SESSION['error']); ?>
                    </p>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <p
                        style="color: #4CAF50; font-weight: 500; text-align: center; margin-bottom: 12px; font-style: italic;">
                        <?= $_SESSION['success'];
                        unset($_SESSION['success']); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>