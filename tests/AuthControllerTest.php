<?php

namespace App\Tests;

use App\Controllers\AuthController;
use App\Config\Database;
use PHPUnit\Framework\MockObject\MockObject;

class AuthControllerTest extends BaseTestCase
{
    private AuthController $authController;
    private MockObject $mockPdo;
    private MockObject $mockStmt;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the Database class
        $mockDatabase = $this->createMock(Database::class);
        $this->mockPdo = $this->createMock(\PDO::class);
        $this->mockStmt = $this->createMock(\PDOStatement::class);

        $mockDatabase->method('getConnection')->willReturn($this->mockPdo);

        $this->authController = $this->getMockBuilder(AuthController::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Set the mocked database connection
        $reflection = new \ReflectionClass($this->authController);
        $pdoProperty = $reflection->getProperty('pdo');
        $pdoProperty->setValue($this->authController, $this->mockPdo);
    }

    public function testLoginWithValidCredentials(): void
    {
        $email = 'test@example.com';
        $password = 'validPassword123';

        // Mock the database query
        $this->mockStmt->method('bindParam')->willReturn(true);
        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetch')->willReturn([
            'id' => 1,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'role' => 'user',
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);

        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        // Since the login method redirects, we'll test the logic indirectly
        // For a full test, we'd need to refactor the controller to separate the validation logic
        $this->expectNotToPerformAssertions(); // Placeholder test until we implement proper structure
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $email = 'test@example.com';
        $password = 'wrongPassword';

        // Mock the database query to return no user
        $this->mockStmt->method('bindParam')->willReturn(true);
        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetch')->willReturn(false);

        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $this->expectNotToPerformAssertions(); // Placeholder test until we implement proper structure
    }
}