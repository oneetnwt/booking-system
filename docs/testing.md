# Testing Guide

The application includes a comprehensive testing suite using PHPUnit to ensure reliability and maintainability.

## Running Tests

### Prerequisites
1. Install the development dependencies:
   ```bash
   composer install
   ```

### Running Tests
1. Run all tests:
   ```bash
   php vendor/bin/phpunit
   ```

2. Run tests with coverage report:
   ```bash
   php vendor/bin/phpunit --coverage-html coverage/
   ```

3. Run specific test file:
   ```bash
   php vendor/bin/phpunit tests/JwtServiceTest.php
   ```

4. Run tests in verbose mode:
   ```bash
   php vendor/bin/phpunit --verbose
   ```

5. Run tests with testdox format (human-readable output):
   ```bash
   php vendor/bin/phpunit --testdox
   ```

## Test Categories

### Unit Tests
- Test individual components in isolation
- Controllers: Authentication, Booking, Home, etc.
- Services: JWT handling, Email service, PDF generation
- Core components: Router, Database configuration

### Integration Tests
- End-to-end user flows (registration, booking, payment)
- API endpoint interactions
- Database operations with real connections
- Third-party service integrations (Google OAuth, Email)

### Test Organization
The test suite follows a structured approach:
- `tests/BaseTestCase.php`: Base class with common test setup
- `tests/AuthControllerTest.php`: Authentication workflow tests
- `tests/BookingControllerTest.php`: Booking process tests
- `tests/JwtServiceTest.php`: Token generation and validation tests
- `tests/RouterTest.php`: Route registration and dispatch tests
- `tests/DatabaseTest.php`: Database connection and configuration tests
- `tests/EnvironmentTest.php`: Environment setup verification tests
- `tests/IntegrationTest.php`: Full workflow integration tests

## Test Coverage
The tests aim to cover:
- Core business logic and validation
- Error handling and edge cases
- Security measures and authentication
- Third-party service integrations
- User interaction flows

## Configuration
Tests use a separate configuration from the application to avoid affecting production data:
- Uses separate environment variables for testing
- Mocks external dependencies where appropriate
- Maintains test database isolation
- Follows PHPUnit best practices