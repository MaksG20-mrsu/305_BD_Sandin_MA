-- =============================================
-- База данных СТО (Станция Технического Обслуживания)
-- =============================================

-- Включаем поддержку внешних ключей
PRAGMA foreign_keys = ON;

-- ---------------------------------------------
-- Таблица: Сотрудники (мастера)
-- ---------------------------------------------
CREATE TABLE employees (
    employee_id   INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name     TEXT NOT NULL,
    position      TEXT NOT NULL DEFAULT 'Мастер',
    phone         TEXT,
    salary_percent REAL NOT NULL CHECK (salary_percent > 0 AND salary_percent <= 100),
    hire_date     DATE NOT NULL DEFAULT (date('now')),
    fire_date     DATE DEFAULT NULL,
    is_active     INTEGER NOT NULL DEFAULT 1 CHECK (is_active IN (0, 1))
);

-- ---------------------------------------------
-- Таблица: Услуги (справочник)
-- ---------------------------------------------
CREATE TABLE services (
    service_id       INTEGER PRIMARY KEY AUTOINCREMENT,
    service_name     TEXT NOT NULL,
    duration_minutes INTEGER NOT NULL CHECK (duration_minutes > 0),
    price            REAL NOT NULL CHECK (price >= 0)
);

-- ---------------------------------------------
-- Таблица: Предварительная запись
-- ---------------------------------------------
CREATE TABLE appointments (
    appointment_id   INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_id      INTEGER NOT NULL,
    appointment_dt   DATETIME NOT NULL,
    status           TEXT NOT NULL DEFAULT 'запланирована' 
                     CHECK (status IN ('запланирована', 'выполнена', 'отменена')),
    created_at       DATETIME DEFAULT (datetime('now')),
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id)
);

-- ---------------------------------------------
-- Таблица: Услуги в предварительной записи
-- ---------------------------------------------
CREATE TABLE appointment_services (
    appt_service_id  INTEGER PRIMARY KEY AUTOINCREMENT,
    appointment_id   INTEGER NOT NULL,
    service_id       INTEGER NOT NULL,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(service_id),
    UNIQUE(appointment_id, service_id)
);

-- ---------------------------------------------
-- Таблица: Фактически выполненные работы
-- ---------------------------------------------
CREATE TABLE works (
    work_id       INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_id   INTEGER NOT NULL,
    work_dt       DATETIME NOT NULL DEFAULT (datetime('now')),
    total_price   REAL NOT NULL CHECK (total_price >= 0),
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id)
);

-- ---------------------------------------------
-- Таблица: Услуги в выполненной работе
-- ---------------------------------------------
CREATE TABLE work_services (
    work_service_id  INTEGER PRIMARY KEY AUTOINCREMENT,
    work_id          INTEGER NOT NULL,
    service_id       INTEGER NOT NULL,
    price_at_time    REAL NOT NULL CHECK (price_at_time >= 0),
    FOREIGN KEY (work_id) REFERENCES works(work_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(service_id),
    UNIQUE(work_id, service_id)
);

-- =============================================
-- Заполнение тестовыми данными
-- =============================================

-- Сотрудники
INSERT INTO employees (full_name, position, phone, salary_percent, hire_date) VALUES
('Иванов Иван Иванович', 'Мастер', '+79001112233', 40.0, '2024-01-15'),
('Петров Пётр Петрович', 'Старший мастер', '+79004445566', 45.0, '2023-06-01'),
('Сидорова Анна Викторовна', 'Мастер', '+79007778899', 35.0, '2024-03-10'),
('Козлов Дмитрий Сергеевич', 'Мастер', '+79001234567', 38.0, '2022-09-20');

-- Уволенный сотрудник (данные сохраняются)
INSERT INTO employees (full_name, position, phone, salary_percent, hire_date, fire_date, is_active) VALUES
('Смирнов Алексей Олегович', 'Мастер', '+79009876543', 30.0, '2021-02-01', '2025-08-15', 0);

-- Услуги
INSERT INTO services (service_name, duration_minutes, price) VALUES
('Замена масла', 30, 1500.00),
('Замена тормозных колодок', 60, 3500.00),
('Диагностика ходовой части', 45, 2000.00),
('Замена воздушного фильтра', 20, 800.00),
('Развал-схождение', 90, 4500.00),
('Замена ремня ГРМ', 180, 8000.00),
('Компьютерная диагностика', 40, 2500.00);

-- Предварительные записи
INSERT INTO appointments (employee_id, appointment_dt, status) VALUES
(1, '2026-11-22 10:00', 'запланирована'),
(1, '2026-11-22 12:00', 'запланирована'),
(2, '2026-11-22 09:00', 'выполнена'),
(3, '2026-11-23 14:00', 'запланирована'),
(5, '2025-07-10 11:00', 'выполнена');

-- Услуги в записях
INSERT INTO appointment_services (appointment_id, service_id) VALUES
(1, 1), (1, 4),   -- запись 1: замена масла + фильтр
(2, 2),           -- запись 2: тормозные колодки
(3, 3), (3, 7),   -- запись 3: диагностика ходовой + компьютерная
(4, 5),           -- запись 4: развал-схождение
(5, 1);           -- запись 5 (уволенный мастер): замена масла

-- Выполненные работы
INSERT INTO works (employee_id, work_dt, total_price) VALUES
(2, '2026-11-22 09:30', 4500.00),
(5, '2025-07-10 11:30', 1500.00),
(1, '2026-11-20 15:00', 2300.00),
(3, '2026-11-21 10:00', 8000.00);

-- Услуги в выполненных работах
INSERT INTO work_services (work_id, service_id, price_at_time) VALUES
(1, 3, 2000.00), (1, 7, 2500.00),   -- работа 1: диагностика + компьютерная
(2, 1, 1500.00),                      -- работа 2: замена масла
(3, 1, 1500.00), (3, 4, 800.00),     -- работа 3: масло + фильтр
(4, 6, 8000.00);                      -- работа 4: ремень ГРМ

