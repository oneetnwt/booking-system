<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\JwtService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ProfileController
{
    private $pdo;
    private $jwtService;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
        $this->jwtService = new JwtService();
    }

    public function profile()
    {
        session_start();

        if (!isset($_COOKIE['token'])) {
            header("Location: /auth/login");
            exit();
        }

        $decoded = $this->jwtService->decodeToken($_COOKIE['token']);
        $user_id = $decoded->data->user_id;

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../Views/profile/profile.php';
    }

    public function myBookings()
    {
        session_start();

        if (!isset($_COOKIE['token'])) {
            header("Location: /auth/login");
            exit();
        }

        $decoded = $this->jwtService->decodeToken($_COOKIE['token']);
        $user_id = $decoded->data->user_id;

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stmt = $this->pdo->prepare("
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
        $bookings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $allBookings = $bookings;
        $completedBookings = [];
        $upcomingBookings = [];

        $currentDate = date('Y-m-d');

        foreach ($bookings as $booking) {
            if (strtotime($booking['check_out']) < strtotime($currentDate)) {
                $completedBookings[] = $booking;
            } else {
                $upcomingBookings[] = $booking;
            }
        }

        require_once __DIR__ . '/../Views/profile/my-bookings.php';
    }

    public function bookingDetails()
    {
        session_start();

        if (!isset($_COOKIE['token'])) {
            header("Location: /home");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $booking_id = $_POST['bookingId'];
        }

        if (!isset($booking_id)) {
            header("Location: /profile/my-bookings");
            exit();
        }

        if (!isset($_POST['bookingId']) || empty($_POST['bookingId'])) {
            header("Location: /profile/my-bookings");
            exit();
        }

        $stmt = $this->pdo->prepare("
            SELECT
                bi.*,
                b.*,
                u.*,
                bd.*,
                r.*,
                p.*
            FROM
                booking_invoice bi
            JOIN
                booking b on bi.booking_id = b.id
            JOIN
                users u on bi.user_id = u.id
            JOIN
                booking_details bd on b.booking_details_id = bd.id
            JOIN
                room r on b.room_id = r.id
            JOIN
                payment p on b.payment_id = p.id
            WHERE
                bi.booking_id = ?
        ");
        $stmt->execute([$booking_id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../Views/profile/booking-details.php';
    }

    public function rateUs()
    {
        session_start();

        if (!isset($_COOKIE['token'])) {
            header("Location: /auth/login");
            exit();
        }

        $decoded = $this->jwtService->decodeToken($_COOKIE['token']);
        $user_id = $decoded->data->user_id;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $booking_id = $_POST['booking_id'];
            $rating = $_POST['rating'];
            $review = $_POST['review'];

            $stmt = $this->pdo->prepare("SELECT * FROM booking WHERE id = ?");
            $stmt->execute([$booking_id]);
            $booking = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$booking) {
                $_SESSION['error'] = "Booking not found";
                header("Location: /profile/my-bookings");
                exit();
            }

            $stmt = $this->pdo->prepare("INSERT INTO reviews(user_id, room_id, rating, review) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $booking['room_id'], $rating, $review]);

            $_SESSION['success'] = "Thank you for your review!";
            header("Location: /profile/my-bookings");
            exit();
        }

        require_once __DIR__ . '/../Views/profile/rate-us.php';
    }
}
