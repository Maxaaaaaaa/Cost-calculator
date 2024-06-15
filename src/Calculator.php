<?php
// src/Calculator.php

class Calculator
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getItems($userId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM items WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function calculateTotal($items)
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'];
        }
        return $total;
    }

    public function calculateTodaySpending($userId)
    {
        $stmt = $this->pdo->prepare("SELECT SUM(price) as total FROM items WHERE user_id = :user_id AND DATE(created_at) = CURDATE()");
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function addItem($name, $price, $userId)
    {
        $stmt = $this->pdo->prepare("INSERT INTO items (name, price, user_id, created_at) VALUES (:name, :price, :user_id, NOW())");
        $stmt->execute(['name' => $name, 'price' => $price, 'user_id' => $userId]);
        return $this->pdo->lastInsertId();
    }

    public function updateItem($id, $name, $price, $userId)
    {
        $stmt = $this->pdo->prepare("UPDATE items SET name = :name, price = :price WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['name' => $name, 'price' => $price, 'id' => $id, 'user_id' => $userId]);
    }

    public function deleteItem($id, $userId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM items WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public function filterItems($userId, $filter)
    {
        switch ($filter) {
            case 'this-month':
                $startDate = date('Y-m-01');
                $endDate = date('Y-m-t');
                break;
            case 'last-month':
                $startDate = date('Y-m-01', strtotime('first day of last month'));
                $endDate = date('Y-m-t', strtotime('last day of last month'));
                break;
            case 'other':
                // Define your custom date range for 'other'
                $startDate = '2020-01-01';
                $endDate = '2020-12-31';
                break;
            default:
                return [];
        }

        $stmt = $this->pdo->prepare("SELECT * FROM items WHERE user_id = :user_id AND created_at BETWEEN :start_date AND :end_date");
        $stmt->execute(['user_id' => $userId, 'start_date' => $startDate, 'end_date' => $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
