# Testing Documentation

## Overview
This directory contains the test suite for the K&A Resort Booking System. The tests follow PHPUnit best practices and cover various aspects of the application.

## Test Types

### Unit Tests
- Test individual components in isolation
- Controllers: Test business logic without external dependencies
- Services: Test utility and business logic classes
- Models: Test data access and manipulation

### Integration Tests
- Test the interaction between multiple components
- End-to-end user flows
- API endpoint interactions

## Running Tests

### All Tests
```bash
php vendor/bin/phpunit
```

### Specific Test File
```bash
php vendor/bin/phpunit tests/ExampleTest.php
```

### With Coverage Report
```bash
php vendor/bin/phpunit --coverage-html coverage/
```

## Test Structure
- `BaseTestCase.php`: Base class for all tests with common setup
- `AuthControllerTest.php`: Tests for authentication functionality
- `BookingControllerTest.php`: Tests for booking functionality
- `JwtServiceTest.php`: Tests for JWT token handling
- `RouterTest.php`: Tests for routing functionality
- `DatabaseTest.php`: Tests for database configuration
- `EnvironmentTest.php`: Tests for environment setup
- `IntegrationTest.php`: Tests for full user flows

## Testing Philosophy
- Tests should be fast, isolated, and repeatable
- Each test should verify a specific behavior
- Tests should not depend on external services when possible
- Mock external dependencies like databases and APIs for unit tests
- Use real services for integration tests

## Test Guidelines
1. Name tests descriptively (e.g., `testLoginWithValidCredentials`)
2. Test one behavior per test method
3. Use appropriate assertions to verify expected outcomes
4. Clean up test data after tests when necessary
5. Don't test third-party libraries, only your own code