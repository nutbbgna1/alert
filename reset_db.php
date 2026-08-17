<?php
require_once __DIR__ . '/includes/db.php';

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS webauthn_credentials");
    $pdo->exec("DROP TABLE IF EXISTS alerts");
    $pdo->exec("DROP TABLE IF EXISTS notes");
    $pdo->exec("DROP TABLE IF EXISTS categories");
    $pdo->exec("DROP TABLE IF EXISTS users");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "<h1>Database has been reset successfully!</h1>";
    echo "<p>ตารางข้อมูลเก่าถูกลบเรียบร้อยแล้ว กรุณากลับไปที่หน้าเว็บหลัก (ระบบจะสร้างตารางใหม่ที่ถูกต้องให้อัตโนมัติ)</p>";
    echo '<a href="index.php">Go to Homepage</a>';
} catch (PDOException $e) {
    echo "Error resetting database: " . $e->getMessage();
}
?>
