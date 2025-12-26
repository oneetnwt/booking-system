# Usage Guide

This document provides information on how to use the K&A Resort Booking System.

## Default Accounts

### Admin Account
- Email: admin@karesort.com
- Password: admin123

### User Account
- Email: user@karesort.com
- Password: user123

## User Registration and Login

1. Navigate to the application homepage
2. Click on the "Sign Up" button to create a new account
3. Fill in the required information (first name, last name, email, phone, password)
4. After registration, you can log in using your credentials
5. The system also supports Google OAuth for authentication

## Booking Process

1. Browse available rooms on the accommodation page
2. Select a room and click "Book Now"
3. Choose your check-in and check-out dates
4. The system will automatically check for availability and conflicts
5. Enter guest information and proceed to payment
6. Multiple payment options are available (PayPal, e-wallet, bank transfer)
7. After successful payment, an invoice will be generated automatically

## Admin Panel Features

The admin panel provides centralized management capabilities:

- **Dashboard**: Overview of bookings, revenue, and key metrics
- **Room Management**: Add, edit, or remove rooms with their details
- **Booking Management**: View, update, or cancel bookings
- **User Management**: Manage registered users
- **Reviews Management**: Monitor and respond to user reviews

## Email Notifications

The system automatically sends email notifications for:
- Booking confirmations
- Payment receipts
- Booking updates or cancellations

## Troubleshooting

- If you encounter permission issues, ensure the web server has write access to the project directory
- For email functionality, make sure to use an app password if using Gmail
- If the database connection fails, verify your MySQL credentials in the `.env` file