<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

// Filters
$filter_priority = $_GET['priority'] ?? '';
$filter_category = intval($_GET['category'] ?? 0);
$filter_status   = $_GET['status'] ?? 'active';
$search          = trim($_GET['q'] ?? '');

$user_id = $_SESSION['user_id'];

$sql = "SELECT a.*, c.name as category_name, c.color as category_color 
        FROM alerts a 
        LEFT JOIN categories c ON a.category_id = c.id 
        WHERE a.status = ? AND a.user_id = ?";
$params = [$filter_status === 'completed' ? 'completed' : 'active', $user_id];

if ($filter_priority) { $sql .= " AND a.priority = ?"; $params[] = $filter_priority; }
if ($filter_category) { $sql .= " AND a.category_id = ?"; $params[] = $filter_category; }
if ($search)          { $sql .= " AND a.title LIKE ?"; $params[] = "%$search%"; }
$sql .= " ORDER BY a.alert_date ASC, a.alert_time ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$alerts = $stmt->fetchAll();

$catStmt = $pdo->prepare("SELECT * FROM categories WHERE user_id = ?");
$catStmt->execute([$user_id]);
$categories = $catStmt->fetchAll();

function formatTime($t) { return date('H:i', strtotime($t)); }
function formatDate($d) { return date('D, M j', strtotime($d)); }
?>
  <section class="view active" id="view-alerts">
    <div class="hero-row">
      <div>
        <h1>All Alerts</h1>
        <p class="subtle"><?= __t('alerts_found', ['count' => count($alerts)]) ?></p>
      </div>
      <button class="primary-btn" data-open-modal>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          <?= __t('create_alert') ?>
      </button>
    </div>

    <!-- Search -->
    <form method="GET" class="search-inline-wrap">
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search alerts..." class="search-inline-input">
      <?php if ($filter_priority): ?><input type="hidden" name="priority" value="<?= htmlspecialchars($filter_priority) ?>"><?php endif; ?>
      <?php if ($filter_category): ?><input type="hidden" name="category" value="<?= $filter_category ?>"><?php endif; ?>
      <?php if ($filter_status): ?><input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>"><?php endif; ?>
      <button type="submit" class="primary-btn">Search</button>
    </form>

    <!-- Filter bar -->
    <div class="filter-bar" style="margin-top:16px;">
      <a href="alerts.php" class="filter-chip <?= (!$filter_priority && !$filter_category && $filter_status === 'active') ? 'active' : '' ?>"><?= __t('filter_all_active') ?></a>
      <a href="alerts.php?status=completed" class="filter-chip <?= $filter_status === 'completed' ? 'active' : '' ?>"><?= __t('filter_completed') ?></a>
      <span class="filter-sep">|</span>
      <a href="alerts.php?priority=high" class="filter-chip <?= $filter_priority === 'high' ? 'active' : '' ?>">🔴 High</a>
      <a href="alerts.php?priority=medium" class="filter-chip <?= $filter_priority === 'medium' ? 'active' : '' ?>">🟠 Medium</a>
      <a href="alerts.php?priority=low" class="filter-chip <?= $filter_priority === 'low' ? 'active' : '' ?>">⚪ Low</a>
      <span class="filter-sep">|</span>
      <?php foreach($categories as $cat): ?>
        <a href="alerts.php?category=<?= $cat['id'] ?>" class="filter-chip <?= $filter_category == $cat['id'] ? 'active' : '' ?>">
          <span class="dot <?= $cat['color'] ?>"></span><?= htmlspecialchars($cat['name']) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Alert Table -->
    <div class="panel" style="margin-top:24px;">
      <?php if(count($alerts) > 0): ?>
        <div class="alert-table">
          <div class="alert-table-head">
            <span>Title</span>
            <span>Date</span>
            <span>Time</span>
            <span>Category</span>
            <span>Priority</span>
            <span>Actions</span>
          </div>
          <?php foreach($alerts as $alert): ?>
            <div class="alert-table-row" id="row-<?= $alert['id'] ?>">
              <span class="alert-title-cell"><?= htmlspecialchars($alert['title']) ?></span>
              <span class="alert-date-cell"><?= formatDate($alert['alert_date']) ?></span>
              <span class="alert-time-cell"><?= formatTime($alert['alert_time']) ?></span>
              <span>
                <?php if($alert['category_name']): ?>
                  <span class="tag <?= htmlspecialchars($alert['category_color']) ?>"><?= htmlspecialchars($alert['category_name']) ?></span>
                <?php else: ?><span class="muted-text">—</span><?php endif; ?>
              </span>
              <span class="priority <?= $alert['priority'] ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="8"></circle></svg>
                <?= ucfirst($alert['priority']) ?>
              </span>
              <span class="row-actions-cell">
                <button class="check-sm" data-complete="<?= $alert['id'] ?>" title="Toggle complete"><?= $alert['status'] === 'completed' ? '✓' : '' ?></button>
                <button class="row-action trash-btn" data-trash="<?= $alert['id'] ?>" title="Trash">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                </button>
              </span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9" stroke-dasharray="0 5"></circle></svg>
          <p style="margin-top:12px;"><?= __t('no_alerts_found') ?></p>
          <button class="primary-btn" style="margin-top:16px;" data-open-modal><?= __t('create_alert') ?></button>
        </div>
      <?php endif; ?>
    </div>
  </section>

<?php include '../includes/modal.php'; ?>
<?php include '../includes/footer.php'; ?>
