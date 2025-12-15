<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\BookingController;
use App\Controllers\ProfileController;
use App\Controllers\AdminController;
use App\Controllers\GoogleAuthController;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$router = new Router();

$router->get('/', HomeController::class, 'index');
$router->get('/home', HomeController::class, 'index');
$router->get('/accommodation', HomeController::class, 'accommodation');
$router->post('/accommodation', HomeController::class, 'accommodation');
$router->get('/reviews', HomeController::class, 'reviews');

$router->get('/auth/login', AuthController::class, 'showLogin');
$router->post('/auth/login', AuthController::class, 'login');
$router->get('/auth/signup', AuthController::class, 'showSignup');
$router->post('/auth/signup', AuthController::class, 'signup');
$router->get('/auth/verify', AuthController::class, 'showVerify');
$router->post('/auth/verify', AuthController::class, 'verify');
$router->get('/auth/forgot-password', AuthController::class, 'showForgotPassword');
$router->post('/auth/forgot-password', AuthController::class, 'forgotPassword');
$router->get('/auth/send-code', AuthController::class, 'showSendCode');
$router->get('/auth/reset-password', AuthController::class, 'showResetPassword');
$router->get('/auth/logout', AuthController::class, 'logout');

$router->get('/google/login', GoogleAuthController::class, 'login');
$router->get('/google/callback', GoogleAuthController::class, 'callback');

$router->get('/booking/confirmation', BookingController::class, 'confirmation');
$router->post('/booking/confirmation', BookingController::class, 'confirmation');
$router->get('/booking/process', BookingController::class, 'process');
$router->post('/booking/process', BookingController::class, 'process');
$router->get('/booking/payment', BookingController::class, 'payment');
$router->post('/booking/payment-verification', BookingController::class, 'paymentVerification');
$router->get('/booking/invoice', BookingController::class, 'invoice');

$router->get('/profile', ProfileController::class, 'profile');
$router->post('/profile', ProfileController::class, 'profile');
$router->get('/profile/my-bookings', ProfileController::class, 'myBookings');
$router->post('/profile/booking-details', ProfileController::class, 'bookingDetails');
$router->get('/profile/rate-us', ProfileController::class, 'rateUs');
$router->post('/profile/rate-us', ProfileController::class, 'rateUs');

$router->get('/admin/dashboard', AdminController::class, 'dashboard');
$router->get('/admin/bookings', AdminController::class, 'bookings');
$router->post('/admin/bookings', AdminController::class, 'bookings');
$router->get('/admin/view-booking', AdminController::class, 'viewBooking');
$router->get('/admin/rooms', AdminController::class, 'rooms');
$router->post('/admin/rooms', AdminController::class, 'rooms');
$router->get('/admin/get-room', AdminController::class, 'getRoom');
$router->get('/admin/users', AdminController::class, 'users');
$router->post('/admin/users', AdminController::class, 'users');
$router->get('/admin/reviews', AdminController::class, 'reviews');
$router->post('/admin/reviews', AdminController::class, 'reviews');
$router->get('/admin/generate-report', AdminController::class, 'generateReport');

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

$router->dispatch($requestMethod, $requestUri);
