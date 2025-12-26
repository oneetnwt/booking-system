<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\JwtService;
use App\Services\PdfService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class BookingController
{
    private $pdo;
    private $jwtService;
    private $pdfService;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
        $this->jwtService = new JwtService();
        $this->pdfService = new PdfService();
    }

    public function confirmation()
    {
        session_start();

        if (!isset($_COOKIE['token'])) {
            header("Location: /auth/login?error=unauthorized");
            exit();
        }

        if (isset($_GET['room_id'])) {
            $_SESSION['room_id'] = $_GET['room_id'];
        }

        if (!isset($_SESSION['room_id'])) {
            header("Location: /home?error=invalid_room");
            exit();
        }

        if (!isset($_SESSION['booking_details'])) {
            $stmt = $this->pdo->prepare("SELECT * FROM room WHERE id = ?");
            $stmt->execute([$_SESSION['room_id']]);
            $room = $stmt->fetch(\PDO::FETCH_ASSOC);

            $bookingFee = 0.01 * (($entranceFee ?? 0) + $room['room_price']);
            $totalPrice = ($entranceFee ?? 0) + $room['room_price'] + $bookingFee;

            $_SESSION['booking_details'] = [
                'check_in' => '',
                'check_out' => '',
                'days' => 0,
                'adult' => 1,
                'children' => 0,
                'roomPrice' => $room['room_price'],
                'entranceFee' => '50.00',
                'bookingFee' => $bookingFee,
                'totalPrice' => $totalPrice
            ];
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $check_in = $_POST['check_in'];
                $check_out = $_POST['check_out'];
                $adult = (int) ($_POST['adult'] ?? 0);
                $children = (int) ($_POST['children'] ?? 0);
                $overnight = $_POST['overnight'] ?? 'no';

                $stmt = $this->pdo->prepare("SELECT b.* FROM booking b
                    INNER JOIN booking_details bd ON b.booking_details_id = bd.id
                    WHERE b.room_id = ? AND b.status != 'cancelled' AND
                    ((bd.check_in BETWEEN ? AND ?) OR
                    (bd.check_out BETWEEN ? AND ?) OR
                    (? BETWEEN bd.check_in AND bd.check_out))");
                $stmt->execute([
                    $_SESSION['room_id'],
                    $check_in,
                    $check_out,
                    $check_in,
                    $check_out,
                    $check_in
                ]);

                if ($stmt->rowCount() > 0) {
                    $_SESSION['error'] = "This room is already booked for the selected dates. Please choose different dates.";
                } else {
                    $stmt = $this->pdo->prepare("SELECT * FROM room WHERE id = ?");
                    $stmt->execute([$_SESSION['room_id']]);
                    $room = $stmt->fetch(\PDO::FETCH_ASSOC);

                    $check_in_date = new \DateTime($check_in);
                    $check_out_date = new \DateTime($check_out);

                    $interval = $check_in_date->diff($check_out_date);
                    $days = $interval->days;

                    if ($days > 0) {
                        $overnight = 'yes';
                    } else {
                        $overnight = 'no';
                        $days = 1;
                    }

                    // Use the same calculation logic as in the original view
                    if ($overnight == 'no') {
                        $adultFee = 50;
                        $childrenFee = 30;

                        $entranceFee = ($adult * $adultFee) + ($children * $childrenFee);
                    } else {
                        $total = $adult + $children;
                        $entranceFee = $total * 200;
                    }

                    $bookingFee = 0.01 * ($entranceFee + $room['room_price']);
                    $totalPrice = $entranceFee + ($days * $room['room_price']) + $bookingFee;

                    $_SESSION['booking_details'] = [
                        'room_id' => $_SESSION['room_id'],
                        'check_in' => $check_in_date->format('Y-m-d\TH:i'),
                        'check_out' => $check_out_date->format('Y-m-d\TH:i'),
                        'days' => $days,
                        'adult' => $adult,
                        'children' => $children,
                        'overnight' => $overnight,
                        'roomPrice' => $room['room_price'],
                        'entranceFee' => $entranceFee,
                        'bookingFee' => $bookingFee,
                        'totalPrice' => $totalPrice
                    ];

                    header("Location: /booking/process");
                    exit();
                }
            } catch (\Exception $e) {
                $_SESSION['error'] = "An error occurred. Please try again.";
            }
        }

        $stmt = $this->pdo->prepare("SELECT * FROM room WHERE id = ?");
        $stmt->execute([$_SESSION['room_id']]);
        $room = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Make database connection available to the view
        $pdo = $this->pdo;
        require_once __DIR__ . '/../Views/booking/booking-confirmation.php';
    }

    public function process()
    {
        session_start();

        $decoded = null;
        $user = null;

        if (isset($_COOKIE['token'])) {
            try {
                $token = $_COOKIE['token'];
                $decoded = $this->jwtService->decodeToken($token);

                $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$decoded->data->user_id]);
                $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                header('Location: /auth/login');
                exit();
            }
        } else {
            header('Location: /auth/login');
            exit();
        }

        if (!isset($_SESSION['booking_details'])) {
            header('Location: /booking/confirmation');
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
                'check_in' => $_SESSION['booking_details']['check_in'],
                'check_out' => $_SESSION['booking_details']['check_out'],
                'days' => $_SESSION['booking_details']['days'],
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
                'contact' => $contact,
                'comment' => $comment
            ];

            header("Location: /booking/payment");
            exit();
        }

        require_once __DIR__ . '/../Views/booking/booking-process.php';
    }

    public function payment()
    {
        session_start();
        require_once __DIR__ . '/../Views/booking/booking-payment.php';
    }

    public function paymentVerification()
    {
        session_start();

        if (!isset($_COOKIE['token'])) {
            header("Location: /auth/login");
            exit();
        }

        $decoded = $this->jwtService->decodeToken($_COOKIE['token']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payment_method = $_POST['payment_method'];
            $user = $decoded->data->user_id;
            $transaction_id = uniqid('KA_', true);
            $_SESSION['transact'] = $transaction_id;

            switch ($payment_method) {
                case 'paypal':
                    $paypal_email = $_POST['paypal_email'];

                    $stmt = $this->pdo->prepare("INSERT INTO payment(user_id, amount, payment_method) VALUES (?, ?, ?)");
                    $stmt->execute([$user, $_SESSION['booking_details']['totalPrice'], $payment_method]);

                    $payment_id = $this->pdo->lastInsertId();

                    $stmt = $this->pdo->prepare("INSERT INTO paypal_payment(payment_id, paypal_email) VALUES (?, ?)");
                    $stmt->execute([$payment_id, $paypal_email]);

                    $stmt = $this->pdo->prepare("INSERT INTO booking_details(check_in, check_out, adult, child, overnight, booking_fee) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$_SESSION['booking_details']['check_in'], $_SESSION['booking_details']['check_out'], $_SESSION['booking_details']['adult'], $_SESSION['booking_details']['children'], $_SESSION['booking_details']['overnight'], $_SESSION['booking_details']['bookingFee']]);

                    $bdid = $this->pdo->lastInsertId();

                    $stmt = $this->pdo->prepare("INSERT INTO booking(room_id, payment_id, booking_details_id, status, comment, transaction_id) VALUES (?, ?, ?, 'pending', ?, ?)");
                    $stmt->execute([
                        $_SESSION['booking_details']['room_id'],
                        $payment_id,
                        $bdid,
                        $_SESSION['booking_details']['comment'] ?? '',
                        $transaction_id
                    ]);

                    $booking_id = $this->pdo->lastInsertId();

                    $stmt = $this->pdo->prepare("INSERT INTO booking_invoice(user_id, booking_id) VALUES (?, ?)");
                    $stmt->execute([$user, $booking_id]);

                    header("Location: /booking/invoice");
                    exit();

                case 'gcash':
                    $gcash_number = $_POST['gcash_number'];
                    $gcash_name = $_POST['gcash_name'];

                    $stmt = $this->pdo->prepare("INSERT INTO payment(user_id, amount, payment_method) VALUES (?, ?, ?)");
                    $stmt->execute([$user, $_SESSION['booking_details']['totalPrice'], $payment_method]);

                    $payment_id = $this->pdo->lastInsertId();

                    $stmt = $this->pdo->prepare("INSERT INTO gcash_payment(payment_id, gcash_number, gcash_name) VALUES (?, ?, ?)");
                    $stmt->execute([$payment_id, $gcash_number, $gcash_name]);

                    $stmt = $this->pdo->prepare("INSERT INTO booking_details(check_in, check_out, adult, child, overnight, booking_fee) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$_SESSION['booking_details']['check_in'], $_SESSION['booking_details']['check_out'], $_SESSION['booking_details']['adult'], $_SESSION['booking_details']['children'], $_SESSION['booking_details']['overnight'], $_SESSION['booking_details']['bookingFee']]);

                    $bdid = $this->pdo->lastInsertId();

                    $stmt = $this->pdo->prepare("INSERT INTO booking(room_id, payment_id, booking_details_id, status, comment, transaction_id) VALUES (?, ?, ?, 'pending', ?, ?)");
                    $stmt->execute([
                        $_SESSION['booking_details']['room_id'],
                        $payment_id,
                        $bdid,
                        $_SESSION['booking_details']['comment'] ?? '',
                        $transaction_id
                    ]);

                    $booking_id = $this->pdo->lastInsertId();

                    $stmt = $this->pdo->prepare("INSERT INTO booking_invoice(user_id, booking_id) VALUES (?, ?)");
                    $stmt->execute([$user, $booking_id]);

                    header("Location: /booking/invoice");
                    exit();

                case 'bank':
                    $bank_name = $_POST['bank_name'];
                    $account_number = $_POST['account_number'];
                    $account_name = $_POST['account_name'];

                    $stmt = $this->pdo->prepare("INSERT INTO payment(user_id, amount, payment_method) VALUES (?, ?, ?)");
                    $stmt->execute([$user, $_SESSION['booking_details']['totalPrice'], $payment_method]);

                    $payment_id = $this->pdo->lastInsertId();

                    $stmt = $this->pdo->prepare("INSERT INTO bank_payment(payment_id, bank_name, account_number, account_name) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$payment_id, $bank_name, $account_number, $account_name]);

                    $stmt = $this->pdo->prepare("INSERT INTO booking_details(check_in, check_out, adult, child, overnight, booking_fee) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$_SESSION['booking_details']['check_in'], $_SESSION['booking_details']['check_out'], $_SESSION['booking_details']['adult'], $_SESSION['booking_details']['children'], $_SESSION['booking_details']['overnight'], $_SESSION['booking_details']['bookingFee']]);

                    $bdid = $this->pdo->lastInsertId();

                    $stmt = $this->pdo->prepare("INSERT INTO booking(room_id, payment_id, booking_details_id, status, comment, transaction_id) VALUES (?, ?, ?, 'pending', ?, ?)");
                    $stmt->execute([
                        $_SESSION['booking_details']['room_id'],
                        $payment_id,
                        $bdid,
                        $_SESSION['booking_details']['comment'] ?? '',
                        $transaction_id
                    ]);

                    $booking_id = $this->pdo->lastInsertId();

                    $stmt = $this->pdo->prepare("INSERT INTO booking_invoice(user_id, booking_id) VALUES (?, ?)");
                    $stmt->execute([$user, $booking_id]);

                    header("Location: /booking/invoice");
                    exit();
            }
        }

        require_once __DIR__ . '/../Views/booking/payment-verification.php';
    }

    public function invoice()
    {
        session_start();

        if (!isset($_COOKIE['token'])) {
            header("Location: /auth/login");
            exit();
        }

        $decoded = $this->jwtService->decodeToken($_COOKIE['token']);

        $stmt = $this->pdo->prepare("
            SELECT b.*, r.room_name, r.room_price, bd.*, p.amount, p.payment_method, p.created_at as payment_date, u.firstname, u.lastname, u.email, u.phone_number
            FROM booking b
            JOIN room r ON b.room_id = r.id
            JOIN booking_details bd ON b.booking_details_id = bd.id
            JOIN payment p ON b.payment_id = p.id
            JOIN booking_invoice bi ON b.id = bi.booking_id
            JOIN users u ON bi.user_id = u.id
            WHERE b.transaction_id = ?
        ");
        $stmt->execute([$_SESSION['transact']]);
        $booking = $stmt->fetch(\PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../Views/booking/invoice.php';
    }
}
