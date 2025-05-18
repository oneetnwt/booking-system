<?php

require __DIR__ . "/../vendor/autoload.php";
require '../db/connectDB.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

session_start();

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$secret_key = $_ENV['JWT_SECRET_KEY'];
$user = null;

if (isset($_COOKIE['token'])) {
    try {
        $token = $_COOKIE['token'];
        $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));

        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$decoded->data->user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Handle invalid token
        header('Location: ../login.php');
        exit();
    }
} else {
    // No token found, redirect to login
    header('Location: ../login.php');
    exit();
}

// Check if booking details exist in session
if (!isset($_SESSION['booking_details'])) {
    header('Location: booking-confirmation.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $contact = $_POST['phone_number'];
    $comment = $_POST['comment'];

    $_SESSION['booking_details'] = [
        'room_id' => $_SESSION['booking_details']['room_id'],
        'days' => $_SESSION['booking_details']['days'],
        'check_in' => $_SESSION['booking_details']['check_in'],
        'check_out' => $_SESSION['booking_details']['check_out'],
        'adult' => $_SESSION['booking_details']['adult'],
        'children' => $_SESSION['booking_details']['children'],
        'overnight' => $_SESSION['booking_details']['overnight'],
        'roomPrice' => $_SESSION['booking_details']['roomPrice'],
        'entranceFee' => $_SESSION['booking_details']['entranceFee'],
        'bookingFee' => $_SESSION['booking_details']['bookingFee'],
        'totalPrice' => $_SESSION['booking_details']['totalPrice'],
        'firstname' => $firstname,
        'lastname' => $lastname,
        'email' => $email,
        'phone_number' => $contact,
        'comment' => $comment ?? ' '
    ];

    header('Location: booking-payment.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles//booking.styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../assets/K&ALogo.png">
    <title>Booking Process</title>
</head>

<body>
    <header>
        <div class="heading">
            <span id="active">1</span>
            Confirmation & Extras
        </div>
        <div class="heading">
            <span id="active">2</span>
            Guest Details
        </div>
        <div class="heading">
            <span>3</span>
            Payment
        </div>
    </header>
    <main class="main-content">
        <div class="container">
            <div class="content">
                <div class="details">
                    <h3>Guest Details</h3>
                    <form action="" method="POST">
                        <div class="name">
                            <div class="form-group">
                                <label for="firstname">First Name:</label>
                                <input type="text" placeholder="First Name" name="firstname"
                                    value="<?php echo htmlspecialchars($user['firstname'] ?? ''); ?>" disabled required>
                            </div>
                            <div class="form-group">
                                <label for="lastname">Last Name:</label>
                                <input type="text" placeholder="Last Name" name="lastname"
                                    value="<?php echo htmlspecialchars($user['lastname'] ?? ''); ?>" disabled required>
                            </div>
                        </div>
                        <div class="email">
                            <div class="form-group">
                                <label for="email">Email:</label>
                                <input type="text" placeholder="Email" name="email"
                                    value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled required>
                            </div>
                            <div class="form-group">
                                <label for="phone_number">Contact Number:</label>
                                <input type="number" placeholder="Contact Number"
                                    value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>"
                                    name="phone_number" disabled required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="comment">Additional Comments</label>
                            <textarea name="comment" id="comment" rows="5"></textarea>
                        </div>
                        <div class="buttons">
                            <a href="booking-confirmation.php"><i class="fa-solid fa-arrow-left"></i>Back</a>
                            <button type="submit" class="a-submit">Next <i class="fa-solid fa-arrow-right"></i></button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="order-details">
                <h3>Price Summary</h3>
                <div class="date">
                    <p><?= date('D, d M Y', strtotime($_SESSION['booking_details']['check_in'])); ?><br><span>From:
                            <?= date('h:i A', strtotime($_SESSION['booking_details']['check_in'])); ?></span></p>
                    <i class="fa-solid fa-arrow-right"></i>
                    <p><?= date('D, d M Y', strtotime($_SESSION['booking_details']['check_out'])); ?><br><span>Until:
                            <?= date('h:i A', strtotime($_SESSION['booking_details']['check_out'])); ?></span></p>
                </div>
                <div class="prices">
                    <div class="room">
                        <p>Room</p>
                        <p class="amount">₱
                            <?php echo isset($_SESSION['booking_details']['roomPrice']) ? number_format($_SESSION['booking_details']['roomPrice'], 2, '.', '') : '0.00'; ?>
                        </p>
                    </div>
                    <div class="entrance">
                        <p>Entrance Fee</p>
                        <p class="amount">₱ <?php
                        $entranceFee = $_SESSION['booking_details']['entranceFee'] ?? 0;
                        echo is_float($entranceFee) ? number_format($entranceFee, 2, '.', '') : $entranceFee . '.00';
                        ?></p>
                    </div>
                    <div class="booking-fee">
                        <p>Booking Fee (1%)</p>
                        <p class="amount">₱ <?php
                        $bookingFee = $_SESSION['booking_details']['bookingFee'] ?? 0;
                        echo is_float($bookingFee) ? number_format($bookingFee, 2, '.', '') : $bookingFee . '.00';
                        ?></p>
                    </div>
                    <div class="booking-fee">
                        <p>Number of Days</p>
                        <p class="amount">
                            <?= $_SESSION['booking_details']['days'] ?>
                        </p>
                    </div>
                </div>
                <div class="total">
                    <p>Total</p>
                    <p>₱ <?php
                    $totalPrice = $_SESSION['booking_details']['totalPrice'] ?? 0;
                    echo is_float($totalPrice) ? number_format($totalPrice, 2, '.', '') : $totalPrice . '.00';
                    ?>
                        <br>
                        <span>
                            Price includes ₱ <?php
                            $bookingFee = $_SESSION['booking_details']['bookingFee'] ?? 0;
                            echo is_float($bookingFee) ? number_format($bookingFee, 2, '.', '') : $bookingFee . '.00';
                            ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </main>
</body>

</html>