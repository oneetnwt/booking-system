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

$stmt = $pdo->prepare("
SELECT 
    bi.*, 
    b.*, 
    u.*,
    r.*,
    p.*
FROM 
    booking_invoice bi
JOIN 
    booking b ON bi.booking_id = b.id
JOIN 
    users u ON bi.user_id = u.id
JOIN
    room r on b.room_id = r.id
JOIN
    payment p on b.payment_id = p.id
WHERE 
    bi.user_id = ?
");
$stmt->execute([$user_id]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                        <li>
                            <a href="profile.php">
                                <i class="fas fa-user-edit"></i>Edit Profile
                            </a>
                        </li>
                        <li class="active">
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
                <div class="card-container">
                    <?php foreach ($data as $d): ?>
                        <div class="card">
                            <img src="" alt="">
                            <div class="details">
                                <div class="left">
                                    <h3>
                                        <?= $d['room_name']; ?>
                                    </h3>
                                    <p>
                                        <?= $d['check_in']; ?>
                                    </p>
                                    <p>
                                        <?= $d['check_out']; ?>
                                    </p>
                                </div>
                                <div class="right">
                                    <h3>
                                        <?= $d['amount']; ?>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>