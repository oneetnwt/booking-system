<?php

namespace App\Models;

use App\Config\Database;

class Review
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function create($user_id, $room_id, $rating, $review)
    {
        $stmt = $this->pdo->prepare("INSERT INTO reviews (user_id, room_id, rating, review) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $room_id, $rating, $review]);
        return $this->pdo->lastInsertId();
    }

    public function getAll()
    {
        $stmt = $this->pdo->query("
            SELECT r.*, u.firstname, u.lastname, rm.room_name 
            FROM reviews r 
            JOIN users u ON r.user_id = u.id 
            JOIN room rm ON r.room_id = rm.id 
            ORDER BY r.created_at DESC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getRecent($limit = 5)
    {
        $stmt = $this->pdo->prepare("
            SELECT r.*, u.firstname, u.lastname, rm.room_name 
            FROM reviews r 
            JOIN users u ON r.user_id = u.id 
            JOIN room rm ON r.room_id = rm.id 
            ORDER BY r.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM reviews WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getByRoomId($room_id)
    {
        $stmt = $this->pdo->prepare("
            SELECT r.*, u.firstname, u.lastname 
            FROM reviews r 
            JOIN users u ON r.user_id = u.id 
            WHERE r.room_id = ? 
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$room_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
