<?php
session_start();
require_once '../db/connectDB.php';
require_once '../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$secret_key = $_ENV['JWT_SECRET_KEY'];

// Check if user is logged in and is admin
if (!isset($_COOKIE['token'])) {
    header("Location: ../auth/login.php");
    exit();
}

try {
    $token = $_COOKIE['token'];
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));

    if ($decoded->data->role !== 'admin') {
        header("Location: ../home/home.php");
        exit();
    }
} catch (Exception $e) {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: bookings.php");
    exit();
}

$booking_id = (int) $_GET['id'];

try {
    $stmt = $pdo->prepare("
        SELECT b.*, u.firstname, u.lastname, u.email, u.phone, r.room_name, r.room_type, r.price, p.amount as total_amount, p.payment_method, p.payment_status, bd.check_in, bd.check_out, bd.guests
        FROM booking b 
        JOIN booking_invoice bi ON b.id = bi.booking_id
        JOIN users u ON bi.user_id = u.id 
        JOIN room r ON b.room_id = r.id 
        LEFT JOIN payment p ON b.payment_id = p.id
        LEFT JOIN booking_details bd ON b.booking_details_id = bd.id
        WHERE b.id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        header("Location: bookings.php");
        exit();
    }
} catch (PDOException $e) {
    header("Location: bookings.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - K&A Resort</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/admin.styles.css">
</head>

<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/K&A.png" alt="K&A Resort Logo">
                <h2>Admin Panel</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="dashboard.php">
                            <i class="fas fa-home"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="active">
                        <a href="bookings.php">
                            <i class="fas fa-calendar-check"></i>
                            Bookings
                        </a>
                    </li>
                    <li>
                        <a href="users.php">
                            <i class="fas fa-users"></i>
                            Users
                        </a>
                    </li>
                    <li>
                        <a href="reviews.php">
                            <i class="fas fa-star"></i>
                            Reviews
                        </a>
                    </li>
                    <li>
                        <a href="rooms.php">
                            <i class="fas fa-bed"></i>
                            Rooms
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="main-header">
                <div class="header-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search...">
                </div>
                <div class="header-actions">
                    <div class="notifications">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </div>
                    <div class="admin-profile">
                        <img src="../assets/default-avatar.png" alt="Admin">
                        <span><?php echo $decoded->data->firstname . ' ' . $decoded->data->lastname; ?></span>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="content-header">
                    <h1>Booking Details</h1>
                    <div class="header-actions">
                        <a href="bookings.php" class="btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Bookings
                        </a>
                        <button class="btn-primary" onclick="updateStatus()">
                            <i class="fas fa-edit"></i> Update Status
                        </button>
                    </div>
                </div>

                <div class="booking-details">
                    <div class="details-section">
                        <h2>Booking Information</h2>
                        <div class="details-grid">
                            <div class="detail-item">
                                <span class="label">Booking ID:</span>
                                <span class="value">#<?php echo $booking['id']; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Status:</span>
                                <span class="value">
                                    <span class="status-badge <?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Check-in Date:</span>
                                <span
                                    class="value"><?php echo date('F d, Y', strtotime($booking['check_in'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Check-out Date:</span>
                                <span
                                    class="value"><?php echo date('F d, Y', strtotime($booking['check_out'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Number of Guests:</span>
                                <span class="value"><?php echo $booking['guests']; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Booking Date:</span>
                                <span
                                    class="value"><?php echo date('F d, Y', strtotime($booking['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="details-section">
                        <h2>Guest Information</h2>
                        <div class="details-grid">
                            <div class="detail-item">
                                <span class="label">Name:</span>
                                <span
                                    class="value"><?php echo $booking['firstname'] . ' ' . $booking['lastname']; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Email:</span>
                                <span class="value"><?php echo $booking['email']; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Phone:</span>
                                <span class="value"><?php echo $booking['phone']; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="details-section">
                        <h2>Room Information</h2>
                        <div class="details-grid">
                            <div class="detail-item">
                                <span class="label">Room Name:</span>
                                <span class="value"><?php echo $booking['room_name']; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Room Type:</span>
                                <span class="value"><?php echo ucfirst($booking['room_type']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Price per Night:</span>
                                <span class="value">₱<?php echo number_format($booking['price'], 2); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="details-section">
                        <h2>Payment Information</h2>
                        <div class="details-grid">
                            <div class="detail-item">
                                <span class="label">Total Amount:</span>
                                <span
                                    class="value">₱<?php echo number_format($booking['total_amount'] ?? 0, 2); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Payment Method:</span>
                                <span class="value"><?php echo ucfirst($booking['payment_method'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Payment Status:</span>
                                <span class="value">
                                    <span class="status-badge <?php echo $booking['payment_status'] ?? 'pending'; ?>">
                                        <?php echo ucfirst($booking['payment_status'] ?? 'Pending'); ?>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Update Booking Status</h2>
            <form id="statusForm" method="POST" action="bookings.php">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select name="status" id="status" required>
                        <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending
                        </option>
                        <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>
                            Confirmed</option>
                        <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>
                            Cancelled</option>
                        <option value="done" <?php echo $booking['status'] === 'done' ? 'selected' : ''; ?>>Done</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">Update Status</button>
            </form>
        </div>
    </div>

    <script>
        // Modal functionality
        const modal = document.getElementById('statusModal');
        const closeBtn = document.getElementsByClassName('close')[0];

        function updateStatus() {
            modal.style.display = 'block';
        }

        closeBtn.onclick = function () {
            modal.style.display = 'none';
        }

        window.onclick = function (event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>

</html>