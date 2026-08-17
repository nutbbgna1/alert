<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

$today = date('Y-m-d');

// Filters
$filter_priority = $_GET['priority'] ?? '';
$filter_category = $_GET['category'] ?? '';

$user_id = $_SESSION['user_id'];

$sql = "SELECT a.*, c.name as category_name, c.color as category_color 
        FROM alerts a 
        LEFT JOIN categories c ON a.category_id = c.id 
        WHERE a.alert_date = ? AND a.status = 'active' AND a.user_id = ?";
$params = [$today, $user_id];

if ($filter_priority) { $sql .= " AND a.priority = ?"; $params[] = $filter_priority; }
if ($filter_category) { $sql .= " AND a.category_id = ?"; $params[] = $filter_category; }
$sql .= " ORDER BY a.alert_time ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$alerts = $stmt->fetchAll();

$catStmt = $pdo->prepare("SELECT * FROM categories WHERE user_id = ?");
$catStmt->execute([$user_id]);
$categories = $catStmt->fetchAll();

function formatTime($t) { return date('H:i', strtotime($t)); }
?>
  <section class="view active" id="view-today">
    <div class="hero-row">
      <div>
        <h1><?= __t('today_title') ?></h1>
        <p class="subtle"><?= date('l, F j, Y') ?></p>
      </div>
      <button class="primary-btn" data-open-modal>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        <?= __t('create_alert') ?>
      </button>
    </div>

    <!-- Filters -->
    <div class="filter-bar">
      <a href="today.php" class="filter-chip <?= (!$filter_priority && !$filter_category) ? 'active' : '' ?>">All</a>
      <a href="today.php?priority=high" class="filter-chip priority-high <?= $filter_priority === 'high' ? 'active' : '' ?>">High</a>
      <a href="today.php?priority=medium" class="filter-chip priority-medium <?= $filter_priority === 'medium' ? 'active' : '' ?>">Medium</a>
      <a href="today.php?priority=low" class="filter-chip priority-low <?= $filter_priority === 'low' ? 'active' : '' ?>">Low</a>
      <span class="filter-sep">|</span>
      <?php foreach($categories as $cat): ?>
        <a href="today.php?category=<?= $cat['id'] ?>" class="filter-chip <?= $filter_category == $cat['id'] ? 'active' : '' ?>">
          <span class="dot <?= $cat['color'] ?>"></span><?= htmlspecialchars($cat['name']) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Alert List -->
    <div class="panel" style="margin-top:24px;">
      <div class="panel-head">
        <h2><?= __t('todays_alerts') ?> <span class="muted-dot">•</span> <span class="muted-text"><?= __t('today_items', ['count' => count($alerts)]) ?></span></h2>
      </div>
      <div class="task-list">
        <?php if (count($alerts) > 0): ?>
          <?php foreach($alerts as $alert): ?>
            <div class="task-row" id="row-<?= $alert['id'] ?>">
              <button class="check" data-complete="<?= $alert['id'] ?>"></button>
              <div class="task-time"><?= formatTime($alert['alert_time']) ?></div>
              <div class="task-content">
                <div class="task-title"><?= htmlspecialchars($alert['title']) ?></div>
                <?php if($alert['description']): ?>
                  <div class="task-desc"><?= htmlspecialchars($alert['description']) ?></div>
                <?php endif; ?>
              </div>
              <?php if($alert['category_name']): ?>
                <div class="tag <?= htmlspecialchars($alert['category_color']) ?>"><?= htmlspecialchars($alert['category_name']) ?></div>
              <?php endif; ?>
              <div class="priority <?= $alert['priority'] ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="8"></circle></svg>
                <?= ucfirst($alert['priority']) ?>
              </div>
              <button class="row-action trash-btn" data-trash="<?= $alert['id'] ?>" title="Move to trash">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
              </button>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="4" ry="4"></rect></svg>
            <p style="margin-top:12px;"><?= __t('no_alerts_today') ?></p>
            <button class="primary-btn" style="margin-top:16px;" data-open-modal><?= __t('btn_create_one') ?></button>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

<?php include '../includes/modal.php'; ?>
<?php include '../includes/footer.php'; ?>
