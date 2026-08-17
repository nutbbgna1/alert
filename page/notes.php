<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

$search = trim($_GET['q'] ?? '');

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM notes WHERE user_id = ?";
$params = [$user_id];
if ($search) {
    $sql .= " AND (title LIKE ? OR body LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$notes = $stmt->fetchAll();
?>
  <section class="view active" id="view-notes">
    <div class="hero-row">
      <div>
        <h1><?= __t('notes_title') ?></h1>
        <p class="subtle"><?= count($notes) ?> note<?= count($notes) !== 1 ? 's' : '' ?></p>
      </div>
      <button class="primary-btn" id="openNoteModal">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        New Note
      </button>
    </div>

    <!-- Search -->
    <form method="GET" class="search-inline-wrap">
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search notes..." class="search-inline-input">
      <button type="submit" class="primary-btn">Search</button>
      <?php if($search): ?><a href="notes.php" class="outline-btn" style="margin-left:8px;">Clear</a><?php endif; ?>
    </form>

    <!-- Notes Grid -->
    <div class="notes-grid" style="margin-top:24px;">
      <?php if(count($notes) > 0): ?>
        <?php foreach($notes as $note): ?>
          <div class="note-card" id="note-<?= $note['id'] ?>">
            <div class="note-card-head">
              <strong><?= htmlspecialchars($note['title']) ?></strong>
              <span class="note-date-badge"><?= htmlspecialchars($note['date']) ?></span>
            </div>
            <p class="note-card-body"><?= nl2br(htmlspecialchars($note['body'])) ?></p>
            <div class="note-card-footer">
              <button class="row-action delete-note-btn" data-note-id="<?= $note['id'] ?>" title="Delete note">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                <?= __t('delete') ?>
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state" style="grid-column:1/-1;">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="8" y1="6" x2="16" y2="6"></line><line x1="8" y1="10" x2="16" y2="10"></line><line x1="8" y1="14" x2="16" y2="14"></line></svg>
          <p style="margin-top:12px;"><?= __t('no_notes') ?></p>
          <button class="primary-btn" style="margin-top:16px;" id="openNoteModal2">+ <?= __t('create_note') ?></button>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Note Modal -->
  <div id="noteModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
      <div class="modal-head">
        <h2>New Note</h2>
        <button id="closeNoteModal" class="modal-close-btn">&times;</button>
      </div>
      <form id="noteForm" class="modal-form">
        <label>Title *
          <input type="text" id="noteTitle" name="title" placeholder="Note title" required>
        </label>
        <label>Content
          <textarea id="noteBody" name="body" rows="6" placeholder="Write your note here..."></textarea>
        </label>
        <div class="modal-footer">
          <button type="button" id="closeNoteModal2" class="outline-btn">Cancel</button>
          <button type="submit" class="primary-btn">Save Note</button>
        </div>
      </form>
    </div>
  </div>

<?php include '../includes/modal.php'; ?>
<?php include '../includes/footer.php'; ?>
