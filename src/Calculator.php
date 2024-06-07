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

    public function addItem($name, $price, $userId)
    {
        $stmt = $this->pdo->prepare("INSERT INTO items (name, price, user_id) VALUES (:name, :price, :user_id)");
        $stmt->execute(['name' => $name, 'price' => $price, 'user_id' => $userId]);
        return $this->pdo->lastInsertId();
    }

    public function updateItem($id, $name, $price, $userId)
    {
        $stmt = $this->pdo->prepare("UPDATE items SET name = :name, price = :price WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['name' => $name, 'price' => $price, 'id' => $id, 'user_id' => $userId]);
    }
}
?>
