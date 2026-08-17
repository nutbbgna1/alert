<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

$user_id = $_SESSION['user_id'];

$catStmt = $pdo->prepare("SELECT c.*, COUNT(a.id) as alert_count 
    FROM categories c 
    LEFT JOIN alerts a ON a.category_id = c.id AND a.status = 'active'
    WHERE c.user_id = ?
    GROUP BY c.id ORDER BY c.id");
$catStmt->execute([$user_id]);
$categories = $catStmt->fetchAll();

$colorMap = [
    'work'     => ['bg' => '#E0F2F1', 'text' => '#00897B'],
    'study'    => ['bg' => '#E8F5E9', 'text' => '#43A047'],
    'personal' => ['bg' => '#E8EAF6', 'text' => '#3F51B5'],
    'finance'  => ['bg' => '#FFF3E0', 'text' => '#FB8C00'],
    'health'   => ['bg' => '#FFEBEE', 'text' => '#E53935'],
    'other'    => ['bg' => '#F5F5F5', 'text' => '#757575'],
];
?>
  <section class="view active" id="view-categories">
    <div class="hero-row">
      <div>
        <h1>Categories</h1>
        <p class="subtle">Manage your alert categories</p>
      </div>
      <button class="primary-btn" id="openCategoryModal">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        New Category
      </button>
    </div>

    <div class="categories-grid">
      <?php foreach($categories as $cat):
        $color = $colorMap[$cat['color']] ?? ['bg'=>'#F5F5F5','text'=>'#757575'];
      ?>
        <div class="cat-card" style="border-left: 4px solid <?= $color['text'] ?>;">
          <div class="cat-card-head">
            <span class="cat-icon" style="background:<?= $color['bg'] ?>;color:<?= $color['text'] ?>;">
              <span class="dot <?= $cat['color'] ?>" style="width:12px;height:12px;"></span>
            </span>
            <div>
              <strong class="cat-name"><?= htmlspecialchars($cat['name']) ?></strong>
              <p class="subtle"><?= $cat['alert_count'] ?> active alert<?= $cat['alert_count'] != 1 ? 's' : '' ?></p>
            </div>
          </div>
          <div style="display:flex; justify-content:space-between; align-items:center; margin-top: 16px;">
            <a href="alerts.php?category=<?= $cat['id'] ?>" class="cat-view-link" style="margin-top:0;">View alerts →</a>
            <button class="delete-cat-btn" data-id="<?= $cat['id'] ?>" style="background:none;border:none;color:var(--danger, #F44336);cursor:pointer;font-weight:600;font-size:13px;">Delete</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Alerts per category breakdown -->
    <div class="panel" style="margin-top:32px;">
      <div class="panel-head"><h2>Alert Breakdown by Category</h2></div>
      <div style="display:flex;flex-direction:column;gap:16px;padding-top:8px;">
        <?php
          $total = array_sum(array_column($categories, 'alert_count')) ?: 1;
          foreach($categories as $cat):
            $color = $colorMap[$cat['color']] ?? ['bg'=>'#F5F5F5','text'=>'#757575'];
            $pct = round(($cat['alert_count'] / $total) * 100);
        ?>
          <div>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
              <span style="display:flex;align-items:center;gap:8px;font-weight:500;">
                <span class="dot <?= $cat['color'] ?>"></span><?= htmlspecialchars($cat['name']) ?>
              </span>
              <span style="color:var(--text-muted);"><?= $cat['alert_count'] ?> alerts (<?= $pct ?>%)</span>
            </div>
            <div style="background:#F3F4F6;border-radius:99px;height:8px;overflow:hidden;">
              <div style="width:<?= $pct ?>%;background:<?= $color['text'] ?>;height:100%;border-radius:99px;transition:width 0.6s ease;"></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Category Modal -->
  <div id="categoryModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
      <div class="modal-head">
        <h2>New Category</h2>
        <button id="closeCategoryModal" class="modal-close-btn">&times;</button>
      </div>
      <form id="categoryForm" class="modal-form">
        <label>Name *
          <input type="text" id="catName" name="name" placeholder="Category name" required autocomplete="off">
        </label>
        <label>Color
          <select id="catColor" name="color">
            <option value="work">Work (Teal)</option>
            <option value="study">Study (Green)</option>
            <option value="personal">Personal (Blue)</option>
            <option value="finance">Finance (Orange)</option>
            <option value="health">Health (Red)</option>
            <option value="other">Other (Gray)</option>
          </select>
        </label>
        <div class="modal-footer">
          <button type="button" id="cancelCategoryModal" class="outline-btn">Cancel</button>
          <button type="submit" class="primary-btn">Save Category</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    const catModal = document.querySelector('#categoryModal');
    document.querySelector('#openCategoryModal')?.addEventListener('click', () => {
      catModal.style.display = 'flex';
      document.querySelector('#catName').focus();
    });
    document.querySelector('#closeCategoryModal')?.addEventListener('click', () => catModal.style.display = 'none');
    document.querySelector('#cancelCategoryModal')?.addEventListener('click', () => catModal.style.display = 'none');
    catModal?.addEventListener('click', e => { if (e.target === catModal) catModal.style.display = 'none'; });

    document.querySelector('#categoryForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new URLSearchParams(new FormData(this));
      formData.append('action', 'create');
      
      fetch('../actions/category_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: formData.toString()
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          window.location.reload();
        } else {
          alert(data.error || 'Error creating category');
        }
      });
    });

    document.querySelectorAll('.delete-cat-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        if (confirm('Are you sure you want to delete this category? All alerts in this category will become uncategorized.')) {
          const id = this.dataset.id;
          fetch('../actions/category_action.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=delete&id=${id}`
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              window.location.reload();
            } else {
              alert(data.error || 'Failed to delete category');
            }
          })
          .catch(err => {
            alert('An error occurred');
            console.error(err);
          });
        }
      });
    });
  </script>

<?php include '../includes/modal.php'; ?>
<?php include '../includes/footer.php'; ?>
