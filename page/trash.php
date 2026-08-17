<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

$user_id = $_SESSION['user_id'];

$trashedStmt = $pdo->prepare("SELECT a.*, c.name as category_name, c.color as category_color 
    FROM alerts a 
    LEFT JOIN categories c ON a.category_id = c.id 
    WHERE a.status = 'trashed' AND a.user_id = ? 
    ORDER BY a.id DESC");
$trashedStmt->execute([$user_id]);
$trashed = $trashedStmt->fetchAll();
?>
  <section class="view active" id="view-trash">
    <div class="hero-row">
      <div>
        <h1>Trash</h1>
        <p class="subtle"><?= count($trashed) ?> item<?= count($trashed) !== 1 ? 's' : '' ?> in trash</p>
      </div>
      <?php if(count($trashed) > 0): ?>
        <button class="outline-btn danger-btn" id="emptyTrashBtn">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
          Empty Trash
        </button>
      <?php endif; ?>
    </div>

    <div class="panel" style="margin-top:24px;">
      <?php if(count($trashed) > 0): ?>
        <div class="task-list">
          <?php foreach($trashed as $alert): ?>
            <div class="task-row trashed-row" id="trash-row-<?= $alert['id'] ?>">
              <div class="task-content" style="opacity:0.6;">
                <div class="task-title" style="text-decoration:line-through;"><?= htmlspecialchars($alert['title']) ?></div>
                <?php
                  $timeDesc = '';
                  if ($alert['alert_time'] && $alert['end_time']) {
                    $timeDesc = ' at ' . date('H:i', strtotime($alert['alert_time'])) . ' - ' . date('H:i', strtotime($alert['end_time']));
                  } elseif ($alert['alert_time']) {
                    $timeDesc = ' at ' . date('H:i', strtotime($alert['alert_time']));
                  }
                ?>
                <div class="task-desc"><?= date('D, M j', strtotime($alert['alert_date'])) ?><?= $timeDesc ?></div>
              </div>
              <?php if($alert['category_name']): ?>
                <span class="tag <?= htmlspecialchars($alert['category_color']) ?>"><?= htmlspecialchars($alert['category_name']) ?></span>
              <?php endif; ?>
              <div style="display:flex;gap:8px;margin-left:auto;">
                <button class="outline-btn restore-btn" data-restore="<?= $alert['id'] ?>" style="padding:6px 12px;font-size:12px;">Restore</button>
                <button class="row-action danger-row-btn delete-forever-btn" data-delete="<?= $alert['id'] ?>" title="Delete forever">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
          <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            <p style="margin-top:12px;"><?= __t('trash_empty_msg') ?></p>
          </div>
      <?php endif; ?>
    </div>
  </section>

<?php include '../includes/modal.php'; ?>
<?php include '../includes/footer.php'; ?>
