<?php

require '../db/connectDB.php';
require '../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$secret_key = $_ENV['JWT_SECRET_KEY'];

if($_SERVER['REQUEST_METHOD'] === "POST"){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user && password_verify($password, $user['password'])){
         $token = JWT::encode(
            array(
                'iat' => time(),
                'nbf' => time(),
                'exp' => time() + 3600,
                'data' => array(
                    'user_id' => $user['id'],
                    'name' => $user['firstname'] . " " . $user['lastname'],
                    'email' => $user['email']
                ) 
                ),
                $secret_key,
                'HS256'
            );
            setcookie("token", $token, time() + 3600, "/", "", true, true);
            header("Location: ../home/home.php");
            exit();
    }
}