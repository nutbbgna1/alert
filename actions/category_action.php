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

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$id, $user_id])) {
                echo json_encode(['success' => true]);
                exit;
            }
        }
        echo json_encode(['success' => false, 'error' => 'Could not delete category']);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
