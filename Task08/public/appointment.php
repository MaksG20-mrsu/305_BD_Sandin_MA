<?php
require_once __DIR__ . '/../db.php';
$pdo = getDb();

$message = '';
$selectedService = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;
$selectedMaster = isset($_GET['master_id']) ? (int)$_GET['master_id'] : 0;

// Обработка формы записи
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = (int)$_POST['service_id'];
    $masterId = (int)$_POST['master_id'];
    $appointmentDate = $_POST['appointment_date'];
    $appointmentTime = $_POST['appointment_time'];
    
    if ($serviceId && $masterId && $appointmentDate && $appointmentTime) {
        $appointmentDt = $appointmentDate . ' ' . $appointmentTime;
        
        try {
            // Проверяем доступность времени
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE employee_id = ? AND appointment_dt = ? AND status != 'отменена'");
            $checkStmt->execute([$masterId, $appointmentDt]);
            
            if ($checkStmt->fetchColumn() > 0) {
                $message = "Ошибка: это время уже занято!";
            } else {
                // Создаём запись
                $stmt = $pdo->prepare("INSERT INTO appointments (employee_id, appointment_dt, status) VALUES (?, ?, 'запланирована')");
                $stmt->execute([$masterId, $appointmentDt]);
                $appointmentId = $pdo->lastInsertId();
                
                // Привязываем услугу
                $stmt = $pdo->prepare("INSERT INTO appointment_services (appointment_id, service_id) VALUES (?, ?)");
                $stmt->execute([$appointmentId, $serviceId]);
                
                $message = "Запись успешно создана!";
                $selectedService = 0;
                $selectedMaster = 0;
            }
        } catch (PDOException $e) {
            $message = "Ошибка при создании записи: " . $e->getMessage();
        }
    } else {
        $message = "Ошибка: заполните все поля.";
    }
}

// Получаем список услуг
$services = $pdo->query("SELECT * FROM services ORDER BY service_name")->fetchAll(PDO::FETCH_ASSOC);

// Если выбрана услуга, получаем мастеров, которые её оказывают
$masters = [];
if ($selectedService > 0) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT e.employee_id, e.full_name 
        FROM employees e
        JOIN master_services ms ON e.employee_id = ms.employee_id
        WHERE ms.service_id = ? AND e.is_active = 1
        ORDER BY e.full_name
    ");
    $stmt->execute([$selectedService]);
    $masters = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Если выбран мастер, получаем его график
$schedules = [];
if ($selectedMaster > 0) {
    $stmt = $pdo->prepare("
        SELECT work_date, start_time, end_time 
        FROM schedules 
        WHERE employee_id = ? AND work_date >= date('now')
        ORDER BY work_date, start_time
    ");
    $stmt->execute([$selectedMaster]);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Предварительная запись</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 700px; background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1, h2 { color: #333; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        select, input[type="date"], input[type="time"] { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
        button { margin-top: 20px; padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #45a049; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 5px; background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .step { margin-top: 20px; padding: 15px; background: #f9f9f9; border-left: 4px solid #4CAF50; }
        .info { color: #666; font-size: 14px; margin-top: 5px; }
        a { color: #4CAF50; text-decoration: none; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; font-size: 14px; }
        th { background-color: #4CAF50; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Предварительная запись к мастеру</h1>
        <p><a href="index.php">← Назад</a></p>
        
        <?php if ($message): ?>
            <div class="message <?= strpos($message, 'Ошибка') !== false ? 'error' : '' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <form method="GET" id="selectForm">
            <div class="step">
                <h2>Шаг 1: Выберите услугу</h2>
                <label>Услуга *</label>
                <select name="service_id" onchange="this.form.submit()" required>
                    <option value="">-- Выберите услугу --</option>
                    <?php foreach ($services as $service): ?>
                        <option value="<?= $service['service_id'] ?>" <?= $selectedService == $service['service_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($service['service_name']) ?> (<?= $service['duration_minutes'] ?> мин, <?= number_format($service['price'], 2) ?> руб.)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <?php if ($selectedService > 0): ?>
                <div class="step">
                    <h2>Шаг 2: Выберите мастера</h2>
                    <?php if (empty($masters)): ?>
                        <p class="info">Нет мастеров, оказывающих эту услугу.</p>
                    <?php else: ?>
                        <label>Мастер *</label>
                        <select name="master_id" onchange="this.form.submit()" required>
                            <option value="">-- Выберите мастера --</option>
                            <?php foreach ($masters as $master): ?>
                                <option value="<?= $master['employee_id'] ?>" <?= $selectedMaster == $master['employee_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($master['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </form>
        
        <?php if ($selectedMaster > 0): ?>
            <div class="step">
                <h2>Шаг 3: Выберите дату и время</h2>
                
                <?php if (empty($schedules)): ?>
                    <p class="info">У этого мастера пока нет графика работы. Добавьте график на странице "График работы мастеров".</p>
                <?php else: ?>
                    <p class="info">Доступные дни работы мастера:</p>
                    <table>
                        <thead>
                            <tr>
                                <th>Дата</th>
                                <th>Начало</th>
                                <th>Окончание</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedules as $schedule): ?>
                                <tr>
                                    <td><?= $schedule['work_date'] ?></td>
                                    <td><?= $schedule['start_time'] ?></td>
                                    <td><?= $schedule['end_time'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <form method="POST">
                        <input type="hidden" name="service_id" value="<?= $selectedService ?>">
                        <input type="hidden" name="master_id" value="<?= $selectedMaster ?>">
                        
                        <label>Дата записи *</label>
                        <input type="date" name="appointment_date" min="<?= date('Y-m-d') ?>" required>
                        
                        <label>Время записи *</label>
                        <input type="time" name="appointment_time" required>
                        
                        <button type="submit">Записаться</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
