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
            <!-- Filter Buttons -->
            <div class="filter-buttons">
                <button id="this-month">This Month</button>
                <button id="last-month">Last Month</button>
                <button id="other">Other</button>
            </div>

            <!-- Table for Items -->
            <div class="table-container">
                <table id="items-table">
                    <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $item):?>
                        <tr>
                            <td><?= htmlspecialchars($item['name'])?></td>
                            <td><?= htmlspecialchars($item['price'])?></td>
                            <td>
                                <button class="edit" data-id="<?= $item['id']?>">Edit</button>
                                <button class="delete" data-id="<?= $item['id']?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach;?>
                    </tbody>
                </table>
            </div>

            <!-- Form for adding items -->
            <form id="addItemForm" method="POST" action="">
                <div class="form-group">
                    <label for="name">Item Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="price">Price:</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="type">Type:</label>
                    <select id="type" name="type">
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>
                <button type="submit">Add Item</button>
            </form>

            <!-- Independent Table -->
            <div class="independent-table-container">
                <table id="balance-table">
                    <thead>
                    <tr>
                        <th>Balance</th>
                        <th>Today you spend</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td id="balance">0.00</td>
                        <td id="today-spending">0.00</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pie Chart Container -->
        <div class="chart-container" style="width: 275px; height: 275px;">
            <canvas id="expensesChart"></canvas>
            <h2>Total: <?= $total?></h2>
        </div>

        <!-- Edit Item Form -->
        <div id="editFormContainer" class="edit-form-container" style="display: none;">
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

        <!-- Modal for This Month -->
        <div id="thisMonthModal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>This Month</h2>
                <p>Total: <span id="this-month-total"></span></p>
                <hr>
                <p>Income: <span id="this-month-income-percentage"></span>%</p>
                <p>+<span id="this-month-income"></span></p>
                <div class="progress-bar">
                    <div id="income-bar" class="progress-bar-fill" style="background-color: green;"></div>
                </div>
                <hr>
                <p>Expenses: <span id="this-month-expenses-percentage"></span>%</p>
                <p>-<span id="this-month-expenses"></span></p>
                <div class="progress-bar">
                    <div id="expenses-bar" class="progress-bar-fill" style="background-color: red;"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const chartData = <?= json_encode($chartData)?>;
    </script>
    <script src="script.js"></script>
</body>
</html>

<?php include '../templates/footer.php';?>
