<?php

namespace App\Tests;

use App\Services\JwtService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

class JwtServiceTest extends BaseTestCase
{
    private JwtService $jwtService;
    private string $testSecretKey;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up a test secret key
        $this->testSecretKey = 'test_secret_key_for_testing_purposes_only';

        // Temporarily set environment variable for testing
        if (!isset($_ENV['JWT_SECRET_KEY'])) {
            $_ENV['JWT_SECRET_KEY'] = $this->testSecretKey;
        }

        $this->jwtService = new JwtService();
    }

    public function testGenerateTokenReturnsValidToken(): void
    {
        $payload = [
            'user_id' => 1,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'role' => 'user'
        ];

        $token = $this->jwtService->generateToken($payload);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);

        // Verify the token can be decoded
        $decoded = JWT::decode($token, new Key($this->testSecretKey, 'HS256'));

        $this->assertEquals($payload['user_id'], $decoded->data->user_id);
        $this->assertEquals($payload['firstname'], $decoded->data->firstname);
        $this->assertEquals($payload['lastname'], $decoded->data->lastname);
        $this->assertEquals($payload['role'], $decoded->data->role);
    }

    public function testDecodeTokenReturnsValidPayload(): void
    {
        $payload = [
            'user_id' => 2,
            'firstname' => 'Jane',
            'lastname' => 'Smith',
            'role' => 'admin'
        ];

        $token = $this->jwtService->generateToken($payload);
        $decoded = $this->jwtService->decodeToken($token);

        $this->assertEquals($payload['user_id'], $decoded->data->user_id);
        $this->assertEquals($payload['firstname'], $decoded->data->firstname);
        $this->assertEquals($payload['lastname'], $decoded->data->lastname);
        $this->assertEquals($payload['role'], $decoded->data->role);
    }

    public function testDecodeInvalidTokenThrowsException(): void
    {
        $this->expectException(\Exception::class);
        $this->jwtService->decodeToken('invalid_token');
    }
}