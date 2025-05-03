<?php

session_start();

use Firebase\JWT\JWT;

require_once '../vendor/autoload.php';
require '../db/connectDB.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

$client = new Google_Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT']);

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    $oauth = new Google_Service_Oauth2($client);
    $userinfo = $oauth->userinfo->get();

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$userinfo->email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = "User not found";
        header("Location: ../auth/login.php");
        exit();
    }

    $key = $_ENV['JWT_SECRET_KEY'];

    $token = JWT::encode(
        array(
            'iat' => time(),
            'nbf' => time(),
            'data' => array(
                'user_id' => $user['id'],
                'firstname' => $user['firstname'],
                'lastname' => $user['lastname']
            )
        ),
        $key,
        'HS256'
    );

    setcookie("token", $token, time() + 3600, "/", "", true, true);

    header("Location: ../home/home.php");
    exit();
} else {
    echo "No code returned from Google.";
}
