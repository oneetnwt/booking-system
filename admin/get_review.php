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
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $token = $_COOKIE['token'];
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));

    if ($decoded->data->role !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit();
    }
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Review ID is required']);
    exit();
}

$review_id = (int) $_GET['id'];

try {
    $stmt = $pdo->prepare("
        SELECT r.*, u.firstname, u.lastname, rm.room_name
        FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        JOIN room rm ON r.room_id = rm.id 
        WHERE r.id = ?
    ");
    $stmt->execute([$review_id]);
    $review = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$review) {
        http_response_code(404);
        echo json_encode(['error' => 'Review not found']);
        exit();
    }

    echo json_encode($review);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}