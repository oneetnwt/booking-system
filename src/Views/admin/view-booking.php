<?php
session_start();
require_once __DIR__ . '/../../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Config\Database;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 3));
$dotenv->load();

$secret_key = $_ENV['JWT_SECRET_KEY'];
$pdo = Database::getInstance()->getConnection();

// Check if user is logged in and is admin
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

if (!isset($_GET['id'])) {
    header("Location: bookings.php");
    exit();
}

$booking_id = (int) $_GET['id'];

try {
    $stmt = $pdo->prepare("
            SELECT b.*, u.*, r.*, p.*, bd.*
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
    echo "Error";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/styles/admin.styles.css">
    <link rel="icon" href="../assets/K&ALogo.png">
    <title>Booking Details - K&A Resort</title>
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
                        <li>
                            <a href="dashboard.php">
                                <span class="mdi mdi-view-dashboard"></span>
                                Dashboard
                            </a>
                        </li>
                        <li class="active">
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
                                        <?php echo ucfirst($booking['status'] === 'done' ? "Completed" : "Pending"); ?>
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
                                <span class="value"><?php echo $booking['adult'] + $booking['child']; ?></span>
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
                                <span class="value"><?php echo $booking['phone_number']; ?></span>
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
                                <span class="label">Price per Night:</span>
                                <span class="value">₱<?php echo number_format($booking['room_price'], 2); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="details-section">
                        <h2>Payment Information</h2>
                        <div class="details-grid">
                            <div class="detail-item">
                                <span class="label">Total Amount:</span>
                                <span class="value">₱<?php echo number_format($booking['amount'] ?? 0, 2); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Payment Method:</span>
                                <span class="value"><?php echo ucfirst($booking['payment_method'] ?? 'N/A'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                        <option value="done" <?php echo $booking['status'] === 'done' ? 'selected' : ''; ?>></option>
                        Completed</option>
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