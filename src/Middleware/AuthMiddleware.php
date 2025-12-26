<?php

namespace App\Middleware;

use App\Services\JwtService;

class AuthMiddleware
{
    private $jwtService;

    public function __construct()
    {
        $this->jwtService = new JwtService();
    }

    public function handle()
    {
        session_start();

        if (!isset($_COOKIE['token'])) {
            header("Location: /auth/login");
            exit();
        }

        try {
            $token = $_COOKIE['token'];
            $decoded = $this->jwtService->decodeToken($token);
            return $decoded;
        } catch (\Exception $e) {
            header("Location: /auth/login");
            exit();
        }
    }

    public function isGuest()
    {
        return !isset($_COOKIE['token']);
    }

    public function isAuthenticated()
    {
        return isset($_COOKIE['token']);
    }
}
