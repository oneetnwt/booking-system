<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\JwtService;
use Firebase\JWT\JWT;

class GoogleAuthController
{
    private $pdo;
    private $jwtService;
    private $client;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
        $this->jwtService = new JwtService();
        
        $this->client = new \Google_Client();
        $this->client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $this->client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $this->client->setRedirectUri($_ENV['GOOGLE_REDIRECT']);
        $this->client->addScope("email");
        $this->client->addScope("profile");
    }

    public function login()
    {
        $auth_url = $this->client->createAuthUrl();
        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
        exit;
    }

    public function callback()
    {
        session_start();

        if (isset($_GET['code'])) {
            $token = $this->client->fetchAccessTokenWithAuthCode($_GET['code']);
            $this->client->setAccessToken($token);

            $oauth = new \Google_Service_Oauth2($this->client);
            $userinfo = $oauth->userinfo->get();

            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$userinfo->email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                $_SESSION['error'] = "User not found";
                header("Location: /auth/login");
                exit();
            }

            $jwt_token = $this->jwtService->generateToken([
                'user_id' => $user['id'],
                'firstname' => $user['firstname'],
                'lastname' => $user['lastname'],
                'role' => $user['role']
            ]);

            setcookie("token", $jwt_token, time() + 3600, "/", "", true, true);

            header("Location: /home");
            exit();
        } else {
            echo "No code returned from Google.";
        }
    }
}
