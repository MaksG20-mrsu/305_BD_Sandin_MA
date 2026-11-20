<?php
$dbPath = __DIR__ . '/sto.db';
$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Получаем список всех мастеров
$stmt = $pdo->query("SELECT employee_id, full_name FROM employees ORDER BY full_name");
$masters = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Доступные мастера:\n";
foreach ($masters as $master) {
    echo "{$master['employee_id']} - {$master['full_name']}\n";
}

echo "\nВведите номер мастера (или Enter для всех): ";
$input = trim(fgets(STDIN));

if ($input === '') {
    // Все мастера
    $sql = "SELECT e.employee_id, e.full_name, w.work_dt, s.service_name, ws.price_at_time
            FROM works w
            JOIN employees e ON w.employee_id = e.employee_id
            JOIN work_services ws ON w.work_id = ws.work_id
            JOIN services s ON ws.service_id = s.service_id
            ORDER BY e.full_name, w.work_dt";
    $stmt = $pdo->query($sql);
} else {
    // Валидация номера
    if (!is_numeric($input)) {
        die("Ошибка: введите числовой номер мастера.\n");
    }
    $masterId = (int)$input;
    
    // Проверка существования мастера
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE employee_id = ?");
    $checkStmt->execute([$masterId]);
    if ($checkStmt->fetchColumn() == 0) {
        die("Ошибка: мастер с номером $masterId не найден.\n");
    }
    
    $sql = "SELECT e.employee_id, e.full_name, w.work_dt, s.service_name, ws.price_at_time
            FROM works w
            JOIN employees e ON w.employee_id = e.employee_id
            JOIN work_services ws ON w.work_id = ws.work_id
            JOIN services s ON ws.service_id = s.service_id
            WHERE e.employee_id = ?
            ORDER BY e.full_name, w.work_dt";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$masterId]);
}

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    echo "\nНет данных о выполненных работах.\n";
    exit;
}

// Вывод таблицы с псевдографикой
echo "\n";
echo "+------+----------------------------+---------------------+----------------------------------+------------+\n";
echo "| №    | ФИО мастера                | Дата работы         | Услуга                           | Стоимость  |\n";
echo "+------+----------------------------+---------------------+----------------------------------+------------+\n";

foreach ($rows as $row) {
    printf("| %-4d | %-26s | %-19s | %-32s | %10.2f |\n",
        $row['employee_id'],
        mb_substr($row['full_name'], 0, 26),
        $row['work_dt'],
        mb_substr($row['service_name'], 0, 32),
        $row['price_at_time']
    );
}

echo "+------+----------------------------+---------------------+----------------------------------+------------+\n";
echo "Всего записей: " . count($rows) . "\n";
