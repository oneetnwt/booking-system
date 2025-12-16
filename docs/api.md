# API Documentation

This document outlines the available API endpoints for the K&A Resort Booking System.

## Authentication Endpoints

| Method | Endpoint                    | Description            |
|--------|-----------------------------|------------------------|
| POST   | `/auth/login.auth.php`      | User login             |
| POST   | `/auth/signup.auth.php`     | User registration      |
| POST   | `/auth/forgot-password.php` | Request password reset |
| POST   | `/auth/reset-password.php`  | Reset password         |
| POST   | `/auth/send-code.php`       | Send verification code |
| POST   | `/auth/verify.php`          | Verify user account    |
| GET    | `/auth/logout.php`          | User logout            |

## Admin Endpoints

| Method | Endpoint                  | Description                   |
|--------|---------------------------|-------------------------------|
| GET    | `/admin/get_room.php`     | Get room details              |
| GET    | `/admin/bookings.php`     | Get all bookings              |
| GET    | `/admin/users.php`        | Get all users                 |
| GET    | `/admin/rooms.php`        | Get all rooms                 |
| GET    | `/admin/reviews.php`      | Get all reviews               |
| GET    | `/admin/view-booking.php` | View specific booking details |
| GET    | `/admin/dashboard.php`    | Get dashboard statistics      |

## Database Schema

The system uses MySQL/MariaDB with the following key tables:

### Users and Authentication

- `users` - Stores user accounts (firstname, lastname, email, phone, password)
- `role` - Manages user roles (user/admin)

### Room Management

- `room` - Contains room information (name, description, price, images)
- Supports different room types (Upper Kubo, A-House, Cottage)

### Booking System

- `booking` - Main booking records with status tracking
- `booking_details` - Specific booking information (check-in/out, guests)
- `booking_invoice` - Links bookings to user invoices

### Payment System
- `payment` - Main payment records with amount and method
- `paypal_payment` - PayPal-specific payment details
- `gcash_payment` - GCash payment information
- `bank_payment` - Bank transfer details