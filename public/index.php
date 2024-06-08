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
    if (is_numeric($price) && $price >= -999999999999.99 && $price <= 999999999999.99) {
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container">
    <h1>Online Cost Calculator</h1>

    <div class="logout-container">
        <a href="logout.php" class="logout-button"><i class="fas fa-sign-out-alt"></i></a>
    </div>

    <div class="content">
        <div class="form-table-container">
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

            <div class="table-container">
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
            </div>

            <!-- Edit Item Form -->
            <div id="editFormContainer" style="display: none;">
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
        </div>

        <!-- Pie Chart Container -->
        <div class="chart-container" style="width: 275px; height: 275px;">
            <canvas id="expensesChart"></canvas>
            <h2>Total: <?= $total ?></h2>
        </div>
    </div>
</div>

<script>
    const chartData = <?= json_encode($chartData) ?>;
</script>
<script src="script.js"></script>
</body>
</html>

<?php include '../templates/footer.php'; ?>
