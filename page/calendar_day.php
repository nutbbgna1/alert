<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

$dateParam = $_GET['date'] ?? date('Y-m-d');
$dateTs = strtotime($dateParam);
if (!$dateTs) {
    $dateTs = time();
    $dateParam = date('Y-m-d');
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// Shared helpers
function formatAlertTime($start, $end = null) {
    if (!$start && !$end) return '—';
    if ($start && $end) return date('H:i', strtotime($start)) . ' - ' . date('H:i', strtotime($end));
    return $start ? date('H:i', strtotime($start)) : date('H:i', strtotime($end));
}

// -- Day View Variables --
$prevDate = date('Y-m-d', strtotime('-1 day', $dateTs));
$nextDate = date('Y-m-d', strtotime('+1 day', $dateTs));
$title = date('l, F j, Y', $dateTs);

$startDate = $dateParam;
$endDate = $dateParam;

// Fetch Alerts for the range
$sql = "SELECT a.*, c.name as category_name, c.color as category_color 
        FROM alerts a 
        LEFT JOIN categories c ON a.category_id = c.id 
        WHERE a.alert_date >= ? AND a.alert_date <= ? AND a.status != 'trashed' AND a.user_id = ? 
        ORDER BY a.alert_date ASC, a.alert_time ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$startDate, $endDate, $user_id]);
$alerts = $stmt->fetchAll();
?>
  <section class="view active" id="view-calendar">
    <div class="hero-row">
      <div>
        <h1>Calendar</h1>
        <p class="subtle"><?= $title ?></p>
      </div>
      <div style="display:flex; gap:16px; align-items:center;">
        <div class="view-toggles" style="display:flex; background:#F3F4F6; padding:4px; border-radius:8px; gap:4px;">
          <a href="calendar.php?date=<?= $dateParam ?>" class="toggle-btn"><?= __t('month') ?></a>
          <a href="calendar_week.php?date=<?= $dateParam ?>" class="toggle-btn"><?= __t('week') ?></a>
          <a href="calendar_day.php?date=<?= $dateParam ?>" class="toggle-btn active"><?= __t('day') ?></a>
        </div>
        <button class="primary-btn" data-open-modal>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          <?= __t('create_alert') ?>
        </button>
      </div>
    </div>

    <div class="cal-nav">
      <a href="calendar_day.php?date=<?= $prevDate ?>" class="icon-btn cal-arrow">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
      </a>
      <h2 class="cal-month-title"><?= $title ?></h2>
      <a href="calendar_day.php?date=<?= $nextDate ?>" class="icon-btn cal-arrow">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
      </a>
    </div>

    <!-- Render Day View -->
    <div class="panel" style="margin-top:24px;">
      <div class="panel-head">
        <h2><?= __t('events_for') ?> <?= date('l', $dateTs) ?> <span class="muted-dot">•</span> <span class="muted-text"><?= count($alerts) ?> <?= __t('items') ?></span></h2>
      </div>
      <div class="task-list">
        <?php if (count($alerts) > 0): ?>
          <?php foreach($alerts as $alert): ?>
            <div class="task-row" id="row-<?= $alert['id'] ?>">
              <button class="check <?= $alert['status'] === 'completed' ? 'checked' : '' ?>" data-complete="<?= $alert['id'] ?>">
                <?= $alert['status'] === 'completed' ? '✓' : '' ?>
              </button>
              <div class="task-time" style="white-space:nowrap;"><?= formatAlertTime($alert['alert_time'], $alert['end_time'] ?? null) ?></div>
              <div class="task-content">
                <div class="task-title" <?= $alert['status'] === 'completed' ? 'style="text-decoration:line-through;color:var(--text-muted);"' : '' ?>><?= htmlspecialchars($alert['title']) ?></div>
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
              <button class="row-action trash-btn" data-trash="<?= $alert['id'] ?>" title="<?= __t('move_to_trash') ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
              </button>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="4" ry="4"></rect></svg>
            <p style="margin-top:12px;"><?= __t('no_alerts_today') ?></p>
            <button class="primary-btn" style="margin-top:16px;" data-open-modal><?= __t('create_one') ?></button>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </section>

<?php include '../includes/modal.php'; ?>
<?php include '../includes/footer.php'; ?>
