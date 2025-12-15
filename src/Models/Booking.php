<?php

namespace App\Models;

use App\Config\Database;

class Booking
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM booking WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findByTransactionId($transaction_id)
    {
        $stmt = $this->pdo->prepare("
            SELECT b.*, r.room_name, r.room_price, bd.*, p.amount, p.payment_method, p.created_at as payment_date, u.firstname, u.lastname, u.email, u.phone_number
            FROM booking b
            JOIN room r ON b.room_id = r.id
            JOIN booking_details bd ON b.booking_details_id = bd.id
            JOIN payment p ON b.payment_id = p.id
            JOIN booking_invoice bi ON b.id = bi.booking_id
            JOIN users u ON bi.user_id = u.id
            WHERE b.transaction_id = ?
        ");
        $stmt->execute([$transaction_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getByUserId($user_id)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                bi.*, 
                b.*,
                bd.*,
                u.*,
                r.*,
                p.*
            FROM 
                booking_invoice bi
            JOIN 
                booking b ON bi.booking_id = b.id
            JOIN
                booking_details bd ON b.booking_details_id = bd.id
            JOIN 
                users u ON bi.user_id = u.id
            JOIN
                room r on b.room_id = r.id
            JOIN
                payment p on b.payment_id = p.id
            WHERE 
                bi.user_id = ?
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAll()
    {
        return $this->pdo->query("SELECT * FROM booking ORDER BY created_at DESC")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getRecent($limit = 5)
    {
        $stmt = $this->pdo->prepare("
            SELECT b.id, u.firstname, u.lastname, r.room_name, b.status, p.amount, b.created_at
            FROM booking b
            JOIN booking_invoice bi ON b.id = bi.booking_id
            JOIN users u ON bi.user_id = u.id
            JOIN room r ON b.room_id = r.id
            LEFT JOIN payment p ON b.payment_id = p.id
            ORDER BY b.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO booking (room_id, payment_id, booking_details_id, status, comment, transaction_id) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['room_id'],
            $data['payment_id'],
            $data['booking_details_id'],
            $data['status'] ?? 'pending',
            $data['comment'] ?? '',
            $data['transaction_id']
        ]);
        return $this->pdo->lastInsertId();
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->pdo->prepare("UPDATE booking SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function count()
    {
        return $this->pdo->query("SELECT COUNT(*) FROM booking")->fetchColumn();
    }

    public function createBookingDetails($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO booking_details (check_in, check_out, adult, child, overnight, booking_fee) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['check_in'],
            $data['check_out'],
            $data['adult'],
            $data['child'],
            $data['overnight'],
            $data['booking_fee']
        ]);
        return $this->pdo->lastInsertId();
    }

    public function createBookingInvoice($user_id, $booking_id)
    {
        $stmt = $this->pdo->prepare("INSERT INTO booking_invoice (user_id, booking_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $booking_id]);
        return $this->pdo->lastInsertId();
    }
}
