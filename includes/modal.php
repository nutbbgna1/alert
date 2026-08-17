<?php
$user_id = $_SESSION['user_id'] ?? 0;
$catStmt2 = $pdo->prepare("SELECT * FROM categories WHERE user_id = ? ORDER BY id");
$catStmt2->execute([$user_id]);
$modalCats = $catStmt2->fetchAll();
?>
<!-- Create Alert Modal -->
<div id="alertModal" class="modal-overlay" style="display:none;">
  <div class="modal-box">
    <div class="modal-head">
      <h2><?= __t('modal_create_title') ?></h2>
      <button class="modal-close-btn" id="closeAlertModal">&times;</button>
    </div>
    <form id="alertForm" class="modal-form">
      <label><?= __t('modal_field_title') ?> *
        <input type="text" id="alertTitle" name="title" placeholder="<?= __t('modal_placeholder_title') ?>" required autocomplete="off">
      </label>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <label><?= __t('modal_field_date') ?> *
          <input type="date" id="alertDate" name="alert_date" required value="<?= date('Y-m-d') ?>">
        </label>
        <label><?= __t('modal_field_category') ?>
          <select id="alertCategory" name="category_id">
            <option value=""><?= __t('modal_none_category') ?></option>
            <?php foreach($modalCats as $c): ?>
              <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <label><?= __t('modal_field_start_time') ?>
          <input type="time" id="alertTime" name="alert_time" placeholder="--:--">
        </label>
        <label><?= __t('modal_field_end_time') ?>
          <input type="time" id="alertEndTime" name="end_time" placeholder="--:--">
        </label>
      </div>
      <div style="display:grid;grid-template-columns:1fr;gap:16px;">
        <label><?= __t('modal_field_priority') ?>
          <select id="alertPriority" name="priority">
            <option value="low"><?= __t('priority_low') ?></option>
            <option value="medium" selected><?= __t('priority_medium') ?></option>
            <option value="high"><?= __t('priority_high') ?></option>
          </select>
        </label>
      </div>
      <label><?= __t('modal_field_desc') ?>
        <textarea id="alertDesc" name="description" rows="3" placeholder="<?= __t('modal_placeholder_desc') ?>"></textarea>
      </label>
      <div class="modal-footer">
        <button type="button" id="cancelAlertModal" class="outline-btn"><?= __t('btn_cancel') ?></button>
        <button type="submit" class="primary-btn" id="submitAlertBtn">
          <span id="submitAlertBtnText"><?= __t('btn_create') ?></span>
        </button>
      </div>
    </form>
  </div>
</div>
<script>
  // Pass translation strings to JS
  window._lang = {
    creating: "<?= __r('btn_creating') ?>",
    create:   "<?= __r('btn_create') ?>",
  };
</script>
