<?php

namespace App\Models;

use App\Config\Database;

class Room
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM room WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getAll()
    {
        return $this->pdo->query("SELECT * FROM room ORDER BY created_at DESC")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAllOrderByPrice($limit = null)
    {
        $sql = "SELECT * FROM room ORDER BY room_price ASC";
        
        if ($limit !== null) {
            $sql .= " LIMIT $limit";
        }

        return $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO room (room_name, room_price, room_description, image_path) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['room_name'],
            $data['room_price'],
            $data['room_description'],
            $data['image_path'] ?? null
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data)
    {
        $stmt = $this->pdo->prepare("
            UPDATE room 
            SET room_name = ?, room_price = ?, room_description = ?, image_path = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['room_name'],
            $data['room_price'],
            $data['room_description'],
            $data['image_path'],
            $id
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM room WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function count()
    {
        return $this->pdo->query("SELECT COUNT(*) FROM room")->fetchColumn();
    }

    public function isAvailable($room_id, $check_in, $check_out)
    {
        $stmt = $this->pdo->prepare("
            SELECT b.* FROM booking b 
            INNER JOIN booking_details bd ON b.booking_details_id = bd.id 
            WHERE b.room_id = ? AND b.status != 'cancelled' AND
            ((bd.check_in BETWEEN ? AND ?) OR 
            (bd.check_out BETWEEN ? AND ?) OR 
            (? BETWEEN bd.check_in AND bd.check_out))
        ");
        $stmt->execute([$room_id, $check_in, $check_out, $check_in, $check_out, $check_in]);
        
        return $stmt->rowCount() === 0;
    }
}
