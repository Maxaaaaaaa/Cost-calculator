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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];

    if (is_numeric($price) && $price > 0 && $price <= 999999999999.99) {
        $calculator->addItem($name, $price, $userId);
        $lastInsertId = $pdo->lastInsertId();
        $items = $calculator->getItems($userId);
        $total = $calculator->calculateTotal($items);

        $response['success'] = true;
        $response['item'] = ['id' => $lastInsertId, 'name' => htmlspecialchars($name), 'price' => htmlspecialchars($price)];
        $response['total'] = $total;
        $response['items'] = $items; // Return updated items for chart update
    } else {
        $response['message'] = 'Invalid price value.';
    }
}

echo json_encode($response);
?>
