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

    // CREATE alert
    if ($action === 'create') {
        $title       = trim($_POST['title'] ?? '');
        $alert_date  = trim($_POST['alert_date'] ?? '');
        $alert_time  = trim($_POST['alert_time'] ?? '');
        if (empty($alert_time)) $alert_time = '09:00:00';
        
        $category_id = intval($_POST['category_id'] ?? 0) ?: null;
        $priority    = $_POST['priority'] ?? 'medium';
        $description = trim($_POST['description'] ?? '');

        if (!$title || !$alert_date) {
            echo json_encode(['success' => false, 'error' => 'Title and date are required.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO alerts (title, alert_date, alert_time, category_id, priority, description, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$title, $alert_date, $alert_time, $category_id, $priority, $description, $user_id])) {
                echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to insert alert']);
            }
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Database Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // TOGGLE complete/active
    if ($action === 'toggle_complete') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT status FROM alerts WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        $current = $stmt->fetchColumn();

        if ($current !== false) {
            $newStatus = ($current === 'active') ? 'completed' : 'active';
            $update = $pdo->prepare("UPDATE alerts SET status = ? WHERE id = ? AND user_id = ?");
            if ($update->execute([$newStatus, $id, $user_id])) {
                echo json_encode(['success' => true, 'newStatus' => $newStatus]);
                exit;
            }
        }
    }

    // DELETE alert
    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM alerts WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$id, $user_id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    // TRASH (move to trash status)
    if ($action === 'trash') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE alerts SET status = 'trashed' WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$id, $user_id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    // RESTORE from trash
    if ($action === 'restore') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE alerts SET status = 'active' WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$id, $user_id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    // EMPTY TRASH
    if ($action === 'empty_trash') {
        $stmt = $pdo->prepare("DELETE FROM alerts WHERE status = 'trashed' AND user_id = ?");
        if ($stmt->execute([$user_id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    // CLEAR COMPLETED
    if ($action === 'clear_completed') {
        $stmt = $pdo->prepare("DELETE FROM alerts WHERE status = 'completed' AND user_id = ?");
        if ($stmt->execute([$user_id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    // RESET ALL ALERTS & NOTES
    if ($action === 'reset_all') {
        $stmt1 = $pdo->prepare("DELETE FROM alerts WHERE user_id = ?");
        $stmt1->execute([$user_id]);
        $stmt2 = $pdo->prepare("DELETE FROM notes WHERE user_id = ?");
        $stmt2->execute([$user_id]);
        echo json_encode(['success' => true]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>
