<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\JwtService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class HomeController
{
    private $pdo;
    private $jwtService;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
        $this->jwtService = new JwtService();
    }

    public function index()
    {
        session_start();

        if (isset($_SESSION['booking_details'])) {
            unset($_SESSION['booking_details']);
            unset($_SESSION['room_id']);
        }

        $decoded = null;
        if (isset($_COOKIE['token'])) {
            $token = $_COOKIE['token'];
            $decoded = $this->jwtService->decodeToken($token);
        }

        $stmt = $this->pdo->prepare("SELECT * FROM room ORDER BY room_price ASC LIMIT 3");
        $stmt->execute();
        $accommodations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $this->pdo->prepare("
            SELECT r.*, u.firstname, u.lastname, rm.room_name 
            FROM reviews r 
            JOIN users u ON r.user_id = u.id 
            JOIN room rm ON r.room_id = rm.id 
            ORDER BY r.created_at DESC 
            LIMIT 2
        ");
        $stmt->execute();
        $reviews = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../Views/home/home.php';
    }

    public function accommodation()
    {
        session_start();

        if (isset($_SESSION['booking_details'])) {
            unset($_SESSION['booking_details']);
            unset($_SESSION['room_id']);
        }

        $decoded = null;
        if (isset($_COOKIE['token'])) {
            $token = $_COOKIE['token'];
            $decoded = $this->jwtService->decodeToken($token);
        }

        $stmt = $this->pdo->prepare("SELECT * FROM room");
        $stmt->execute();
        $accommodations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $room = (int) $_POST['room'];
            $_SESSION['room_id'] = $room;
            header("Location: /booking/confirmation");
            exit();
        }

        require_once __DIR__ . '/../Views/home/accommodation.php';
    }

    public function reviews()
    {
        session_start();

        $decoded = null;
        if (isset($_COOKIE['token'])) {
            $token = $_COOKIE['token'];
            $decoded = $this->jwtService->decodeToken($token);
        }

        $stmt = $this->pdo->prepare("
            SELECT r.*, u.firstname, u.lastname, rm.room_name 
            FROM reviews r 
            JOIN users u ON r.user_id = u.id 
            JOIN room rm ON r.room_id = rm.id 
            ORDER BY r.created_at DESC
        ");
        $stmt->execute();
        $reviews = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../Views/home/reviews.php';
    }
}
