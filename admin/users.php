<?php
session_start();

require_once '../db/connectDB.php';
require __DIR__ . "/../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$secret_key = $_ENV['JWT_SECRET_KEY'];

if (isset($_COOKIE['token'])) {
    $token = $_COOKIE['token'];
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
}


if (!isset($_COOKIE['token']) || $decoded->data->role !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all users
$stmt = $pdo->prepare("SELECT * FROM users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="styles/admin.styles.css">
    <title>Admin - User Management</title>
</head>

<body>
    <header>
        <div class="logo">
            <img src="../assets/K&ALogo.png" alt="" class="circle-logo">
            <img src="../assets/K&A.png" alt="" class="logo-text">
        </div>
        <a href="../auth/logout.php">
            Log out
            <span class="mdi mdi-logout"></span>
        </a>
    </header>
    <div class="main">
        <div class="sidebar">
            <div class="sidebar-content">
                <h3>ADMIN PANEL</h3>
                <nav>
                    <ul>
                        <li>
                            <a href="dashboard.php">
                                <span class="mdi mdi-view-dashboard"></span>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="bookings.php">
                                <span class="mdi mdi-file-tree"></span>
                                Bookings
                            </a>
                        </li>
                        <li class="active">
                            <a href="users.php">
                                <span class="mdi mdi-account-multiple"></span>
                                Users
                            </a>
                        </li>
                        <li>
                            <a href="reviews.php">
                                <span class="mdi mdi-star-box"></span>
                                Reviews and Ratings
                            </a>
                        </li>
                        <li>
                            <a href="rooms.php">
                                <span class="mdi mdi-bed"></span>
                                Rooms
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <div class="main-content">
            <div class="main-container">
                <h3>Users</h3>
                <div class="userTable">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email Address</th>
                                <th>Contact Number</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <tr>
                                            <td><?= $user['id'] ?></td>
                                            <td><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td><?= htmlspecialchars($user['phone_number']) ?></td>
                                            <td>
                                                <form action="" method="POST">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <button type="submit" class="block-btn">See bookings</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td><?= $user['id'] ?></td>
                                            <td><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td><?= htmlspecialchars($user['phone_number']) ?></td>
                                            <td><?= ucfirst(htmlspecialchars($user['role'])) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center;">No users found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>