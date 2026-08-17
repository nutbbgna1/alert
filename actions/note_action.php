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
        $title = trim($_POST['title'] ?? '');
        $body  = trim($_POST['body'] ?? '');
        $date  = date('M j');
        if (!$title) {
            echo json_encode(['success' => false, 'error' => 'Title is required.']);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO notes (title, body, date, user_id) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$title, $body, $date, $user_id])) {
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$id, $user_id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }
}

echo json_encode(['success' => false]);
?>
