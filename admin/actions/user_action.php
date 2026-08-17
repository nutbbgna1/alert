<?php
require_once '../includes/admin_auth.php';
require_once '../includes/db.php';

$action = $_POST['action'] ?? '';
$userId = $_POST['user_id'] ?? 0;

if (!$userId || $userId == $_SESSION['user_id']) {
    // Basic protection against self-deletion/demotion
    $_SESSION['error'] = 'Invalid action or cannot modify yourself.';
    header("Location: ../users.php");
    exit;
}

if ($action === 'delete') {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $_SESSION['msg'] = 'User deleted successfully.';
} elseif ($action === 'toggle_role') {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $currentRole = $stmt->fetchColumn();
    
    if ($currentRole) {
        $newRole = ($currentRole === 'admin') ? 'user' : 'admin';
        $updateStmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $updateStmt->execute([$newRole, $userId]);
        $_SESSION['msg'] = 'User role updated successfully.';
    }
}

header("Location: ../users.php");
exit;
