<?php
session_start();
require_once '../includes/db.php';

$action = $_POST['action'] ?? '';

if ($action === 'register') {
    $full_name = trim($_POST['full_name'] ?? '');
    $nickname = trim($_POST['nickname'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!$username || !$password || !$full_name || !$nickname || !$department) {
        $_SESSION['error'] = 'Please fill in all fields.';
        header("Location: ../register.php");
        exit;
    }
    
    // Check if username exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = 'Username already exists.';
        header("Location: ../register.php");
        exit;
    }
    
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, full_name, nickname, department) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $hash, $full_name, $nickname, $department]);
        $user_id = $pdo->lastInsertId();
        
        // Seed default categories for this user
        $defaultCats = [
            ['Work', 'work'],
            ['Study', 'study'],
            ['Personal', 'personal'],
            ['Finance', 'finance'],
            ['Health', 'health'],
            ['Other', 'other']
        ];
        $catStmt = $pdo->prepare("INSERT INTO categories (name, color, user_id) VALUES (?, ?, ?)");
        foreach ($defaultCats as $cat) {
            $catStmt->execute([$cat[0], $cat[1], $user_id]);
        }
        
        // Update all existing records with NULL user_id to this user (if they are the first user)
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $userCount = $stmt->fetchColumn();
        if ($userCount == 1) {
            $pdo->prepare("UPDATE alerts SET user_id = ? WHERE user_id IS NULL")->execute([$user_id]);
            $pdo->prepare("UPDATE categories SET user_id = ? WHERE user_id IS NULL")->execute([$user_id]);
            $pdo->prepare("UPDATE notes SET user_id = ? WHERE user_id IS NULL")->execute([$user_id]);
        }
        
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        header("Location: ../page/index.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = 'Registration failed: ' . $e->getMessage();
        header("Location: ../register.php");
        exit;
    }
} elseif ($action === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!$username || !$password) {
        $_SESSION['error'] = 'Please fill in all fields.';
        header("Location: ../login.php");
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: ../page/index.php");
        exit;
    } else {
        $_SESSION['error'] = 'Invalid username or password.';
        header("Location: ../login.php");
        exit;
    }
}
