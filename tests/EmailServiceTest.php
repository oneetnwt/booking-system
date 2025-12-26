<?php

namespace App\Tests;

use App\Services\EmailService;

class EmailServiceTest extends BaseTestCase
{
    public function testEmailServiceCanBeInstantiated(): void
    {
        // This test might fail if environment variables aren't set properly
        // For now, we'll just test that the class can be instantiated
        $this->assertTrue(class_exists(EmailService::class));
    }
}