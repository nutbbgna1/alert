<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

// Fetch stats
$today = date('Y-m-d');

$user_id = $_SESSION['user_id'];

$todayStmt = $pdo->prepare("SELECT COUNT(*) FROM alerts WHERE alert_date = ? AND status = 'active' AND user_id = ?");
$todayStmt->execute([$today, $user_id]);
$todayCount = $todayStmt->fetchColumn();

$upcomingStmt = $pdo->prepare("SELECT COUNT(*) FROM alerts WHERE alert_date > ? AND status = 'active' AND user_id = ?");
$upcomingStmt->execute([$today, $user_id]);
$upcomingCount = $upcomingStmt->fetchColumn();

$overdueStmt = $pdo->prepare("SELECT COUNT(*) FROM alerts WHERE alert_date < ? AND status = 'active' AND user_id = ?");
$overdueStmt->execute([$today, $user_id]);
$overdueCount = $overdueStmt->fetchColumn();

$completedStmt = $pdo->prepare("SELECT COUNT(*) FROM alerts WHERE status = 'completed' AND user_id = ?");
$completedStmt->execute([$user_id]);
$completedCount = $completedStmt->fetchColumn();

// Fetch today's list
$todayListStmt = $pdo->prepare("
  SELECT a.*, c.name as category_name, c.color as category_color 
  FROM alerts a 
  LEFT JOIN categories c ON a.category_id = c.id 
  WHERE a.alert_date = ? AND a.status = 'active' AND a.user_id = ?
  ORDER BY a.alert_time ASC
");
$todayListStmt->execute([$today, $user_id]);
$todayAlerts = $todayListStmt->fetchAll();

// Fetch upcoming list
$upcomingListStmt = $pdo->prepare("
  SELECT a.*, c.name as category_name, c.color as category_color 
  FROM alerts a 
  LEFT JOIN categories c ON a.category_id = c.id 
  WHERE a.alert_date > ? AND a.status = 'active' AND a.user_id = ?
  ORDER BY a.alert_date ASC, a.alert_time ASC
  LIMIT 4
");
$upcomingListStmt->execute([$today, $user_id]);
$upcomingAlerts = $upcomingListStmt->fetchAll();

// Fetch notes
$notesStmt = $pdo->prepare("SELECT * FROM notes WHERE user_id = ? ORDER BY id ASC LIMIT 3");
$notesStmt->execute([$user_id]);
$notesList = $notesStmt->fetchAll();

function formatAlertTime($start, $end = null) {
    if (!$start && !$end) return '—';
    if ($start && $end) return date('H:i', strtotime($start)) . ' - ' . date('H:i', strtotime($end));
    return $start ? date('H:i', strtotime($start)) : date('H:i', strtotime($end));
}
function formatDate($dateStr) {
    return date('M j', strtotime($dateStr));
}
?>
      <section class="view active" id="view-dashboard">
        <div class="hero-row">
          <div>
            <h1><?= __t('greeting', ['name' => htmlspecialchars($_SESSION['username'])]) ?></h1>
            <p class="subtle"><?= __t('greeting_focus', ['count' => $todayCount]) ?></p>
          </div>
          <button class="primary-btn" data-open-modal><?= __t('create_alert') ?></button>
        </div>

        <div class="stats-grid">
          <article class="stat-card">
            <span class="stat-label"><?= __t('stat_today') ?></span>
            <strong><?= $todayCount ?></strong>
            <small><?= __t('stat_alerts') ?></small>
          </article>
          <article class="stat-card">
            <span class="stat-label"><?= __t('stat_upcoming') ?></span>
            <strong><?= $upcomingCount ?></strong>
            <small><?= __t('stat_alerts') ?></small>
          </article>
          <article class="stat-card danger">
            <span class="stat-label"><?= __t('stat_overdue') ?></span>
            <strong class="text-danger"><?= $overdueCount ?></strong>
            <small><?= __t('stat_alerts') ?></small>
          </article>
          <article class="stat-card">
            <span class="stat-label"><?= __t('stat_completed') ?></span>
            <strong><?= $completedCount ?></strong>
            <small><?= __t('stat_alerts') ?></small>
          </article>
        </div>

        <div class="dashboard-grid">
          <div class="grid-left">
            <section class="panel large">
              <div class="panel-head">
                <div>
                  <h2><?= __t('panel_today') ?> <span class="muted-dot">•</span> <span class="muted-text"><?= date('M j, Y', strtotime($today)) ?></span></h2>
                </div>
                <a href="today.php" class="text-btn"><?= __t('btn_view_all') ?></a>
              </div>
              <div class="task-list">
                <?php if (count($todayAlerts) > 0): ?>
                  <?php foreach($todayAlerts as $alert): ?>
                    <div class="task-row">
                      <button class="check" data-complete="<?= $alert['id'] ?>"></button>
                      <div class="task-time" style="white-space:nowrap;"><?= formatAlertTime($alert['alert_time'], $alert['end_time'] ?? null) ?></div>
                      <div class="task-content">
                        <div class="task-title"><?= htmlspecialchars($alert['title']) ?></div>
                      </div>
                      <?php if($alert['category_name']): ?>
                        <div class="tag <?= htmlspecialchars($alert['category_color']) ?>"><?= htmlspecialchars($alert['category_name']) ?></div>
                      <?php endif; ?>
                      <div class="priority <?= $alert['priority'] ?>">
                         <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg>
                         <?= ucfirst($alert['priority']) ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="empty-state"><?= __t('empty_today') ?></div>
                <?php endif; ?>
              </div>
            </section>

            <section class="panel large" style="margin-top: 24px;">
              <div class="panel-head">
                <div>
                  <h2><?= __t('panel_upcoming') ?></h2>
                </div>
                <a href="alerts.php" class="text-btn"><?= __t('btn_view_all') ?></a>
              </div>
              <div class="upcoming-list">
                <?php if (count($upcomingAlerts) > 0): ?>
                  <?php 
                    $currentDate = '';
                    foreach($upcomingAlerts as $alert): 
                      $alertDate = $alert['alert_date'];
                      if($alertDate !== $currentDate):
                        $currentDate = $alertDate;
                        // Determine if it's tomorrow (relative to our fake $today)
                        $isTomorrow = (date('Y-m-d', strtotime($today . ' +1 day')) === $alertDate);
                        $dateLabel = $isTomorrow ? "Tomorrow" : date('D', strtotime($alertDate));
                  ?>
                    <div class="upcoming-date-header">
                      <span class="up-day"><?= $dateLabel ?></span>
                      <span class="up-date"><?= formatDate($alertDate) ?></span>
                    </div>
                  <?php endif; ?>
                    
                  <div class="task-row no-check">
                    <div class="task-time" style="white-space:nowrap;"><?= formatAlertTime($alert['alert_time'], $alert['end_time'] ?? null) ?></div>
                    <div class="task-content">
                      <div class="task-title"><?= htmlspecialchars($alert['title']) ?></div>
                    </div>
                    <?php if($alert['category_name']): ?>
                      <div class="tag <?= htmlspecialchars($alert['category_color']) ?>"><?= htmlspecialchars($alert['category_name']) ?></div>
                    <?php endif; ?>
                    <div class="priority <?= $alert['priority'] ?>">
                       <?= ucfirst($alert['priority']) ?>
                    </div>
                  </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="empty-state"><?= __t('empty_upcoming') ?></div>
                <?php endif; ?>
              </div>
            </section>
          </div>
          
          <div class="grid-right">
            <section class="panel">
              <div class="panel-head" style="justify-content: space-between;">
                <a href="calendar.php" class="icon-btn" style="text-decoration:none;">‹</a>
                <h2 style="font-size: 14px; margin: 0; font-weight: 600;"><?= date('F Y') ?></h2>
                <a href="calendar.php" class="icon-btn" style="text-decoration:none;">›</a>
              </div>
              <div class="mini-calendar">
                <div class="cal-head">Sun</div><div class="cal-head">Mon</div><div class="cal-head">Tue</div><div class="cal-head">Wed</div><div class="cal-head">Thu</div><div class="cal-head">Fri</div><div class="cal-head">Sat</div>
                <?php
                  $year = (int)date('Y');
                  $month = (int)date('m');
                  $daysInMonth = (int)date('t');
                  $firstDay = (int)date('w', mktime(0,0,0,$month,1,$year));
                  $prevDays = (int)date('t', mktime(0,0,0,$month-1,1,$year));
                  
                  for($i = $firstDay - 1; $i >= 0; $i--) {
                      echo '<div class="cal-day muted">' . ($prevDays - $i) . '</div>';
                  }
                  for($d = 1; $d <= $daysInMonth; $d++) {
                      $isToday = ($d == (int)date('j')) ? 'today' : '';
                      echo '<div class="cal-day ' . $isToday . '">' . $d . '</div>';
                  }
                  $totalCells = $firstDay + $daysInMonth;
                  $remaining = (ceil($totalCells / 7) * 7) - $totalCells;
                  for($i = 1; $i <= $remaining; $i++) {
                      echo '<div class="cal-day muted">' . $i . '</div>';
                  }
                ?>
              </div>
              <div class="calendar-agenda-mini">
                 <div class="agenda-header">
                    <span><?= __t('panel_today') ?> <span class="muted-dot">•</span> <?= date('M j') ?></span>
                    <span class="muted-text"><?= count($todayAlerts) ?> events</span>
                 </div>
                 <?php foreach($todayAlerts as $alert): ?>
                    <div class="agenda-item">
                       <span class="dot <?= htmlspecialchars($alert['category_color']) ?>"></span>
                       <span class="time"><?= formatAlertTime($alert['alert_time'], $alert['end_time'] ?? null) ?></span>
                       <span class="title"><?= htmlspecialchars($alert['title']) ?></span>
                       <span class="tag <?= htmlspecialchars($alert['category_color']) ?>"><?= htmlspecialchars($alert['category_name']) ?></span>
                    </div>
                 <?php endforeach; ?>
              </div>
              <div class="calendar-footer">
                <a href="calendar.php" class="outline-btn">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                  Open Calendar
                </a>
              </div>
            </section>

            <section class="panel" style="margin-top: 24px;">
              <div class="panel-head">
                <h2><?= __t('panel_notes') ?></h2>
                <a href="notes.php" class="text-btn"><?= __t('btn_view_all') ?></a>
              </div>
              <div class="notes-preview">
                <?php foreach($notesList as $note): ?>
                  <div class="note-line">
                    <div class="note-header">
                      <strong><?= htmlspecialchars($note['title']) ?></strong>
                      <span class="note-date"><?= htmlspecialchars($note['date']) ?></span>
                    </div>
                    <div class="note-body"><?= nl2br(htmlspecialchars($note['body'])) ?></div>
                  </div>
                <?php endforeach; ?>
              </div>
            </section>
          </div>
        </div>
      </section>

<?php include '../includes/modal.php'; ?>
<?php include '../includes/footer.php'; ?>
