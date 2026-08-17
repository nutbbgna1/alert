<?php
require_once '../includes/admin_auth.php';
require_once '../includes/db.php';
include '../includes/header.php';
include 'includes/sidebar.php';
include '../includes/topbar.php';

$stmt = $pdo->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM alerts WHERE user_id = u.id) as total_alerts 
    FROM users u 
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();
?>
      <section class="view active" id="view-admin-users">
        <div class="hero-row">
          <div>
            <h1>User Management</h1>
            <p class="subtle">Manage registered users and roles</p>
          </div>
        </div>

        <?php if(isset($_SESSION['msg'])): ?>
          <div class="toast-inline success" style="margin-bottom:16px;">✓ <?= htmlspecialchars($_SESSION['msg']) ?></div>
          <?php unset($_SESSION['msg']); ?>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
          <div class="toast-inline error" style="margin-bottom:16px; background:#FFEBEE; color:#E53935;">⚠ <?= htmlspecialchars($_SESSION['error']) ?></div>
          <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="panel large">
          <table style="width: 100%; text-align: left; border-collapse: collapse;">
            <thead>
              <tr style="border-bottom: 2px solid var(--border-color);">
                <th style="padding: 12px 8px;">ID</th>
                <th style="padding: 12px 8px;">Username</th>
                <th style="padding: 12px 8px;">Name (Nickname)</th>
                <th style="padding: 12px 8px;">Dept</th>
                <th style="padding: 12px 8px;">Role</th>
                <th style="padding: 12px 8px;">Total Alerts</th>
                <th style="padding: 12px 8px;">Registered At</th>
                <th style="padding: 12px 8px; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($users as $user): ?>
                <tr style="border-bottom: 1px solid var(--border-color);">
                  <td style="padding: 12px 8px;"><?= $user['id'] ?></td>
                  <td style="padding: 12px 8px;"><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                  <td style="padding: 12px 8px;">
                    <?= htmlspecialchars($user['full_name']) ?> 
                    <?php if($user['nickname']) echo "(".htmlspecialchars($user['nickname']).")"; ?>
                  </td>
                  <td style="padding: 12px 8px;"><?= htmlspecialchars($user['department']) ?></td>
                  <td style="padding: 12px 8px;">
                    <span class="tag <?= $user['role'] === 'admin' ? 'study' : 'personal' ?>">
                      <?= ucfirst($user['role']) ?>
                    </span>
                  </td>
                  <td style="padding: 12px 8px;"><?= $user['total_alerts'] ?></td>
                  <td style="padding: 12px 8px;"><?= date('M j, Y H:i', strtotime($user['created_at'])) ?></td>
                  <td style="padding: 12px 8px; text-align: right; display: flex; gap: 8px; justify-content: flex-end;">
                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                      <form action="actions/user_action.php" method="POST" style="margin:0;">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <input type="hidden" name="action" value="toggle_role">
                        <button type="submit" class="outline-btn" style="font-size: 13px; padding: 6px 12px;">
                          <?= $user['role'] === 'admin' ? 'Demote' : 'Make Admin' ?>
                        </button>
                      </form>
                      <form action="actions/user_action.php" method="POST" style="margin:0;" onsubmit="return confirm('Are you sure you want to delete this user? All their data will be lost.');">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="outline-btn danger-btn" style="font-size: 13px; padding: 6px 12px; border-color: #EF4444; color: #EF4444;">Delete</button>
                      </form>
                    <?php else: ?>
                      <span class="subtle" style="font-size: 13px;">(You)</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

<?php include '../includes/modal.php'; ?>
<?php include '../includes/footer.php'; ?>
