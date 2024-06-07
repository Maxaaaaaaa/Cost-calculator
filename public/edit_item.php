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
    $name = $_POST['name'];
    $price = $_POST['price'];

    if (is_numeric($price) && $price > 0 && $price <= 999999999999.99) {
        // Check if the item exists and belongs to the user
        $stmt = $pdo->prepare("SELECT * FROM items WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            // Update the item
            $stmt = $pdo->prepare("UPDATE items SET name = :name, price = :price WHERE id = :id AND user_id = :user_id");
            $stmt->execute(['name' => $name, 'price' => $price, 'id' => $id, 'user_id' => $userId]);

            if ($stmt->rowCount() > 0) {
                $items = $calculator->getItems($userId);
                $total = $calculator->calculateTotal($items);

                $response['success'] = true;
                $response['item'] = ['id' => $id, 'name' => htmlspecialchars($name), 'price' => htmlspecialchars($price)];
                $response['total'] = $total;
                $response['items'] = $items; // Return updated items for chart update
            } else {
                $response['message'] = 'Item not found or not authorized.';
            }
        } else {
            $response['message'] = 'Item not found or not authorized.';
        }
    } else {
        $response['message'] = 'Invalid price value.';
    }
}

echo json_encode($response);
?>
