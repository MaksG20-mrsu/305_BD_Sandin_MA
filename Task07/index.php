<?php
$dbPath = __DIR__ . '/sto.db';
$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Получаем список мастеров для выпадающего списка
$stmt = $pdo->query("SELECT employee_id, full_name FROM employees ORDER BY full_name");
$masters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Определяем выбранного мастера
$selectedMaster = isset($_GET['master_id']) ? (int)$_GET['master_id'] : 0;

// Формируем запрос
if ($selectedMaster > 0) {
    $sql = "SELECT e.employee_id, e.full_name, w.work_dt, s.service_name, ws.price_at_time
            FROM works w
            JOIN employees e ON w.employee_id = e.employee_id
            JOIN work_services ws ON w.work_id = ws.work_id
            JOIN services s ON ws.service_id = s.service_id
            WHERE e.employee_id = ?
            ORDER BY e.full_name, w.work_dt";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$selectedMaster]);
} else {
    $sql = "SELECT e.employee_id, e.full_name, w.work_dt, s.service_name, ws.price_at_time
            FROM works w
            JOIN employees e ON w.employee_id = e.employee_id
            JOIN work_services ws ON w.work_id = ws.work_id
            JOIN services s ON ws.service_id = s.service_id
            ORDER BY e.full_name, w.work_dt";
    $stmt = $pdo->query($sql);
}

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список оказанных услуг СТО</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        h1 {
            color: #333;
        }
        .filter {
            background: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        select, button {
            padding: 8px 12px;
            font-size: 14px;
            margin-right: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .no-data {
            padding: 20px;
            text-align: center;
            background: white;
            border-radius: 5px;
        }
        .total {
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Список оказанных услуг СТО</h1>
    
    <div class="filter">
        <form method="GET">
            <label for="master_id">Выберите мастера:</label>
            <select name="master_id" id="master_id">
                <option value="0">Все мастера</option>
                <?php foreach ($masters as $master): ?>
                    <option value="<?= $master['employee_id'] ?>" 
                            <?= $selectedMaster == $master['employee_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($master['full_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Показать</button>
        </form>
    </div>

    <?php if (empty($rows)): ?>
        <div class="no-data">Нет данных о выполненных работах.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>№ мастера</th>
                    <th>ФИО мастера</th>
                    <th>Дата работы</th>
                    <th>Услуга</th>
                    <th>Стоимость</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= $row['employee_id'] ?></td>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= $row['work_dt'] ?></td>
                        <td><?= htmlspecialchars($row['service_name']) ?></td>
                        <td><?= number_format($row['price_at_time'], 2, '.', ' ') ?> руб.</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="total">Всего записей: <?= count($rows) ?></div>
    <?php endif; ?>
</body>
</html>
