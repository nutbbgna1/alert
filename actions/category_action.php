<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}
require_once '../includes/db.php';
$user_id = $_SESSION['user_id'];

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $color = trim($_POST['color'] ?? 'other');
        
        if (!$name) {
            echo json_encode(['success' => false, 'error' => 'Name is required.']);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO categories (name, color, user_id) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $color, $user_id])) {
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
