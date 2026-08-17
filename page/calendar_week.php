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
function formatTime($t) { return date('H:i', strtotime($t)); }

// -- Week View Variables --
$prevDate = date('Y-m-d', strtotime('-1 week', $dateTs));
$nextDate = date('Y-m-d', strtotime('+1 week', $dateTs));

// Find sunday of this week
$dayOfWeek = date('w', $dateTs);
$startOfWeekTs = strtotime("-{$dayOfWeek} days", $dateTs);
$endOfWeekTs = strtotime("+" . (6 - $dayOfWeek) . " days", $dateTs);

$startDate = date('Y-m-d', $startOfWeekTs);
$endDate = date('Y-m-d', $endOfWeekTs);

// Check if week spans multiple months
if (date('m', $startOfWeekTs) === date('m', $endOfWeekTs)) {
    $title = date('M j', $startOfWeekTs) . ' - ' . date('j, Y', $endOfWeekTs);
} else {
    $title = date('M j', $startOfWeekTs) . ' - ' . date('M j, Y', $endOfWeekTs);
}

// Fetch Alerts for the range
$sql = "SELECT a.*, c.name as category_name, c.color as category_color 
        FROM alerts a 
        LEFT JOIN categories c ON a.category_id = c.id 
        WHERE a.alert_date >= ? AND a.alert_date <= ? AND a.status != 'trashed' AND a.user_id = ? 
        ORDER BY a.alert_date ASC, a.alert_time ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$startDate, $endDate, $user_id]);
$allAlerts = $stmt->fetchAll();

// Group alerts by day string 'Y-m-d'
$byDate = [];
foreach($allAlerts as $a) {
    $byDate[$a['alert_date']][] = $a;
}
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
          <a href="calendar_week.php?date=<?= $dateParam ?>" class="toggle-btn active"><?= __t('week') ?></a>
          <a href="calendar_day.php?date=<?= $dateParam ?>" class="toggle-btn"><?= __t('day') ?></a>
        </div>
        <button class="primary-btn" data-open-modal>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          <?= __t('create_alert') ?>
        </button>
      </div>
    </div>

    <div class="cal-nav">
      <a href="calendar_week.php?date=<?= $prevDate ?>" class="icon-btn cal-arrow">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
      </a>
      <h2 class="cal-month-title"><?= $title ?></h2>
      <a href="calendar_week.php?date=<?= $nextDate ?>" class="icon-btn cal-arrow">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
      </a>
    </div>

    <!-- Render Week View -->
    <div class="full-calendar week-calendar">
      <?php foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d): ?>
        <div class="fcal-head"><?= $d ?></div>
      <?php endforeach; ?>

      <?php for($i = 0; $i < 7; $i++): 
          $ts = strtotime("+$i days", $startOfWeekTs);
          $dateStr = date('Y-m-d', $ts);
          $isToday = ($dateStr === $today);
          $alerts  = $byDate[$dateStr] ?? [];
      ?>
        <div class="fcal-cell <?= $isToday ? 'today' : '' ?>" onclick="window.location.href='calendar_day.php?date=<?= $dateStr ?>'" style="cursor:pointer;">
          <span class="fcal-daynum <?= $isToday ? 'today-num' : '' ?>"><?= date('j', $ts) ?></span>
          <div class="fcal-events" style="margin-top:12px; gap:8px;">
            <?php foreach($alerts as $a): ?>
              <div class="fcal-event <?= $a['priority'] ?>-event" title="<?= htmlspecialchars($a['title']) ?>" style="padding:6px; font-size:12px; white-space:normal;">
                <strong><?= date('H:i', strtotime($a['alert_time'])) ?></strong><br>
                <?= htmlspecialchars($a['title']) ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endfor; ?>
    </div>

  </section>

<?php include '../includes/modal.php'; ?>
<?php include '../includes/footer.php'; ?>
