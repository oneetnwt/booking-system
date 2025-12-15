<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Config\Database;

session_start();

require __DIR__ . '/../../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 3));
$dotenv->load();

$secret_key = $_ENV['JWT_SECRET_KEY'];
$pdo = Database::getInstance()->getConnection();

$token = $_COOKIE['token'];

$decoded = JWT::decode($token, new Key($secret_key, 'HS256'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'];
    $user = $decoded->data->user_id;
    $transaction_id = uniqid('KA_', true);
    $_SESSION['transact'] = $transaction_id;

    switch ($payment_method) {
        case 'paypal':
            $paypal_email = $_POST['paypal_email'];

            // Insert data to table payment
            $stmt = $pdo->prepare("INSERT INTO payment(user_id, amount, payment_method) VALUES (?, ?, ?)");
            $stmt->execute([$user, $_SESSION['booking_details']['totalPrice'], $payment_method]);

            $payment_id = $pdo->lastInsertId();

            // Insert data to paypal
            $stmt = $pdo->prepare("INSERT INTO paypal_payment(payment_id, paypal_email) VALUES (?, ?)");
            $stmt->execute([$payment_id, $paypal_email]);

            // Booking details
            $stmt = $pdo->prepare("INSERT INTO booking_details(check_in, check_out, adult, child, overnight, booking_fee) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['booking_details']['check_in'], $_SESSION['booking_details']['check_out'], $_SESSION['booking_details']['adult'], $_SESSION['booking_details']['children'], $_SESSION['booking_details']['overnight'], $_SESSION['booking_details']['bookingFee']]);

            $bdid = $pdo->lastInsertId();

            // Create booking record
            $stmt = $pdo->prepare("INSERT INTO booking(room_id, payment_id, booking_details_id, status, comment, transaction_id) VALUES (?, ?, ?, 'pending', ?, ?)");
            $stmt->execute([
                $_SESSION['booking_details']['room_id'],
                $payment_id,
                $bdid,
                $_SESSION['booking_details']['comment'] ?? '',
                $transaction_id
            ]);

            $booking_id = $pdo->lastInsertId();

            // Create booking invoice
            $stmt = $pdo->prepare("INSERT INTO booking_invoice(user_id, booking_id) VALUES (?, ?)");
            $stmt->execute([$user, $booking_id]);

            header("Location: /booking/invoice");
            exit();
        case 'gcash':
            $gcash_number = $_POST['gcash_number'];
            $gcash_name = $_POST['gcash_name'];

            // Insert payment record
            $stmt = $pdo->prepare("INSERT INTO payment(user_id, amount, payment_method) VALUES (?, ?, ?)");
            $stmt->execute([$user, $_SESSION['booking_details']['totalPrice'], $payment_method]);

            $payment_id = $pdo->lastInsertId();

            // Insert GCash details
            $stmt = $pdo->prepare("INSERT INTO gcash_payment(payment_id, gcash_number, gcash_name) VALUES (?, ?, ?)");
            $stmt->execute([$payment_id, $gcash_number, $gcash_name]);

            // Create booking details
            $stmt = $pdo->prepare("INSERT INTO booking_details(check_in, check_out, adult, child, overnight, booking_fee) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['booking_details']['check_in'], $_SESSION['booking_details']['check_out'], $_SESSION['booking_details']['adult'], $_SESSION['booking_details']['children'], $_SESSION['booking_details']['overnight'], $_SESSION['booking_details']['bookingFee']]);

            $bdid = $pdo->lastInsertId();

            // Create booking record
            $stmt = $pdo->prepare("INSERT INTO booking(room_id, payment_id, booking_details_id, status, comment, transaction_id) VALUES (?, ?, ?, 'pending', ?, ?)");
            $stmt->execute([
                $_SESSION['booking_details']['room_id'],
                $payment_id,
                $bdid,
                $_SESSION['booking_details']['comment'] ?? '',
                $transaction_id
            ]);

            $booking_id = $pdo->lastInsertId();

            // Create booking invoice
            $stmt = $pdo->prepare("INSERT INTO booking_invoice(user_id, booking_id) VALUES (?, ?)");
            $stmt->execute([$user, $booking_id]);

            header("Location: /booking/invoice");
            exit();
        case 'cash':
            // Create payment record
            $stmt = $pdo->prepare("INSERT INTO payment(user_id, amount, payment_method, payment_status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$user, $_SESSION['booking_details']['totalPrice'], $payment_method]);
            $payment_id = $pdo->lastInsertId();

            // Create booking details
            $stmt = $pdo->prepare("INSERT INTO booking_details(check_in, check_out, adult, child, overnight, booking_fee) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['booking_details']['check_in'],
                $_SESSION['booking_details']['check_out'],
                $_SESSION['booking_details']['adult'],
                $_SESSION['booking_details']['children'],
                $_SESSION['booking_details']['overnight'],
                $_SESSION['booking_details']['bookingFee']
            ]);
            $bdid = $pdo->lastInsertId();

            // Create booking record
            $stmt = $pdo->prepare("INSERT INTO booking(room_id, payment_id, booking_details_id, status, comment, transaction_id) VALUES (?, ?, ?, 'pending', ?, ?)");
            $stmt->execute([
                $_SESSION['booking_details']['room_id'],
                $payment_id,
                $bdid,
                $_SESSION['booking_details']['comment'] ?? '',
                $transaction_id
            ]);
            $booking_id = $pdo->lastInsertId();

            // Create booking invoice
            $stmt = $pdo->prepare("INSERT INTO booking_invoice(user_id, booking_id) VALUES (?, ?)");
            $stmt->execute([$user, $booking_id]);

            header("Location: /booking/invoice");
            exit();
        case 'bank':
            $bank_name = $_POST['bank_name'];
            $account_number = $_POST['account_number'];
            $account_name = $_POST['account_name'];

            // Create payment record
            $stmt = $pdo->prepare("INSERT INTO payment(user_id, amount, payment_method, payment_status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$user, $_SESSION['booking_details']['totalPrice'], $payment_method]);
            $payment_id = $pdo->lastInsertId();

            // Create bank payment details
            $stmt = $pdo->prepare("INSERT INTO bank_payment(payment_id, bank_name, account_number, account_name) VALUES (?, ?, ?, ?)");
            $stmt->execute([$payment_id, $bank_name, $account_number, $account_name]);

            // Create booking details
            $stmt = $pdo->prepare("INSERT INTO booking_details(check_in, check_out, adult, child, overnight, booking_fee) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['booking_details']['check_in'],
                $_SESSION['booking_details']['check_out'],
                $_SESSION['booking_details']['adult'],
                $_SESSION['booking_details']['children'],
                $_SESSION['booking_details']['overnight'],
                $_SESSION['booking_details']['bookingFee']
            ]);
            $bdid = $pdo->lastInsertId();

            // Create booking record
            $stmt = $pdo->prepare("INSERT INTO booking(room_id, payment_id, booking_details_id, status, comment, transaction_id) VALUES (?, ?, ?, 'pending', ?, ?)");
            $stmt->execute([
                $_SESSION['booking_details']['room_id'],
                $payment_id,
                $bdid,
                $_SESSION['booking_details']['comment'] ?? '',
                $transaction_id
            ]);
            $booking_id = $pdo->lastInsertId();

            // Create booking invoice
            $stmt = $pdo->prepare("INSERT INTO booking_invoice(user_id, booking_id) VALUES (?, ?)");
            $stmt->execute([$user, $booking_id]);

            header("Location: /booking/invoice");
            exit();
    }
}
