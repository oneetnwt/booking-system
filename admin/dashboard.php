<?php
session_start();

// Uncomment this for production to ensure only admins can access
// if (!isset($_COOKIE['admin_token'])) {
//     header("Location: ../auth/login.php");
//     exit();
// }

require_once '../db/connectDB.php';

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
                        <li class="active">
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
                        <li>
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
                <h3>DASHBOARD</h3>
                <div class="dashboard-main">
                    <div class="ds-card">
                        <h3>New Bookings</h3>
                        <p>
                            <?php
                            $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM booking WHERE status = 'pending'");
                            $stmt->execute();
                            $count = $stmt->fetch(PDO::FETCH_ASSOC);
                            echo $count['total'];
                            ?>
                        </p>
                    </div>
                    <div class="ds-card">
                        <h3>Completed Bookings</h3>
                        <p>
                            <?php
                            $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM booking WHERE status = 'done'");
                            $stmt->execute();
                            $count = $stmt->fetch(PDO::FETCH_ASSOC);
                            echo $count['total'];
                            ?>
                        </p>
                    </div>
                </div>
                <div class="booking-analytics">
                    <h3>Booking Analytics</h3>
                    <div class="ds-card">
                        <h3>Total Bookings</h3>
                        <p>
                            <?php
                            $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM users");
                            $stmt->execute();
                            $userCount = $stmt->fetch(PDO::FETCH_ASSOC);
                            echo $userCount['total'];
                            ?>
                        </p>
                        <p style="font-size: 1rem;">₱
                            <?php
                            $stmt = $pdo->prepare("SELECT SUM(amount) AS total FROM payment");
                            $stmt->execute();
                            $userCount = $stmt->fetch(PDO::FETCH_ASSOC);
                            echo number_format($userCount['total'], 2);
                            ?>
                        </p>
                    </div>
                </div>
                <div class="user-reviews">
                    <h3>User and Reviews Analytics</h3>
                    <div class="ds-card">
                        <h3>Total Users</h3>
                        <p>
                            <?php
                            $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM users");
                            $stmt->execute();
                            $userCount = $stmt->fetch(PDO::FETCH_ASSOC);
                            echo $userCount['total'];
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>