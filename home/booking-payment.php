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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/booking.styles.css">
    <link rel="stylesheet" href="../styles/payment.styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Booking Process</title>
    <script src="../js/payment.js"></script>
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
                    <h3>Payment Method</h3>
                    <form action="" method="POST" id="payment-form">
                        <div class="payment-methods">
                            <div class="payment-method">
                                <input type="radio" id="paypal" name="payment_method" value="paypal" checked>
                                <label for="paypal">
                                    <img src="../assets/paypal.png" alt="PayPal"
                                        style="object-fit: cover; object-position: center;">
                                    <span>PayPal</span>
                                </label>
                            </div>
                            <div class="payment-method">
                                <input type="radio" id="gcash" name="payment_method" value="gcash">
                                <label for="gcash">
                                    <img src="../assets/gcash.png" alt="GCash">
                                    <span>GCash</span>
                                </label>
                            </div>
                            <div class="payment-method">
                                <input type="radio" id="bank" name="payment_method" value="bank">
                                <label for="bank">
                                    <i class="fa-solid fa-building-columns"></i>
                                    <span>Bank Transfer</span>
                                </label>
                            </div>
                        </div>

                        <div class="payment-details" id="paypal-details">
                            <h4>PayPal Details</h4>
                            <div class="form-group">
                                <label for="paypal_email">PayPal Email</label>
                                <input type="email" id="paypal_email" name="paypal_email"
                                    placeholder="Enter your PayPal email" required>
                            </div>
                        </div>

                        <div class="payment-details" id="gcash-details" style="display: none;">
                            <h4>GCash Details</h4>
                            <div class="form-group">
                                <label for="gcash_number">GCash Number</label>
                                <input type="text" id="gcash_number" name="gcash_number"
                                    placeholder="Enter your GCash number" pattern="[0-9]{11}">
                            </div>
                            <div class="form-group">
                                <label for="gcash_name">Account Name</label>
                                <input type="text" id="gcash_name" name="gcash_name" placeholder="Enter account name">
                            </div>
                        </div>

                        <div class="payment-details" id="bank-details" style="display: none;">
                            <h4>Bank Transfer Details</h4>
                            <div class="form-group">
                                <label for="bank_name">Bank Name</label>
                                <select id="bank_name" name="bank_name">
                                    <option value="">Select Bank</option>
                                    <option value="bdo">BDO</option>
                                    <option value="bpi">BPI</option>
                                    <option value="metrobank">Metrobank</option>
                                    <option value="unionbank">UnionBank</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="account_number">Account Number</label>
                                <input type="text" id="account_number" name="account_number"
                                    placeholder="Enter account number">
                            </div>
                            <div class="form-group">
                                <label for="account_name">Account Name</label>
                                <input type="text" id="account_name" name="account_name"
                                    placeholder="Enter account name">
                            </div>
                        </div>

                        <div class="buttons">
                            <a href="booking-process.php"><i class="fa-solid fa-arrow-left"></i>Back</a>
                            <button type="submit" class="a-submit">Pay Now <i
                                    class="fa-solid fa-arrow-right"></i></button>
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