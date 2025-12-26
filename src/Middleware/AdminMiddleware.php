<?php

namespace App\Middleware;

use App\Services\JwtService;

class AdminMiddleware
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

            if ($decoded->data->role !== 'admin') {
                header("Location: /home");
                exit();
            }

            return $decoded;
        } catch (\Exception $e) {
            header("Location: /auth/login");
            exit();
        }
    }
}
