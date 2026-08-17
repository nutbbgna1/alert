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

// -- Month View Variables --
$year = (int)date('Y', $dateTs);
$month = (int)date('m', $dateTs);
$daysInMonth = (int)date('t', $dateTs);
$firstDay = (int)date('w', mktime(0,0,0,$month,1,$year)); // 0=Sun
$prevDays = (int)date('t', mktime(0,0,0,$month-1,1,$year));

$prevDate = date('Y-m-d', strtotime('-1 month', mktime(0,0,0,$month,1,$year)));
$nextDate = date('Y-m-d', strtotime('+1 month', mktime(0,0,0,$month,1,$year)));
$title = date('F Y', $dateTs);

$startDate = sprintf('%04d-%02d-01', $year, $month);
$endDate = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

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
          <a href="calendar.php?date=<?= $dateParam ?>" class="toggle-btn active"><?= __t('month') ?></a>
          <a href="calendar_week.php?date=<?= $dateParam ?>" class="toggle-btn"><?= __t('week') ?></a>
          <a href="calendar_day.php?date=<?= $dateParam ?>" class="toggle-btn"><?= __t('day') ?></a>
        </div>
        <button class="primary-btn" data-open-modal>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          <?= __t('create_alert') ?>
        </button>
      </div>
    </div>

    <div class="cal-nav">
      <a href="calendar.php?date=<?= $prevDate ?>" class="icon-btn cal-arrow">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
      </a>
      <h2 class="cal-month-title"><?= $title ?></h2>
      <a href="calendar.php?date=<?= $nextDate ?>" class="icon-btn cal-arrow">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
      </a>
    </div>

    <!-- Render Month View -->
    <div class="full-calendar">
      <?php foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d): ?>
        <div class="fcal-head"><?= $d ?></div>
      <?php endforeach; ?>

      <!-- Prev month trailing days -->
      <?php for($i = $firstDay - 1; $i >= 0; $i--): ?>
        <div class="fcal-cell faded"><span class="fcal-daynum"><?= $prevDays - $i ?></span></div>
      <?php endfor; ?>

      <!-- Current month days -->
      <?php for($d = 1; $d <= $daysInMonth; $d++):
        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
        $isToday = ($dateStr === $today);
        $alerts  = $byDate[$dateStr] ?? [];
      ?>
        <div class="fcal-cell <?= $isToday ? 'today' : '' ?>" onclick="window.location.href='calendar_day.php?date=<?= $dateStr ?>'" style="cursor:pointer;">
          <span class="fcal-daynum <?= $isToday ? 'today-num' : '' ?>"><?= $d ?></span>
          <div class="fcal-events">
            <?php foreach(array_slice($alerts, 0, 3) as $a): 
              $timePrefix = $a['alert_time'] ? date('H:i', strtotime($a['alert_time'])) . ' ' : '';
            ?>
              <div class="fcal-event <?= $a['priority'] ?>-event" title="<?= htmlspecialchars($a['title']) ?>">
                <?= $timePrefix ?><?= htmlspecialchars(mb_strimwidth($a['title'], 0, 18, '…')) ?>
              </div>
            <?php endforeach; ?>
            <?php if(count($alerts) > 3): ?>
              <div class="fcal-more">+<?= count($alerts) - 3 ?> more</div>
            <?php endif; ?>
          </div>
        </div>
      <?php endfor; ?>

      <!-- Next month leading days -->
      <?php
        $totalCells = $firstDay + $daysInMonth;
        $remaining  = (ceil($totalCells / 7) * 7) - $totalCells;
        for($i = 1; $i <= $remaining; $i++):
      ?>
        <div class="fcal-cell faded"><span class="fcal-daynum"><?= $i ?></span></div>
      <?php endfor; ?>
    </div>

  </section>

<?php include '../includes/modal.php'; ?>
<?php include '../includes/footer.php'; ?>
