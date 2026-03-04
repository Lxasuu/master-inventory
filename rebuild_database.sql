-- Rebuild Database with Full Corrected Schema
DROP DATABASE IF EXISTS meta_inventory_sql;
CREATE DATABASE meta_inventory_sql;
USE meta_inventory_sql;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    public_id VARCHAR(32) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    email VARCHAR(100),
    role ENUM('admin', 'pic', 'user') DEFAULT 'user',
    is_active TINYINT(1) DEFAULT 1,
    photo VARCHAR(255),
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    location_name VARCHAR(100) UNIQUE NOT NULL,
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8)
);

CREATE TABLE conditions (
    condition_id INT AUTO_INCREMENT PRIMARY KEY,
    condition_name VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE check_statuses (
    check_status_id INT AUTO_INCREMENT PRIMARY KEY,
    status_name VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE pcs (
    pc_id INT AUTO_INCREMENT PRIMARY KEY,
    unique_code VARCHAR(50) UNIQUE NOT NULL,
    unique_name VARCHAR(100) NOT NULL,
    location_id INT,
    condition_id INT,
    check_status_id INT,
    is_ready TINYINT(1) DEFAULT 1,
    internet TINYINT(1) DEFAULT 1,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (location_id) REFERENCES locations(location_id),
    FOREIGN KEY (condition_id) REFERENCES conditions(condition_id),
    FOREIGN KEY (check_status_id) REFERENCES check_statuses(check_status_id)
);

CREATE TABLE applications (
    app_id INT AUTO_INCREMENT PRIMARY KEY,
    app_name VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE pc_applications (
    pc_id INT,
    app_id INT,
    installed TINYINT(1) DEFAULT 1,
    installed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (pc_id, app_id),
    FOREIGN KEY (pc_id) REFERENCES pcs(pc_id) ON DELETE CASCADE,
    FOREIGN KEY (app_id) REFERENCES applications(app_id) ON DELETE CASCADE
);

CREATE TABLE pc_updates (
    update_id INT AUTO_INCREMENT PRIMARY KEY,
    pc_id INT,
    updated_by INT,
    condition_id INT,
    check_status_id INT,
    internet TINYINT(1),
    is_ready TINYINT(1),
    change_note TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pc_id) REFERENCES pcs(pc_id) ON DELETE CASCADE
);

CREATE TABLE activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    entity VARCHAR(50),
    entity_id INT,
    title VARCHAR(255),
    detail TEXT,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

INSERT INTO users (public_id, username, password_hash, full_name, email, role) VALUES ('389f8fe9780e3e3e551294e0296a55ca', 'admin', '$2y$10$zc7dfCxaf2emrcKuhz//9e1.O36q42FsG4lhTIsGLyYEEGOp/Kyxe', 'Administrator', 'admin@meta.inv', 'admin');
INSERT INTO locations (location_name) VALUES ('Komlab 1');
INSERT INTO locations (location_name) VALUES ('Komlab 2');
INSERT INTO locations (location_name) VALUES ('Komlab l3');
INSERT INTO conditions (condition_name) VALUES ('Baik');
INSERT INTO conditions (condition_name) VALUES ('Rusak');
INSERT INTO conditions (condition_name) VALUES ('Service');
INSERT INTO check_statuses (status_name) VALUES ('Belum');
INSERT INTO check_statuses (status_name) VALUES ('Sudah');
INSERT INTO applications (app_name) VALUES ('Android Studio');
INSERT INTO applications (app_name) VALUES ('Laragon');
INSERT INTO applications (app_name) VALUES ('Microsoft Office');
INSERT INTO applications (app_name) VALUES ('Sketch UP');
INSERT INTO applications (app_name) VALUES ('Visual Studio Code');
INSERT INTO applications (app_name) VALUES ('WPS');
INSERT INTO applications (app_name) VALUES ('XAMPP');
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00530', 'MIP COMLAB 11', (SELECT location_id FROM locations WHERE location_name = 'Komlab 2' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-16 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Android Studio' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00542', 'MIP COMLAB 02', (SELECT location_id FROM locations WHERE location_name = 'Komlab 2' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-16 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Android Studio' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00552', 'MIP COMLAB 18', (SELECT location_id FROM locations WHERE location_name = 'Komlab 2' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-16 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Android Studio' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00556', 'MIP COMLAB 24', (SELECT location_id FROM locations WHERE location_name = 'Komlab 2' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-16 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Android Studio' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00544', 'MIP COMLAB 04', (SELECT location_id FROM locations WHERE location_name = 'Komlab 2' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-16 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Android Studio' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00536', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab 2' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-16 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Android Studio' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00529', 'MIP COMLAB 12', (SELECT location_id FROM locations WHERE location_name = 'Komlab 2' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-16 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Android Studio' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00547', 'MIP COMLAB 13', (SELECT location_id FROM locations WHERE location_name = 'Komlab 2' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-16 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Android Studio' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00555', 'MIP COMLAB 19', (SELECT location_id FROM locations WHERE location_name = 'Komlab 2' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-16 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Android Studio' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00550', 'MIP COMLAB 14', (SELECT location_id FROM locations WHERE location_name = 'Komlab 2' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-16 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Android Studio' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00539', 'MIP COMLAB 16', (SELECT location_id FROM locations WHERE location_name = 'Komlab 2' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-16 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Android Studio' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00534', 'MIP COMLAB 07', (SELECT location_id FROM locations WHERE location_name = 'Komlab 1' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-17 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'WPS' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Laragon' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00535', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab 1' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-17 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Android Studio' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00533', 'MIP COMLAB 09', (SELECT location_id FROM locations WHERE location_name = 'Komlab 1' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-17 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'WPS' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Laragon' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00554', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab 1' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-17 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'WPS' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00532', 'MIP COMLAB 25', (SELECT location_id FROM locations WHERE location_name = 'Komlab 1' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-17 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'WPS' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00546', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab 1' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-17 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Android Studio' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00528', 'MIP COMLAB 21', (SELECT location_id FROM locations WHERE location_name = 'Komlab 1' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-17 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'WPS' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00549', 'MIP COMLAB 05', (SELECT location_id FROM locations WHERE location_name = 'Komlab 1' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-17 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00541', 'MIP COMLAB 23', (SELECT location_id FROM locations WHERE location_name = 'Komlab 1' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-17 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Android Studio' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00540', 'MIP COMLAB 17', (SELECT location_id FROM locations WHERE location_name = 'Komlab 1' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-17 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00538', 'MIP COMLAB 15', (SELECT location_id FROM locations WHERE location_name = 'Komlab 1' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-17 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00543', 'MIP COMLAB 03', (SELECT location_id FROM locations WHERE location_name = 'Komlab 1' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-17 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'WPS' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00548', 'MIP COMLAB 08', (SELECT location_id FROM locations WHERE location_name = 'Komlab 1' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-17 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'WPS' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0915/08/00531', ' MIP COMLAB 10', (SELECT location_id FROM locations WHERE location_name = 'Komlab 1' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-17 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Android Studio' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/1222/01/HIBAHPENDIDIKANVOKASI', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab 1' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-17 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'WPS' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('APK/01/0320/28/01102', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab 1' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-17 00:00:00', '2026-01-27 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan WIFI', '2026-01-27 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Android Studio' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 1', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-17 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 2', 'APK/01/0117/13/00725', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-17 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 3', 'APK/01/0117/13/00737', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 4', 'APK/01/0117/13/00718', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 5', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 6', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 7', 'APK/01/0117/13/00725', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 8', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 9', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 10', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 11', 'APK/01/0117/13/00710', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 12', 'APK/01/0117/13/00712', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 13', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 14', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 15', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 16', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 17', 'APK/01/0117/13/00735', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 18', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 19', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 20', 'APK/01/0117/13/00720', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 21', 'APK/01/0117/13/00716', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 22', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 23', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 24', 'APK/01/0117/13/00726', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 25', 'APK/01/0117/13/00714', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 1, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 26', 'APK/01/0117/13/00842', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 0, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 27', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 0, 1, '2025-12-18 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 28', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 0, 1, '2025-12-19 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 29', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Sudah' LIMIT 1), 1, 0, 1, '2025-12-20 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com | Internet: Memiliki akses internet menggunakan LAN', '2026-02-06 00:00:00');
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Microsoft Office' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Visual Studio Code' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'XAMPP' LIMIT 1;
INSERT INTO pc_applications (pc_id, app_id) SELECT LAST_INSERT_ID(), app_id FROM applications WHERE app_name = 'Sketch UP' LIMIT 1;
INSERT INTO pcs (unique_code, unique_name, location_id, condition_id, check_status_id, internet, is_ready, updated_by, created_at, updated_at) VALUES ('PC No 30', '-', (SELECT location_id FROM locations WHERE location_name = 'Komlab l3' LIMIT 1), (SELECT condition_id FROM conditions WHERE condition_name = 'Baik' LIMIT 1), (SELECT check_status_id FROM check_statuses WHERE status_name = 'Belum' LIMIT 1), 1, 1, 1, '2025-12-21 00:00:00', '2026-02-06 00:00:00');
INSERT INTO pc_updates (pc_id, updated_by, change_note, updated_at) VALUES (LAST_INSERT_ID(), 1, 'Initial import. PIC: ilhamdwiischan@gmail.com', '2026-02-06 00:00:00');