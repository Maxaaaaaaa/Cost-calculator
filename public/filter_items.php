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

$filter = $_GET['filter'] ?? '';

$items = $calculator->filterItems($userId, $filter);
$total = $calculator->calculateTotal($items);
$todaySpending = $calculator->calculateTodaySpending($userId);

$response['success'] = true;
$response['items'] = $items;
$response['total'] = number_format($total, 2, '.', '');
$response['todaySpending'] = number_format($todaySpending, 2, '.', '');

echo json_encode($response);
