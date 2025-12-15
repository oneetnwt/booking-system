<?php

namespace App\Models;

use App\Config\Database;

class User
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findByEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findByPhoneNumber($phone_number)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE phone_number = ?");
        $stmt->execute([$phone_number]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (firstname, lastname, email, phone_number, password, role) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['firstname'],
            $data['lastname'],
            $data['email'],
            $data['phone_number'],
            $data['password'],
            $data['role'] ?? 'user'
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data)
    {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        $values[] = $id;

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAll($limit = null, $offset = null)
    {
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        
        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT $offset, $limit";
        }

        return $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function count()
    {
        return $this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }

    public function countByRole($role)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE role = ?");
        $stmt->execute([$role]);
        return $stmt->fetchColumn();
    }
}
