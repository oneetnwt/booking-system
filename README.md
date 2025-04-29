# Booking System

A web-based booking system with user authentication, including Google OAuth integration.

## Features

- User authentication (email/password and Google OAuth)
- Booking management
- User profile management
- Admin dashboard

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer
- XAMPP (or similar local development environment)

## Installation

1. Clone the repository:

   ```
   git clone https://github.com/yourusername/booking-system.git
   cd booking-system
   ```

2. Install dependencies:

   ```
   composer install
   ```

3. Create a `.env` file in the root directory with the following variables:

   ```
   DB_HOST=localhost
   DB_USER=your_database_user
   DB_PASS=your_database_password
   DB_NAME=booking_system

   GOOGLE_CLIENT_ID=your_google_client_id
   GOOGLE_CLIENT_SECRET=your_google_client_secret
   GOOGLE_REDIRECT=http://localhost/booking-system/google-auth/google-callback.php
   ```

4. Import the database schema:

   ```
   mysql -u your_database_user -p your_database_name < resort.sql
   ```

5. Configure Google OAuth:
   - Go to the [Google Cloud Console](https://console.cloud.google.com)
   - Create a new project or select an existing one
   - Enable the Google+ API
   - Create OAuth 2.0 credentials
   - Add the redirect URI: `http://localhost/booking-system/google-auth/google-callback.php`

## Usage

1. Start your local server (XAMPP, etc.)
2. Navigate to `http://localhost/booking-system/`
3. Register a new account or log in with Google

## Project Structure

- `assets/` - Images, CSS, and JavaScript files
- `auth/` - Authentication related files
- `db/` - Database connection and queries
- `google-auth/` - Google OAuth integration
- `home/` - Main application pages
- `styles/` - CSS stylesheets
- `vendor/` - Composer dependencies

## License

[MIT License](LICENSE)

## Contact

For any questions or issues, please contact [your-email@example.com](mailto:your-email@example.com).
