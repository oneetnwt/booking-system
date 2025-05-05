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
    bd.*,
    u.*,
    r.*,
    p.*
FROM 
    booking_invoice bi
JOIN 
    booking b ON bi.booking_id = b.id
JOIN
    booking_details bd ON b.booking_details_id = bd.id
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
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Categorize bookings
$allBookings = $bookings;
$completedBookings = [];
$upcomingBookings = [];

// Current date for comparison
$currentDate = date('Y-m-d');

foreach ($bookings as $booking) {
    // Check if the booking is completed (check-out date is in the past)
    if (strtotime($booking['check_out']) < strtotime($currentDate)) {
        $completedBookings[] = $booking;
    } else {
        // If check-out date is in the future or today, it's upcoming
        $upcomingBookings[] = $booking;
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
                <h3>My Bookings</h3>
                <nav style="margin-top: 1rem;">
                    <ul class="main-navlink">
                        <li id="all-nav" class="active" onclick="showAll()">
                            All
                        </li>
                        <li id="complete-nav" onclick="showCompleted()">
                            Completed
                        </li>
                        <li id="upcoming-nav" onclick="showUpcoming()">
                            Upcoming
                        </li>
                    </ul>
                </nav>

                <!-- All Bookings Section -->
                <div id="all" class="booking-section" style="display: block">
                    <div class="card-container">
                        <?php if (empty($allBookings)): ?>
                            <p class="no-bookings">No bookings found.</p>
                        <?php else: ?>
                            <?php foreach ($allBookings as $booking): ?>
                                <div class="card">
                                    <img src="../assets/<?= $booking['image_path'] ?>" alt="Room Image">
                                    <div class="details">
                                        <div class="left">
                                            <h3><?= $booking['room_name'] ?></h3>
                                            <p>Check-in: <?= $booking['check_in'] ?></p>
                                            <p>Check-out: <?= $booking['check_out'] ?></p>
                                            <p>Transaction ID: <?= $booking['transaction_id'] ?></p>
                                        </div>
                                        <div class="right">
                                            <p>₱ <?= $booking['room_price'] ?></p>
                                            <p>₱ <?= $booking['overnight'] === 'yes' ? $booking['adult'] * 200 : $booking['adult'] * 50 ?>.00
                                            </p>
                                            <p>₱ <?= $booking['overnight'] === 'yes' ? $booking['child'] * 200 : $booking['child'] * 50 ?>.00
                                            </p>
                                            <p>Booking Fee 1%: <?= $booking['booking_fee'] ?? 0 ?></p>
                                            <p style="color: #F74141; font-size: 1rem;"><?= strtoupper($booking['status']) ?>
                                            </p>
                                            <p id="totalAmount">Total Amount: ₱ <?= $booking['amount'] ?></p>
                                            <form action="booking-details.php" method="POST">
                                                <input type="hidden" name="bookingId" value="<?= $booking['booking_id'] ?>">
                                                <button>View Details</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Completed Bookings Section -->
                <div id="completed" class="booking-section" style="display: none">
                    <div class="card-container">
                        <?php if (empty($completedBookings)): ?>
                            <p class="no-bookings">No completed bookings found.</p>
                        <?php else: ?>
                            <?php foreach ($completedBookings as $booking): ?>
                                <div class="card">
                                    <img src="../assets/<?= $booking['image_path'] ?>" alt="Room Image">
                                    <div class="details">
                                        <div class="left">
                                            <h3><?= $booking['room_name'] ?></h3>
                                            <p>Check-in: <?= $booking['check_in'] ?></p>
                                            <p>Check-out: <?= $booking['check_out'] ?></p>
                                            <p>Transaction ID: <?= $booking['transaction_id'] ?></p>
                                        </div>
                                        <div class="right">
                                            <p>₱ <?= $booking['room_price'] ?></p>
                                            <p>₱ <?= $booking['overnight'] === 'yes' ? $booking['adult'] * 200 : $booking['adult'] * 50 ?>.00
                                            </p>
                                            <p>₱ <?= $booking['overnight'] === 'yes' ? $booking['child'] * 200 : $booking['child'] * 50 ?>.00
                                            </p>
                                            <p>Booking Fee 1%: <?= $booking['booking_fee'] ?? 0 ?></p>
                                            <p style="color: #F74141; font-size: 1rem;"><?= strtoupper($booking['status']) ?>
                                            </p>
                                            <p id="totalAmount">Total Amount: ₱ <?= $booking['amount'] ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Upcoming Bookings Section -->
                <div id="upcoming" class="booking-section" style="display: none">
                    <div class="card-container">
                        <?php if (empty($upcomingBookings)): ?>
                            <p class="no-bookings">No upcoming bookings found.</p>
                        <?php else: ?>
                            <?php foreach ($upcomingBookings as $booking): ?>
                                <div class="card">
                                    <img src="../assets/<?= $booking['image_path'] ?>" alt="Room Image">
                                    <div class="details">
                                        <div class="left">
                                            <h3><?= $booking['room_name'] ?></h3>
                                            <p>Check-in: <?= $booking['check_in'] ?></p>
                                            <p>Check-out: <?= $booking['check_out'] ?></p>
                                            <p>Transaction ID: <?= $booking['transaction_id'] ?></p>
                                        </div>
                                        <div class="right">
                                            <p>₱ <?= $booking['room_price'] ?></p>
                                            <p>₱ <?= $booking['overnight'] === 'yes' ? $booking['adult'] * 200 : $booking['adult'] * 50 ?>.00
                                            </p>
                                            <p>₱ <?= $booking['overnight'] === 'yes' ? $booking['child'] * 200 : $booking['child'] * 50 ?>.00
                                            </p>
                                            <p>Booking Fee 1%: <?= $booking['booking_fee'] ?? 0 ?></p>
                                            <p style="color: #F74141; font-size: 1rem;"><?= strtoupper($booking['status']) ?>
                                            </p>
                                            <p id="totalAmount">Total Amount: ₱ <?= $booking['amount'] ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        var allBtn = document.getElementById('all-nav');
        var completeBtn = document.getElementById('complete-nav');
        var upcomingBtn = document.getElementById('upcoming-nav');

        function showAll() {
            allBtn.classList.add('active');
            completeBtn.classList.remove('active');
            upcomingBtn.classList.remove('active');

            document.getElementById('all').style.display = 'block';
            document.getElementById('completed').style.display = 'none';
            document.getElementById('upcoming').style.display = 'none';
        }

        function showCompleted() {
            allBtn.classList.remove('active');
            completeBtn.classList.add('active');
            upcomingBtn.classList.remove('active');

            document.getElementById('all').style.display = 'none';
            document.getElementById('completed').style.display = 'block';
            document.getElementById('upcoming').style.display = 'none';
        }

        function showUpcoming() {
            allBtn.classList.remove('active');
            completeBtn.classList.remove('active');
            upcomingBtn.classList.add('active');

            document.getElementById('all').style.display = 'none';
            document.getElementById('completed').style.display = 'none';
            document.getElementById('upcoming').style.display = 'block';
        }
    </script>
</body>

</html>