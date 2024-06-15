<?php
require '../config/database.php';
require '../src/Calculator.php';
session_start();

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'User not logged in.';
    echo json_encode($response);
    exit;
}

$calculator = new Calculator($pdo);
$userId = $_SESSION['user_id'];

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $id, 'user_id' => $userId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        $response['success'] = true;
        $response['item'] = $item;
    } else {
        $response['message'] = 'Item not found or not authorized.';
    }
}

echo json_encode($response);
?>