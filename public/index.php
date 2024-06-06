<?php
require '../config/database.php';
require '../src/Calculator.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$calculator = new Calculator($pdo);
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];

    // Validate price
    if (is_numeric($price) && $price > 0 && $price <= 999999999999.99) {
        $calculator->addItem($name, $price, $userId);
    } else {
        echo "Invalid price value.";
    }
}

$items = $calculator->getItems($userId);
$total = $calculator->calculateTotal($items);

// Prepare data for the chart
$chartData = [];
foreach ($items as $item) {
    $chartData[] = [
        'name' => htmlspecialchars($item['name']),
        'price' => htmlspecialchars($item['price'])
    ];
}

include '../templates/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Online Cost Calculator</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container">
    <h1>Online Cost Calculator</h1>

    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Item Name:</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" required>
        </div>
        <button type="submit">Add Item</button>
    </form>

    <table>
        <thead>
        <tr>
            <th>Item</th>
            <th>Price</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= htmlspecialchars($item['price']) ?></td>
                <td>
                    <button class="edit" data-id="<?= $item['id'] ?>">Edit</button>
                    <button class="delete" data-id="<?= $item['id'] ?>">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <h2>Total: <?= $total ?></h2>

    <!-- Pie Chart Container -->
    <canvas id="expensesChart" width="400" height="400"></canvas>
</div>

<!-- Edit Item Modal -->
<div id="editModal" style="display:none;">
    <form id="editForm">
        <input type="hidden" id="edit-id" name="id">
        <div class="form-group">
            <label for="edit-name">Item Name:</label>
            <input type="text" id="edit-name" name="name" required>
        </div>
        <div class="form-group">
            <label for="edit-price">Price:</label>
            <input type="number" id="edit-price" name="price" step="0.01" required>
        </div>
        <button type="submit">Save Changes</button>
    </form>
</div>

<script>
    const chartData = <?= json_encode($chartData) ?>;
</script>
<script src="script.js"></script>
<footer>
    <p>© 2023 Ваше Имя. Все права защищены.</p>
    <p>Контактная информация: ваш.email@example.com</p>
</footer>
</body>
</html>

<?php include '../templates/footer.php'; ?>
