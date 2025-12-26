<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{
    public function testDependenciesAreAvailable(): void
    {
        $this->assertTrue(class_exists('Dotenv\\Dotenv'), 'Dotenv should be available');
        $this->assertTrue(class_exists('Firebase\\JWT\\JWT'), 'Firebase JWT should be available');
        $this->assertTrue(class_exists('PHPMailer\\PHPMailer\\PHPMailer'), 'PHPMailer should be available');
    }

    public function testAutoloaderWorking(): void
    {
        $this->assertTrue(class_exists('App\\Controllers\\AuthController'), 'AuthController should be autoloadable');
        $this->assertTrue(class_exists('App\\Config\\Database'), 'Database class should be autoloadable');
        $this->assertTrue(class_exists('App\\Core\\Router'), 'Router class should be autoloadable');
    }

    public function testEnvironmentLoadingFunctionality(): void
    {
        // Test that we can load environment variables
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $this->assertInstanceOf(\Dotenv\Dotenv::class, $dotenv);
    }
}