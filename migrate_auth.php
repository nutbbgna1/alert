<?php
require_once 'includes/db.php';

try {
    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Create webauthn_credentials table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS webauthn_credentials (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            credential_id TEXT NOT NULL,
            public_key TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Add user_id to tables
    $tables = ['alerts', 'categories', 'notes'];
    foreach ($tables as $table) {
        $cols = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'user_id'")->fetchAll();
        if (empty($cols)) {
            $pdo->exec("ALTER TABLE `$table` ADD COLUMN user_id INT NULL");
        }
    }

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
