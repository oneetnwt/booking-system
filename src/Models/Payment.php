<?php

namespace App\Models;

use App\Config\Database;

class Payment
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function create($user_id, $amount, $payment_method)
    {
        $stmt = $this->pdo->prepare("INSERT INTO payment (user_id, amount, payment_method) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $amount, $payment_method]);
        return $this->pdo->lastInsertId();
    }

    public function createPaypalPayment($payment_id, $paypal_email)
    {
        $stmt = $this->pdo->prepare("INSERT INTO paypal_payment (payment_id, paypal_email) VALUES (?, ?)");
        $stmt->execute([$payment_id, $paypal_email]);
        return $this->pdo->lastInsertId();
    }

    public function createGcashPayment($payment_id, $gcash_number, $gcash_name)
    {
        $stmt = $this->pdo->prepare("INSERT INTO gcash_payment (payment_id, gcash_number, gcash_name) VALUES (?, ?, ?)");
        $stmt->execute([$payment_id, $gcash_number, $gcash_name]);
        return $this->pdo->lastInsertId();
    }

    public function createBankPayment($payment_id, $bank_name, $account_number, $account_name)
    {
        $stmt = $this->pdo->prepare("INSERT INTO bank_payment (payment_id, bank_name, account_number, account_name) VALUES (?, ?, ?, ?)");
        $stmt->execute([$payment_id, $bank_name, $account_number, $account_name]);
        return $this->pdo->lastInsertId();
    }

    public function getTotalRevenue()
    {
        $result = $this->pdo->query("SELECT SUM(amount) FROM payment")->fetchColumn();
        return $result ?? 0;
    }

    public function getRevenueByDateRange($start_date, $end_date)
    {
        $stmt = $this->pdo->prepare("
            SELECT SUM(amount) as total_revenue
            FROM payment
            WHERE created_at BETWEEN ? AND ?
        ");
        $stmt->execute([$start_date, $end_date]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total_revenue'] ?? 0;
    }
}
