# Installation Guide

This document provides detailed installation instructions for the K&A Resort Booking System using Docker.

## Option 2: Docker (Production)

### Prerequisites

- Docker Desktop (Windows/macOS) or Docker Engine (Linux)
- Docker Compose

### Step 1: Install Docker

**Windows/macOS:** Download [Docker Desktop](https://www.docker.com/products/docker-desktop)

**Linux:**
```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo apt install docker-compose
```

### Step 2: Clone the Repository

```bash
git clone https://github.com/yourusername/booking-system.git
cd booking-system
```

### Step 3: Create Docker Configuration

Create a `docker-compose.yml` file in the root directory:

```yaml
version: '3.8'

services:
  web:
    image: php:8.2-apache
    container_name: booking-system-web
    ports:
      - "8000:80"
    volumes:
      - .:/var/www/html
    depends_on:
      - db
    environment:
      - DB_SERVERNAME=db
      - DB_USERNAME=root
      - DB_PASSWORD=rootpassword
      - DB_NAME=resort
    command: >
      bash -c "docker-php-ext-install mysqli pdo pdo_mysql &&
               a2enmod rewrite &&
               apache2-foreground"

  db:
    image: mysql:8.0
    container_name: booking-system-db
    ports:
      - "3306:3306"
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: resort
    volumes:
      - db_data:/var/lib/mysql
      - ./resort.sql:/docker-entrypoint/initdb.d/resort.sql

volumes:
  db_data:
```

### Step 4: Create Dockerfile (Optional - for custom PHP setup)

Create a `Dockerfile` in the root directory:

```dockerfile
FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql mbstring

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    curl

RUN docker-php-ext-install zip gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN a2enmod rewrite

RUN chown -R www-data:www-data /var/www/html
```

### Step 5: Environment Configuration

**Option 1:** Copy the example environment file and modify the values:

```bash
cp .env.example .env
```

**Option 2:** Create a `.env` file manually with the Docker-specific database configuration:

```env
# Database Configuration (for Docker)
DB_SERVERNAME=db
DB_USERNAME=root
DB_PASSWORD=rootpassword
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

#### Docker Configuration Details

**Database Configuration:**
- `DB_SERVERNAME`: Host address of your MySQL server (use 'db' when using Docker, 'localhost' otherwise)
- `DB_USERNAME`: Your MySQL username (typically 'root' for local development)
- `DB_PASSWORD`: Your MySQL password (should match the one set in docker-compose.yml)
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

### Step 6: Build and Run with Docker

```bash
# Build and start containers
docker-compose up -d

# Install PHP dependencies inside container
docker-compose exec web composer install

# View logs
docker-compose logs -f

# Stop containers
docker-compose down
```

### Step 7: Access the Application

Open your browser and navigate to: `http://localhost:8000`

### Docker Management Commands

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# Restart containers
docker-compose restart

# View running containers
docker ps

# Access web container shell
docker-compose exec web bash

# Access MySQL container
docker-compose exec db mysql -u root -p
```