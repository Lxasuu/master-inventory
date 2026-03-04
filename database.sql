CREATE DATABASE IF NOT EXISTS meta_inventory_sql;
USE meta_inventory_sql;

-- 1. Locations
CREATE TABLE IF NOT EXISTS locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    location_name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- 2. Conditions
CREATE TABLE IF NOT EXISTS conditions (
    condition_id INT AUTO_INCREMENT PRIMARY KEY,
    condition_name VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- 3. Check Statuses
CREATE TABLE IF NOT EXISTS check_statuses (
    check_status_id INT AUTO_INCREMENT PRIMARY KEY,
    status_name VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- 4. Users
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    public_id VARCHAR(32) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role ENUM('admin', 'pic', 'user') DEFAULT 'user',
    is_active TINYINT(1) DEFAULT 1,
    photo VARCHAR(255),
    last_login_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. PCs
CREATE TABLE IF NOT EXISTS pcs (
    pc_id INT AUTO_INCREMENT PRIMARY KEY,
    unique_code VARCHAR(50) NOT NULL UNIQUE,
    unique_name VARCHAR(100) NOT NULL,
    location_id INT,
    condition_id INT,
    check_status_id INT,
    internet TINYINT(1) DEFAULT 0,
    is_ready TINYINT(1) DEFAULT 0,
    updated_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (location_id) REFERENCES locations(location_id) ON DELETE SET NULL,
    FOREIGN KEY (condition_id) REFERENCES conditions(condition_id) ON DELETE SET NULL,
    FOREIGN KEY (check_status_id) REFERENCES check_statuses(check_status_id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 6. PC Updates
CREATE TABLE IF NOT EXISTS pc_updates (
    update_id INT AUTO_INCREMENT PRIMARY KEY,
    pc_id INT,
    updated_by INT,
    change_note TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pc_id) REFERENCES pcs(pc_id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 7. Applications
CREATE TABLE IF NOT EXISTS applications (
    app_id INT AUTO_INCREMENT PRIMARY KEY,
    app_name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- 8. PC Applications (Many-to-Many)
CREATE TABLE IF NOT EXISTS pc_applications (
    pc_id INT,
    app_id INT,
    PRIMARY KEY (pc_id, app_id),
    FOREIGN KEY (pc_id) REFERENCES pcs(pc_id) ON DELETE CASCADE,
    FOREIGN KEY (app_id) REFERENCES applications(app_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 9. Activity Logs
CREATE TABLE IF NOT EXISTS activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50),
    entity VARCHAR(50),
    entity_id INT,
    title VARCHAR(255),
    detail JSON,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Seeding Data
INSERT IGNORE INTO locations (location_name) VALUES ('Lab A'), ('Lab B'), ('Lab C'), ('Gudang'), ('Lantai 1'), ('Lantai 2');
INSERT IGNORE INTO conditions (condition_name) VALUES ('Baik'), ('Rusak'), ('Perlu Perbaikan'), ('Normal');
INSERT IGNORE INTO check_statuses (status_name) VALUES ('Sudah Dicek'), ('Belum Dicek'), ('Checked'), ('Unchecked');

-- Default Admin Account
-- Username: admin
-- Password: admin123
INSERT IGNORE INTO users (public_id, username, email, password_hash, full_name, role, is_active)
VALUES ('7b1234567890abcdef1234567890abcd', 'admin', 'admin@example.com', '$2y$10$zc7dfCxaf2emrcKuhz//9e1.O36q42FsG4lhTIsGLyYEEGOp/Kyxe', 'Administrator', 'admin', 1);
