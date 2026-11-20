<?php
require_once __DIR__ . '/../db.php';
$pdo = getDb();

// Создаём таблицу графика если её нет
$pdo->exec("CREATE TABLE IF NOT EXISTS schedules (
    schedule_id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_id INTEGER NOT NULL,
    work_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    UNIQUE(employee_id, work_date)
)");

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = (int)$_POST['employee_id'];
    $workDate = $_POST['work_date'];
    $startTime = $_POST['start_time'];
    $endTime = $_POST['end_time'];
    
    if ($employeeId && $workDate && $startTime && $endTime) {
        if ($startTime < $endTime) {
            try {
                $stmt = $pdo->prepare("INSERT INTO schedules (employee_id, work_date, start_time, end_time) VALUES (?, ?, ?, ?)");
                $stmt->execute([$employeeId, $workDate, $startTime, $endTime]);
                $message = "График успешно добавлен!";
            } catch (PDOException $e) {
                $message = "Ошибка: график на эту дату уже существует.";
            }
        } else {
            $message = "Ошибка: время начала должно быть раньше времени окончания.";
        }
    } else {
        $message = "Ошибка: заполните все поля.";
    }
}

$masters = $pdo->query("SELECT employee_id, full_name FROM employees WHERE is_active = 1 ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
$schedules = $pdo->query("SELECT s.*, e.full_name FROM schedules s JOIN employees e ON s.employee_id = e.employee_id ORDER BY s.work_date DESC, s.start_time")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>График работы мастеров</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1, h2 { color: #333; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        select, input[type="date"], input[type="time"] { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
        button { margin-top: 20px; padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #45a049; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 5px; background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #4CAF50; color: white; }
        a { color: #4CAF50; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>График работы мастеров</h1>
        <p><a href="index.php">← Назад</a></p>
        
        <?php if ($message): ?>
            <div class="message <?= strpos($message, 'Ошибка') !== false ? 'error' : '' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <label>Мастер *</label>
            <select name="employee_id" required>
                <option value="">Выберите мастера</option>
                <?php foreach ($masters as $master): ?>
                    <option value="<?= $master['employee_id'] ?>"><?= htmlspecialchars($master['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
            
            <label>Дата *</label>
            <input type="date" name="work_date" required>
            
            <label>Время начала *</label>
            <input type="time" name="start_time" required>
            
            <label>Время окончания *</label>
            <input type="time" name="end_time" required>
            
            <button type="submit">Добавить график</button>
        </form>
        
        <h2>Текущий график</h2>
        <?php if (empty($schedules)): ?>
            <p>График пока не задан.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Мастер</th>
                        <th>Дата</th>
                        <th>Начало</th>
                        <th>Окончание</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schedules as $schedule): ?>
                        <tr>
                            <td><?= htmlspecialchars($schedule['full_name']) ?></td>
                            <td><?= $schedule['work_date'] ?></td>
                            <td><?= $schedule['start_time'] ?></td>
                            <td><?= $schedule['end_time'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
