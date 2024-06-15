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
    $id = $_POST['id'];

    // Check if the item exists and belongs to the user
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $id, 'user_id' => $userId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        // Delete the item
        $stmt = $pdo->prepare("DELETE FROM items WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);

        if ($stmt->rowCount() > 0) {
            $items = $calculator->getItems($userId);
            $total = $calculator->calculateTotal($items);
            $todaySpending = $calculator->calculateTodaySpending($userId);

            $response['success'] = true;
            $response['total'] = number_format($total, 2, '.', ''); // Format total to 2 decimal places
            $response['todaySpending'] = number_format($todaySpending, 2, '.', ''); // Format today's spending to 2 decimal places
            $response['items'] = $items; // Return updated items for chart update
        } else {
            $response['message'] = 'Failed to delete the item.';
        }
    } else {
        $response['message'] = 'Item not found or not authorized.';
    }
}

echo json_encode($response);
?>
