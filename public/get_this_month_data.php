<?php
require '../config/database.php';

header('Content-Type: application/json');

try {
    // Получаем прошлый месяц и год
    $lastMonth = date('m', strtotime('-1 month'));
    $lastYear = date('Y', strtotime('-1 month'));

    // Отладочные сообщения
    error_log("Last Month: $lastMonth");
    error_log("Last Year: $lastYear");

    // Запрос для получения общей суммы доходов и расходов за прошлый месяц
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS income,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS expenses,
            SUM(amount) AS total
        FROM transactions
        WHERE MONTH(date) = :lastMonth AND YEAR(date) = :lastYear
    ");
    $stmt->execute(['lastMonth' => $lastMonth, 'lastYear' => $lastYear]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Отладочные сообщения
    error_log("Query Result: " . print_r($result, true));

    if ($result) {
        $response = [
            'success' => true,
            'total' => $result['total'],
            'income' => $result['income'],
            'expenses' => abs($result['expenses']) // Преобразуем расходы в положительное значение
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'No data found for the last month'
        ];
    }
} catch (PDOException $e) {
    $response = [
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ];
}

echo json_encode($response);
?>
