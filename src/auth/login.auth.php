<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$secret_key = $_ENV['JWT_SECRET_KEY'];

if($_SERVER['REQUEST_METHOD'] === "POST"){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user && password_verify($password, $user['password'])){
        header("Location: ../home.php");
        exit();
    } else {
        $_SESSION['error'] = "User not found";
        header("Location: login.php");
        exit();
    }
}