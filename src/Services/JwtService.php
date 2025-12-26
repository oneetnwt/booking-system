<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private $secret_key;

    public function __construct()
    {
        $this->secret_key = $_ENV['JWT_SECRET_KEY'];
    }

    public function generateToken($data)
    {
        return JWT::encode(
            [
                'iat' => time(),
                'nbf' => time(),
                'data' => $data
            ],
            $this->secret_key,
            'HS256'
        );
    }

    public function decodeToken($token)
    {
        return JWT::decode($token, new Key($this->secret_key, 'HS256'));
    }

    public function verifyToken($token)
    {
        try {
            $this->decodeToken($token);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
