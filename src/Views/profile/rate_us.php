<?php
require '../../vendor/autoload.php';
require '../db/connectDB.php';

use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

session_start();

if (!isset($_COOKIE['token'])) {
    header("Location: /auth/login");
    exit();
}

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$secret_key = $_ENV['JWT_SECRET_KEY'];
$token = $_COOKIE['token'];
$decoded = JWT::decode($token, new Key($secret_key, 'HS256'));

$user_id = $decoded->data->user_id;

// Get booking details
$booking_id = $_GET['id'] ?? null;
if (!$booking_id) {
    header("Location: my-bookings.php");
    exit();
}

$stmt = $pdo->prepare("
    SELECT 
        b.*,
        r.*,
        bi.*,
        bd.*
    FROM 
        booking b
    JOIN 
        room r ON b.room_id = r.id
    JOIN
        booking_invoice bi ON b.id = bi.booking_id
    JOIN
        booking_details bd ON b.booking_details_id = bd.id
    WHERE 
        bi.booking_id = ? AND bi.user_id = ?
");
$stmt->execute([$booking_id, $user_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header("Location: my-bookings.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = $_POST['rating'] ?? 0;
    $comment = $_POST['comment'] ?? '';

    try {
        $stmt = $pdo->prepare("
            INSERT INTO reviews (user_id, room_id, rating, comment, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $booking['room_id'], $rating, $comment]);

        header("Location: my-bookings.php");
        exit();
    } catch (PDOException $e) {
        $error = "Failed to submit rating. Please try again.";
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
    <title>Rate Your Experience - K&A Natural Spring Resort</title>
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <div class="sidebar-content">
                <div class="profile">
                    <img src="../assets/K&ALogo.png" height="75" width="75" alt="">
                    <div class="name">
                        <h3>Rate Your Experience</h3>
                        <p>Share your feedback with us</p>
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
                            <a href="/">
                                <i class="fa-solid fa-house"></i>Back to Home
                            </a>
                        </li>
                    </ul>
                </nav>
                <a href="/auth/logout" id="logout"><i class="fa fa-sign-out" aria-hidden="true"></i>Log out</a>
            </div>
        </div>
        <div class="main">
            <div class="main-content">
                <div class="rating-container">
                    <h3>Rate Your Stay</h3>
                    <?php if (isset($error)): ?>
                        <p class="error"><?= $error ?></p>
                    <?php endif; ?>

                    <div class="booking-details">
                        <img src="../assets/<?= $booking['image_path'] ?>" alt="Room Image">
                        <div class="details">
                            <h4><?= $booking['room_name'] ?></h4>
                            <p><i class="fas fa-calendar-check"></i> Check-in: <?= $booking['check_in'] ?></p>
                            <p><i class="fas fa-calendar-times"></i> Check-out: <?= $booking['check_out'] ?></p>
                            <p><i class="fas fa-users"></i> Adults: <?= $booking['adult'] ?></p>
                            <p><i class="fas fa-child"></i> Children: <?= $booking['child'] ?></p>
                            <p><i class="fas fa-moon"></i> Overnight: <?= ucfirst($booking['overnight']) ?></p>
                        </div>
                    </div>

                    <form method="POST" class="rating-form">
                        <div class="rating-stars">
                            <label>Your Rating:</label>
                            <div class="stars">
                                <input type="radio" id="star5" name="rating" value="5" required>
                                <label for="star5"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star4" name="rating" value="4">
                                <label for="star4"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star3" name="rating" value="3">
                                <label for="star3"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star2" name="rating" value="2">
                                <label for="star2"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star1" name="rating" value="1">
                                <label for="star1"><i class="fas fa-star"></i></label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="comment">Your Comments:</label>
                            <textarea id="comment" name="comment" rows="4"
                                placeholder="Share your experience with us..."></textarea>
                        </div>

                        <div class="button-group">
                            <button type="submit">Submit Rating</button>
                            <a href="my-bookings.php" class="cancel-btn">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const textarea = document.getElementById('comment');

        // Function to adjust textarea height
        function adjustTextareaHeight() {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        }

        // Adjust height on input
        textarea.addEventListener('input', adjustTextareaHeight);

        // Initial adjustment
        adjustTextareaHeight();
    </script>
</body>

</html>