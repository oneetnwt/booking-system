<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\JwtService;
use App\Services\PdfService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AdminController
{
    private $pdo;
    private $jwtService;
    private $pdfService;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
        $this->jwtService = new JwtService();
        $this->pdfService = new PdfService();
    }

    private function checkAdminAuth()
    {
        session_start();

        if (!isset($_COOKIE['token'])) {
            header("Location: /auth/login");
            exit();
        }

        try {
            $token = $_COOKIE['token'];
            $decoded = $this->jwtService->decodeToken($token);

            if ($decoded->data->role !== 'admin') {
                header("Location: /home");
                exit();
            }

            return $decoded;
        } catch (\Exception $e) {
            header("Location: /auth/login");
            exit();
        }
    }

    public function dashboard()
    {
        $decoded = $this->checkAdminAuth();

        $total_users = $this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
        $total_bookings = $this->pdo->query("SELECT COUNT(*) FROM booking")->fetchColumn();
        $total_rooms = $this->pdo->query("SELECT COUNT(*) FROM room")->fetchColumn();
        $total_revenue = $this->pdo->query("SELECT SUM(amount) FROM payment")->fetchColumn() ?? 0;

        $recent_bookings = $this->pdo->query("
            SELECT b.id, u.firstname, u.lastname, r.room_name, b.status, p.amount, b.created_at
            FROM booking b
            JOIN booking_invoice bi ON b.id = bi.booking_id
            JOIN users u ON bi.user_id = u.id
            JOIN room r ON b.room_id = r.id
            LEFT JOIN payment p ON b.payment_id = p.id
            ORDER BY b.created_at DESC
            LIMIT 5
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $recent_reviews = $this->pdo->query("
            SELECT r.id, u.firstname, u.lastname, rm.room_name, r.*
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            JOIN room rm ON r.room_id = rm.id
            ORDER BY r.created_at ASC
            LIMIT 5
        ")->fetchAll(\PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../Views/admin/dashboard.php';
    }

    public function bookings()
    {
        $decoded = $this->checkAdminAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'update_status':
                        if (isset($_POST['booking_id']) && isset($_POST['status'])) {
                            $stmt = $this->pdo->prepare("UPDATE booking SET status = ? WHERE id = ?");
                            $stmt->execute([$_POST['status'], $_POST['booking_id']]);
                        }
                        break;
                }
            }
        }

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $per_page = 10;
        $offset = ($page - 1) * $per_page;

        $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        $where_conditions = [];
        $params = [];

        if ($status_filter) {
            $where_conditions[] = "b.status = ?";
            $params[] = $status_filter;
        }

        if ($search) {
            $where_conditions[] = "(u.firstname LIKE ? OR u.lastname LIKE ? OR r.room_name LIKE ? OR b.id LIKE ?)";
            $search_param = "%$search%";
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
        }

        $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

        $count_query = "
            SELECT COUNT(*) 
            FROM booking b 
            JOIN booking_invoice bi ON b.id = bi.booking_id
            JOIN users u ON bi.user_id = u.id 
            JOIN room r ON b.room_id = r.id 
            $where_clause
        ";
        $stmt = $this->pdo->prepare($count_query);
        $stmt->execute($params);
        $total_bookings = $stmt->fetchColumn();
        $total_pages = ceil($total_bookings / $per_page);

        $query = "
            SELECT b.*, u.firstname, u.lastname, r.room_name, p.amount as total_amount, bd.check_in, bd.check_out
            FROM booking b 
            JOIN booking_invoice bi ON b.id = bi.booking_id
            JOIN users u ON bi.user_id = u.id 
            JOIN room r ON b.room_id = r.id 
            LEFT JOIN payment p ON b.payment_id = p.id
            LEFT JOIN booking_details bd ON b.booking_details_id = bd.id
            $where_clause
            ORDER BY bd.check_in ASC 
            LIMIT $offset, $per_page
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $bookings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../Views/admin/bookings.php';
    }

    public function viewBooking()
    {
        $decoded = $this->checkAdminAuth();

        if (!isset($_GET['id'])) {
            header("Location: /admin/bookings");
            exit();
        }

        $booking_id = $_GET['id'];

        $stmt = $this->pdo->prepare("
            SELECT b.*, u.firstname, u.lastname, u.email, u.phone_number,
                   r.room_name, r.room_price, r.room_description, r.image_path,
                   bd.check_in, bd.check_out, bd.adult, bd.child, bd.overnight, bd.booking_fee,
                   p.amount, p.payment_method, p.created_at as payment_date
            FROM booking b
            JOIN booking_invoice bi ON b.id = bi.booking_id
            JOIN users u ON bi.user_id = u.id
            JOIN room r ON b.room_id = r.id
            JOIN booking_details bd ON b.booking_details_id = bd.id
            LEFT JOIN payment p ON b.payment_id = p.id
            WHERE b.id = ?
        ");
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$booking) {
            header("Location: /admin/bookings");
            exit();
        }

        require_once __DIR__ . '/../Views/admin/view-booking.php';
    }

    public function rooms()
    {
        $decoded = $this->checkAdminAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'add':
                        if (isset($_POST['room_name'], $_POST['room_price'], $_POST['room_description'])) {
                            $image_path = '';
                            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                                $upload_dir = __DIR__ . '/../../assets/rooms/';

                                if (!file_exists($upload_dir)) {
                                    mkdir($upload_dir, 0777, true);
                                }

                                $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                                $allowed_extensions = ['jpg', 'jpeg', 'png'];

                                if (!in_array($file_extension, $allowed_extensions)) {
                                    header("Location: /admin/rooms?error=invalid_format");
                                    exit();
                                }

                                if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                                    header("Location: /admin/rooms?error=file_too_large");
                                    exit();
                                }

                                $filename = uniqid() . '.' . $file_extension;
                                $target_path = $upload_dir . $filename;

                                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                                    $image_path = 'rooms/' . $filename;
                                } else {
                                    error_log('Failed to move uploaded file. Error: ' . error_get_last()['message']);
                                    header("Location: /admin/rooms?error=upload_failed");
                                    exit();
                                }
                            } else {
                                $upload_error = isset($_FILES['image']) ? $_FILES['image']['error'] : 'No file uploaded';
                                error_log('File upload error: ' . $upload_error);
                                header("Location: /admin/rooms?error=upload_failed");
                                exit();
                            }

                            try {
                                $stmt = $this->pdo->prepare("
                                    INSERT INTO room (room_name, room_price, room_description, image_path) 
                                    VALUES (?, ?, ?, ?)
                                ");
                                $stmt->execute([
                                    $_POST['room_name'],
                                    $_POST['room_price'],
                                    $_POST['room_description'],
                                    $image_path
                                ]);
                                header("Location: /admin/rooms?success=added");
                                exit();
                            } catch (\PDOException $e) {
                                error_log('Database error: ' . $e->getMessage());
                                header("Location: /admin/rooms?error=database_error");
                                exit();
                            }
                        }
                        break;

                    case 'edit':
                        if (isset($_POST['room_id'], $_POST['room_name'], $_POST['room_price'], $_POST['room_description'])) {
                            $image_path = $_POST['current_image'];

                            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                                $upload_dir = __DIR__ . '/../../assets/rooms/';

                                if (!file_exists($upload_dir)) {
                                    mkdir($upload_dir, 0777, true);
                                }

                                $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                                $allowed_extensions = ['jpg', 'jpeg', 'png'];

                                if (in_array($file_extension, $allowed_extensions)) {
                                    if ($_FILES['image']['size'] <= 5 * 1024 * 1024) {
                                        $filename = uniqid() . '.' . $file_extension;
                                        $target_path = $upload_dir . $filename;

                                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                                            if ($image_path && file_exists(__DIR__ . '/../../assets/' . $image_path)) {
                                                unlink(__DIR__ . '/../../assets/' . $image_path);
                                            }
                                            $image_path = 'rooms/' . $filename;
                                        }
                                    }
                                }
                            }

                            $stmt = $this->pdo->prepare("
                                UPDATE room 
                                SET room_name = ?, room_price = ?, room_description = ?, image_path = ?
                                WHERE id = ?
                            ");
                            $stmt->execute([
                                $_POST['room_name'],
                                $_POST['room_price'],
                                $_POST['room_description'],
                                $image_path,
                                $_POST['room_id']
                            ]);
                            header("Location: /admin/rooms?success=updated");
                            exit();
                        }
                        break;

                    case 'delete':
                        if (isset($_POST['room_id'])) {
                            $stmt = $this->pdo->prepare("SELECT image_path FROM room WHERE id = ?");
                            $stmt->execute([$_POST['room_id']]);
                            $room = $stmt->fetch(\PDO::FETCH_ASSOC);

                            if ($room && $room['image_path']) {
                                $image_full_path = __DIR__ . '/../../assets/' . $room['image_path'];
                                if (file_exists($image_full_path)) {
                                    unlink($image_full_path);
                                }
                            }

                            $stmt = $this->pdo->prepare("DELETE FROM room WHERE id = ?");
                            $stmt->execute([$_POST['room_id']]);
                            header("Location: /admin/rooms?success=deleted");
                            exit();
                        }
                        break;
                }
            }
        }

        $stmt = $this->pdo->query("SELECT * FROM room ORDER BY created_at DESC");
        $rooms = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../Views/admin/rooms.php';
    }

    public function getRoom()
    {
        $decoded = $this->checkAdminAuth();

        if (isset($_GET['id'])) {
            $stmt = $this->pdo->prepare("SELECT * FROM room WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $room = $stmt->fetch(\PDO::FETCH_ASSOC);

            header('Content-Type: application/json');
            echo json_encode($room);
            exit();
        }
    }

    public function users()
    {
        $decoded = $this->checkAdminAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'update_role':
                        if (isset($_POST['user_id']) && isset($_POST['role'])) {
                            $stmt = $this->pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                            $stmt->execute([$_POST['role'], $_POST['user_id']]);
                        }
                        break;

                    case 'delete':
                        if (isset($_POST['user_id'])) {
                            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
                            $stmt->execute([$_POST['user_id']]);
                        }
                        break;
                }
            }
        }

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $per_page = 10;
        $offset = ($page - 1) * $per_page;

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        $where_clause = "";
        $params = [];

        if ($search) {
            $where_clause = "WHERE firstname LIKE ? OR lastname LIKE ? OR email LIKE ?";
            $search_param = "%$search%";
            $params = [$search_param, $search_param, $search_param];
        }

        $count_query = "SELECT COUNT(*) FROM users $where_clause";
        $stmt = $this->pdo->prepare($count_query);
        $stmt->execute($params);
        $total_users = $stmt->fetchColumn();
        $total_pages = ceil($total_users / $per_page);

        $query = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT $offset, $per_page";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../Views/admin/users.php';
    }

    public function reviews()
    {
        $decoded = $this->checkAdminAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action']) && $_POST['action'] === 'delete') {
                if (isset($_POST['review_id'])) {
                    $stmt = $this->pdo->prepare("DELETE FROM reviews WHERE id = ?");
                    $stmt->execute([$_POST['review_id']]);
                }
            }
        }

        $stmt = $this->pdo->query("
            SELECT r.*, u.firstname, u.lastname, rm.room_name
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            JOIN room rm ON r.room_id = rm.id
            ORDER BY r.created_at DESC
        ");
        $reviews = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../Views/admin/reviews.php';
    }

    public function generateReport()
    {
        $decoded = $this->checkAdminAuth();

        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

        $this->pdfService->generateSalesReport($start_date, $end_date);
    }
}
