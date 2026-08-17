<?php
require_once '../includes/admin_auth.php';
require_once '../includes/db.php';
include '../includes/header.php';
include 'includes/sidebar.php';
include '../includes/topbar.php';

// Fetch Admin Stats
$userCountStmt = $pdo->query("SELECT COUNT(*) FROM users");
$totalUsers = $userCountStmt->fetchColumn();

$alertCountStmt = $pdo->query("SELECT COUNT(*) FROM alerts");
$totalAlerts = $alertCountStmt->fetchColumn();

$activeAlertsStmt = $pdo->query("SELECT COUNT(*) FROM alerts WHERE status = 'active'");
$activeAlerts = $activeAlertsStmt->fetchColumn();

$completedAlertsStmt = $pdo->query("SELECT COUNT(*) FROM alerts WHERE status = 'completed'");
$completedAlerts = $completedAlertsStmt->fetchColumn();
?>
      <section class="view active" id="view-admin-dashboard">
        <div class="hero-row">
          <div>
            <h1>Admin Dashboard</h1>
            <p class="subtle">System Overview and Statistics</p>
          </div>
        </div>

        <div class="stats-grid">
          <article class="stat-card">
            <span class="stat-label">Total Users</span>
            <strong><?= $totalUsers ?></strong>
            <small>registered</small>
          </article>
          <article class="stat-card">
            <span class="stat-label">Total Alerts</span>
            <strong><?= $totalAlerts ?></strong>
            <small>system-wide</small>
          </article>
          <article class="stat-card">
            <span class="stat-label">Active Alerts</span>
            <strong style="color:var(--primary);"><?= $activeAlerts ?></strong>
            <small>pending</small>
          </article>
          <article class="stat-card">
            <span class="stat-label">Completed</span>
            <strong><?= $completedAlerts ?></strong>
            <small>alerts</small>
          </article>
        </div>
      </section>
<?php include '../includes/modal.php'; ?>
<?php include '../includes/footer.php'; ?>
