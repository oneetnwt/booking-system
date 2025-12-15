<?php

require '../db/connectDB.php';
require_once __DIR__ . "/../../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$secret_key = $_ENV['JWT_SECRET_KEY'];

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $key = $_ENV['JWT_SECRET_KEY'];

        $token = JWT::encode(
            array(
                'iat' => time(),
                'nbf' => time(),
                'data' => array(
                    'user_id' => $user['id'],
                    'firstname' => $user['firstname'],
                    'lastname' => $user['lastname'],
                    'role' => $user['role']
                )
            ),
            $key,
            'HS256'
        );

        setcookie("token", $token, time() + 3600, "/", "", true, true);

        header("Location: ../home/home.php");
        exit();
    } else {
        $_SESSION['error'] = "User not found";
        header("Location: login.php");
        exit();
    }
}
