# Setup Guide

This document provides instructions for setting up the K&A Resort Booking System. Choose one of the two options below depending on your preference and environment.

## Option 1: PHP Built-in Server (Development)

### Prerequisites

- PHP 8.2 or higher
- MySQL 10.4 or higher (or MariaDB)
- Composer
- Web browser

### Step 1: Install PHP

Download and install PHP 8.2+ from [php.net](https://windows.php.net/download)

**Windows:**
1. Download the Thread Safe ZIP package
2. Extract to `C:\php`
3. Add `C:\php` to your system PATH
4. Copy `php.ini-development` to `php.ini`
5. Enable required extensions in `php.ini`:
   ```ini
   extension=mysqli
   extension=pdo_mysql
   extension=mbstring
   extension=openssl
   extension=curl
   extension=gd
   ```

**Linux/macOS:**
```bash
# Ubuntu/Debian
sudo apt install php8.2 php8.2-mysql php8.2-mbstring php8.2-curl php8.2-gd

# macOS (using Homebrew)
brew install php@8.2
```

### Step 2: Install MySQL

**Windows:** Download from [mysql.com](https://dev.mysql.com/downloads/installer/)

**Linux:**
```bash
sudo apt install mysql-server
sudo mysql_secure_installation
```

**macOS:**
```bash
brew install mysql
brew services start mysql
```

### Step 3: Install Composer

Download from [getcomposer.org](https://getcomposer.org/download/)

### Step 4: Clone the Repository

```bash
git clone https://github.com/yourusername/booking-system.git
cd booking-system
```

### Step 5: Install Dependencies

```bash
composer install
```

### Step 6: Database Setup

1. Start MySQL service
2. Create database and import schema:

```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE resort;
exit;

# Import the SQL file
mysql -u root -p resort < resort.sql
```

### Step 7: Environment Configuration

**Option 1:** Copy the example environment file and modify the values:

```bash
cp .env.example .env
```

**Option 2:** Create a `.env` file manually in the root directory with the following structure:

```env
# Database Configuration
DB_SERVERNAME=localhost
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
DB_NAME=resort

# Application Email Configuration
APP_EMAIL=your_email@gmail.com
APP_PASSWORD=your_app_password
APP_NAME=K&A Resort

# reCAPTCHA Configuration
RECAPTCHA_SITE_KEY=your_recaptcha_site_key
RECAPTCHA_SECRET_KEY=your_recaptcha_secret_key

# JWT Configuration
JWT_SECRET_KEY=your_jwt_secret_key

# Google OAuth Configuration
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT=http://localhost:8000/google/callback
```

#### Configuration Details

**Database Configuration:**
- `DB_SERVERNAME`: Host address of your MySQL server (usually 'localhost')
- `DB_USERNAME`: Your MySQL username (typically 'root' for local development)
- `DB_PASSWORD`: Your MySQL password
- `DB_NAME`: Name of the database you created (should be 'resort' if following previous steps)

**Application Email Configuration:**
- `APP_EMAIL`: Email address used to send notifications (Gmail recommended)
- `APP_PASSWORD`: App password for the email account (not your regular password)
- `APP_NAME`: Name of your resort application

**Gmail Setup (Required for APP_EMAIL):**
Option A: Set up Gmail App Password (Recommended)
   1. Enable 2-Step Verification: https://myaccount.google.com/security
   2. Generate App Password: https://myaccount.google.com/apppasswords
   3. When generating the app password, Google displays it with spaces (e.g., "abcd efgh ijkl mnop")
      BUT you must remove the spaces when adding it to your .env file
   4. Update .env (remove spaces from app password):
   ```
   APP_EMAIL=your_gmail@gmail.com
   APP_PASSWORD=abcdefghijkmnop
   ```
   Example:
   ```
   APP_EMAIL=floresaybaez574@gmail.com
   APP_PASSWORD=nqna gipa kapn xgzr  # Google shows this with spaces
   # BUT in .env file use (no spaces):
   APP_PASSWORD=nqngipakapnxgzr
   ```

**reCAPTCHA Configuration:**
- `RECAPTCHA_SITE_KEY`: Public key obtained from Google reCAPTCHA dashboard
- `RECAPTCHA_SECRET_KEY`: Private key obtained from Google reCAPTCHA dashboard

  To obtain these keys:
  1. Visit [Google reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin)
  2. Register a new site with type "reCAPTCHA v2" or "Invisible reCAPTCHA"
  3. Add your domain (localhost for local development)
  4. Copy the Site Key and Secret Key to your .env file

  **Note:** If you're using an existing reCAPTCHA key and want to add localhost, follow these steps:

  Adding localhost to existing reCAPTCHA site:

    1. Go to https://www.google.com/recaptcha/admin
    2. Sign in with your Google account
    3. Find your site with key 6LeEcywsAAAAAOjPox8h2M8kixKCXmT3bAvf3y78
    4. Click on the settings/gear icon or site name
    5. Scroll to Domains section
    6. Click Add a new domain
    7. Add localhost and click save
    8. Click Add a new domain again
    9. Add 127.0.0.1 and click save

   OR create new reCAPTCHA for localhost:

    1. Go to https://www.google.com/recaptcha/admin
    2. Click + button (Create)
    3. Label: Booking System - Localhost
    4. reCAPTCHA type: Select reCAPTCHA v2 → "I'm not a robot" Checkbox
    5. Domains:
       - Add localhost
       - Add 127.0.0.1
    6. Accept terms and click Submit
    7. Copy the new Site Key and Secret Key
    8. Update your .env file with the new keys

**JWT Configuration:**
- `JWT_SECRET_KEY`: Random string used to sign JWT tokens securely
  Generate a strong secret key using a password generator or command like:
  ```bash
  openssl rand -hex 32
  ```

**Google OAuth Configuration:**
- `GOOGLE_CLIENT_ID`: Client ID from Google Developer Console
- `GOOGLE_CLIENT_SECRET`: Client Secret from Google Developer Console
- `GOOGLE_REDIRECT`: The URL where Google redirects after authentication

  To set up Google OAuth:
  1. Go to [Google Cloud Console](https://console.cloud.google.com/)
  2. Create a new project or select existing one
  3. Enable Google+ API
  4. Go to Credentials > Create Credentials > OAuth 2.0 Client IDs
  5. Set application type as 'Web application'
  6. Add authorized redirect URIs (include http://localhost:8000/google/callback)
  7. Copy Client ID and Secret to your .env file

  **Architecture Note:** This application uses a modern MVC architecture with a central router at `public/index.php`. The Google authentication flow works as follows:
  - Login button directs users to `/google/login` route (handled by GoogleAuthController::login)
  - After authentication with Google, users are redirected back to `/google/callback` (handled by GoogleAuthController::callback)
  - The callback controller verifies the user and creates a JWT token for session management

### Step 8: Run the Application

```bash
php -S localhost:8000
```

### Step 9: Access the Application

Open your browser and navigate to: `http://localhost:8000`