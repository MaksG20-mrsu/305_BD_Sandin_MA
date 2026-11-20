<?php
function getDb() {
    $dbPath = __DIR__ . '/data/sto.db';
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
    
    // Создаём дополнительные таблицы если их нет
    $pdo->exec("CREATE TABLE IF NOT EXISTS master_services (
        master_service_id INTEGER PRIMARY KEY AUTOINCREMENT,
        employee_id INTEGER NOT NULL,
        service_id INTEGER NOT NULL,
        FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
        FOREIGN KEY (service_id) REFERENCES services(service_id),
        UNIQUE(employee_id, service_id)
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS schedules (
        schedule_id INTEGER PRIMARY KEY AUTOINCREMENT,
        employee_id INTEGER NOT NULL,
        work_date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
        UNIQUE(employee_id, work_date)
    )");
    
    return $pdo;
}
