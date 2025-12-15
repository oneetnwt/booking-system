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

        // Configure HTTP client for SSL verification
        $httpConfig = ['http' => ['verify' => true]];

        // Check for system certificate settings
        $cafile = ini_get('openssl.cafile');
        $cainfo = ini_get('curl.cainfo');

        if ($cafile && file_exists($cafile)) {
            $httpConfig['http']['verify'] = $cafile;
        } elseif ($cainfo && file_exists($cainfo)) {
            $httpConfig['http']['verify'] = $cainfo;
        } elseif ($caBundlePath = $_ENV['CA_BUNDLE_PATH'] ?? null) {
            // Use CA bundle path from environment if specified
            $httpConfig['http']['verify'] = $caBundlePath;
        }
        // As a fallback for development environments, you might need to disable verification
        // However, note that this is not secure for production use
        elseif ($_ENV['APP_ENV'] === 'development') {
            // For development only - do not use in production!
            $httpConfig['http']['verify'] = false;
        }

        $this->client->setHttpClient(
            new \GuzzleHttp\Client($httpConfig)
        );
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
