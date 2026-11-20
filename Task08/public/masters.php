<?php
require_once __DIR__ . '/../db.php';
$pdo = getDb();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name']);
    $position = trim($_POST['position']);
    $phone = trim($_POST['phone']);
    $salaryPercent = (float)$_POST['salary_percent'];
    $services = $_POST['services'] ?? [];
    
    if ($fullName && $salaryPercent > 0 && $salaryPercent <= 100) {
        $stmt = $pdo->prepare("INSERT INTO employees (full_name, position, phone, salary_percent) VALUES (?, ?, ?, ?)");
        $stmt->execute([$fullName, $position, $phone, $salaryPercent]);
        $employeeId = $pdo->lastInsertId();
        
        // Привязка услуг (если есть таблица master_services)
        if (!empty($services)) {
            // Создаём таблицу если её нет
            $pdo->exec("CREATE TABLE IF NOT EXISTS master_services (
                master_service_id INTEGER PRIMARY KEY AUTOINCREMENT,
                employee_id INTEGER NOT NULL,
                service_id INTEGER NOT NULL,
                FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
                FOREIGN KEY (service_id) REFERENCES services(service_id),
                UNIQUE(employee_id, service_id)
            )");
            
            $stmt = $pdo->prepare("INSERT INTO master_services (employee_id, service_id) VALUES (?, ?)");
            foreach ($services as $serviceId) {
                $stmt->execute([$employeeId, $serviceId]);
            }
        }
        
        $message = "Мастер успешно добавлен!";
    } else {
        $message = "Ошибка: заполните все обязательные поля корректно.";
    }
}

$services = $pdo->query("SELECT * FROM services ORDER BY service_name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить мастера</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 600px; background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input[type="text"], input[type="number"] { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
        .checkbox-group { margin-top: 10px; }
        .checkbox-group label { display: inline-block; margin-right: 15px; font-weight: normal; }
        button { margin-top: 20px; padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #45a049; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 5px; background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        a { color: #4CAF50; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Добавить мастера</h1>
        <p><a href="index.php">← Назад</a></p>
        
        <?php if ($message): ?>
            <div class="message <?= strpos($message, 'Ошибка') !== false ? 'error' : '' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <label>ФИО *</label>
            <input type="text" name="full_name" required>
            
            <label>Должность</label>
            <input type="text" name="position" value="Мастер">
            
            <label>Телефон</label>
            <input type="text" name="phone">
            
            <label>Процент от выручки (%) *</label>
            <input type="number" name="salary_percent" min="1" max="100" step="0.1" required>
            
            <label>Услуги, которые может выполнять:</label>
            <div class="checkbox-group">
                <?php foreach ($services as $service): ?>
                    <label>
                        <input type="checkbox" name="services[]" value="<?= $service['service_id'] ?>">
                        <?= htmlspecialchars($service['service_name']) ?>
                    </label><br>
                <?php endforeach; ?>
            </div>
            
            <button type="submit">Добавить мастера</button>
        </form>
    </div>
</body>
</html>
