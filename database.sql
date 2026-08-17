CREATE DATABASE IF NOT EXISTS alert_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE alert_db;

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  color VARCHAR(20) NOT NULL
);

CREATE TABLE IF NOT EXISTS notes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  body TEXT,
  date VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS alerts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  alert_date DATE NOT NULL,
  alert_time TIME NOT NULL,
  category_id INT,
  priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
  status ENUM('active', 'completed') DEFAULT 'active',
  description TEXT,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(255),
  nickname VARCHAR(100),
  department VARCHAR(100),
  role ENUM('user', 'admin') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS webauthn_credentials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  credential_id TEXT NOT NULL,
  public_key TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO categories (name, color) VALUES 
('Work', 'work'),
('Study', 'study'),
('Personal', 'personal'),
('Finance', 'finance'),
('Health', 'health'),
('Other', 'other')
ON DUPLICATE KEY UPDATE name=name;

-- Sample data
INSERT INTO notes (title, body, date) VALUES 
('ไอเดียโปรเจกต์ใหม่', 'ลูกค้าเป้าหมาย / กลุ่มคนทำงาน / เครื่องมือ 3 อย่างที่จำเป็น', 'May 20'),
('รหัส Wi-Fi บ้าน', 'Network: Home_5G\nPassword: meeme1234', 'May 18'),
('รายการซื้อของเดือนนี้', '1. Apples Fuji\n2. Greek Yogurt\n3. Granola', 'May 15');

INSERT INTO alerts (title, alert_date, alert_time, category_id, priority, status) VALUES 
('สำรวจความประทับใจลูกค้า', '2025-05-24', '09:00:00', 1, 'high', 'active'),
('ประชุมทีม Project Alpha', '2025-05-24', '13:30:00', 1, 'medium', 'active'),
('จ่ายค่าน้ำและค่าอินเทอร์เน็ต', '2025-05-24', '18:00:00', 4, 'medium', 'active'),
('อ่านหนังสือ 30 นาที', '2025-05-24', '20:30:00', 2, 'low', 'active'),
('นัดพบลูกค้า KBank', '2025-05-26', '10:00:00', 1, 'medium', 'active'),
('อัปเดตการใช้งานระบบใหม่', '2025-05-26', '14:00:00', 1, 'medium', 'active'),
('ตรวจสุขภาพประจำปี', '2025-05-27', '09:00:00', 5, 'low', 'active');
