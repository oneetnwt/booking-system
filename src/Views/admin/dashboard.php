<?php
session_start();

require_once '../db/connectDB.php';
require_once '../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$secret_key = $_ENV['JWT_SECRET_KEY'];


if (!isset($_COOKIE['token'])) {
    header("Location: /auth/login");
    exit();
}

try {
    $token = $_COOKIE['token'];
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));

    if ($decoded->data->role !== 'admin') {
        header("Location: /home");
        exit();
    }
} catch (Exception $e) {
    header("Location: /auth/login");
    exit();
}

// Summary Stats
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$total_bookings = $pdo->query("SELECT COUNT(*) FROM booking")->fetchColumn();
$total_rooms = $pdo->query("SELECT COUNT(*) FROM room")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(amount) FROM payment")->fetchColumn() ?? 0;

// Recent Bookings
$recent_bookings = $pdo->query("
    SELECT b.id, u.firstname, u.lastname, r.room_name, b.status, p.amount, b.created_at
    FROM booking b
    JOIN booking_invoice bi ON b.id = bi.booking_id
    JOIN users u ON bi.user_id = u.id
    JOIN room r ON b.room_id = r.id
    LEFT JOIN payment p ON b.payment_id = p.id
    ORDER BY b.created_at DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Recent Reviews
$recent_reviews = $pdo->query("
    SELECT r.id, u.firstname, u.lastname, rm.room_name, r.*
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN room rm ON r.room_id = rm.id
    ORDER BY r.created_at ASC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="styles/admin.styles.css">
    <link rel="icon" href="../assets/K&ALogo.png">

    <title>Admin - User Management</title>
</head>

<body>
    <header>
        <div class="logo">
            <img src="../assets/K&ALogo.png" alt="" class="circle-logo">
            <img src="../assets/K&A.png" alt="" class="logo-text">
        </div>
        <a href="/">
            <span class="mdi mdi-home"></span>
            Home
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
                <h3>Dashboard</h3>
                <div class="dashboard-main" style="display: flex; gap: 2rem; flex-wrap: wrap; margin-bottom: 2rem;">
                    <div class="ds-card">
                        <h3>Total Users</h3>
                        <p><?= $total_users ?></p>
                    </div>
                    <div class="ds-card">
                        <h3>Total Bookings</h3>
                        <p><?= $total_bookings ?></p>
                    </div>
                    <div class="ds-card">
                        <h3>Total Rooms</h3>
                        <p><?= $total_rooms ?></p>
                    </div>
                    <div class="ds-card">
                        <h3 style="color: #27AE60">Total Revenue</h3>
                        <p style="color: #27AE60">₱<?= number_format($total_revenue, 2) ?></p>
                    </div>
                </div>
                <div style=" display: flex; flex-direction: column; gap: 2rem;">
                    <div>
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h3>Recent Bookings</h3>
                            <a href="../lib/generate_pdf.php" class="btn-primary"
                                style="text-decoration: none; padding: 0.5rem 1rem; font-size: 0.9rem;">
                                <i class="fas fa-file-pdf"></i>
                                Generate Report
                            </a>
                        </div>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Guest</th>
                                        <th>Room</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_bookings as $b): ?>
                                        <?php if ($b['status'] == 'pending'): ?>
                                            <tr>
                                                <td>#<?= $b['id'] ?></td>
                                                <td><?= htmlspecialchars($b['firstname'] . ' ' . $b['lastname']) ?></td>
                                                <td><?= htmlspecialchars($b['room_name']) ?></td>
                                                <td><span
                                                        class="status-badge <?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span>
                                                </td>
                                                <td>₱<?= number_format($b['amount'] ?? 0, 2) ?></td>
                                                <td><?= date('M d, Y', strtotime($b['created_at'])) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Function to load reviews
        function loadReviews() {
            fetch('get_review.php')
                .then(response => response.json())
                .then(reviews => {
                    const tbody = document.getElementById('reviewsTableBody');
                    tbody.innerHTML = '';

                    reviews.forEach(review => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                    <td>#${review.id}</td>
                    <td>${review.firstname} ${review.lastname}</td>
                    <td>${review.room_name}</td>
                    <td>
                        <div class="stars">
                            ${Array(5).fill().map((_, i) =>
                            `<i class="fas fa-star ${i < review.rating ? 'active' : ''}"></i>`
                        ).join('')}
                        </div>
                    </td>
                    <td>${review.comment || ''}</td>
                    <td>${new Date(review.created_at).toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric'
                        })}</td>
                `;
                        tbody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Error loading reviews:', error);
                });
        }

        // Load reviews when page loads
        document.addEventListener('DOMContentLoaded', loadReviews);
    </script>
</body>

</html>