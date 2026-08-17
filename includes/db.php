<?php
require_once __DIR__ . '/lang.php';
if (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1')) {
    // Local XAMPP
    $host = '127.0.0.1';
    $db = 'alert_db';
    $user = 'root';
    $pass = '';
} else {
    // Live Server (Production)
    $host = 'localhost'; // บนโฮสติ้งจริงมักจะใช้ localhost
    $db = 'u865886212_alert';
    $user = 'u865886212_alert';
    $pass = 'Drcbd@1234@';
}
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// เปิดแสดง Error เพื่อใช้หาปัญหา 500
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Auto-create tables if they don't exist
    try {
        $pdo->query("SELECT 1 FROM users LIMIT 1");
        
        // Check if new columns exist
        try {
            $pdo->query("SELECT password_hash FROM users LIMIT 1");
        } catch (\PDOException $e) {
            $columns = [
                "password_hash VARCHAR(255) NOT NULL AFTER username",
                "full_name VARCHAR(255)",
                "nickname VARCHAR(100)",
                "department VARCHAR(100)",
                "role ENUM('user', 'admin') DEFAULT 'user'"
            ];
            foreach ($columns as $col) {
                try {
                    $pdo->exec("ALTER TABLE users ADD COLUMN $col");
                } catch (\PDOException $colEx) {}
            }
        }
        
        // Ensure alerts table has 'trashed' status
        try {
            $pdo->exec("ALTER TABLE alerts MODIFY COLUMN status ENUM('active', 'completed', 'trashed') DEFAULT 'active'");
        } catch (\PDOException $e) {}
    } catch (\PDOException $e) {
        // Create users table
        try {
            $pdo->exec("
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
            ");
        } catch (\PDOException $migrateEx) {
            die("Migration Error (users): " . $migrateEx->getMessage());
        }
    }
    
    // Check for categories table
    try {
        $pdo->query("SELECT 1 FROM categories LIMIT 1");
    } catch (\PDOException $e) {
        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS categories (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(50) NOT NULL,
                    color VARCHAR(20) NOT NULL,
                    user_id INT NULL
                );
                CREATE TABLE IF NOT EXISTS notes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    body TEXT,
                    date VARCHAR(50),
                    user_id INT NULL
                );
                CREATE TABLE IF NOT EXISTS alerts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    alert_date DATE NOT NULL,
                    alert_time TIME NOT NULL,
                    category_id INT,
                    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
                    status ENUM('active', 'completed', 'trashed') DEFAULT 'active',
                    description TEXT,
                    user_id INT NULL,
                    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
                );
                CREATE TABLE IF NOT EXISTS webauthn_credentials (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    credential_id TEXT NOT NULL,
                    public_key TEXT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                );
                
                INSERT IGNORE INTO categories (name, color) VALUES 
                ('Work', 'work'), ('Study', 'study'), ('Personal', 'personal'),
                ('Finance', 'finance'), ('Health', 'health'), ('Other', 'other');
            ");
        } catch (\PDOException $migrateEx) {
            die("Migration Error (other tables): " . $migrateEx->getMessage());
        }
    }
} catch (\PDOException $e) {
    die("Database Connection Error: " . $e->getMessage() . " | DB: " . $db . " | User: " . $user);
}
?>
