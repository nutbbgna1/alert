    <main class="main">
      <header class="topbar">
        <div class="search-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          <input id="globalSearch" type="search" placeholder="<?= __t('search_placeholder') ?>" />
        </div>
        <div class="top-actions">
          <!-- Language Switcher -->
          <?php
            $switchLang = ($currentLang === 'th') ? 'en' : 'th';
            $switchLabel = __r('switch_to_lang');
            $switchFlag  = __r('lang_flag');
            $currentPage = basename($_SERVER['PHP_SELF']);
          ?>
          <a href="<?= $currentPage ?>?lang=<?= $switchLang ?>" 
             class="lang-switcher" 
             title="Switch language"
             aria-label="Switch to <?= $switchLabel ?>">
            <span class="lang-flag"><?= $switchFlag ?></span>
            <span class="lang-label"><?= $switchLabel ?></span>
          </a>

          <button class="icon-btn" title="Messages">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
          </button>
          <button class="icon-btn" title="Notifications">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
            <span class="badge" id="notifBadge"></span>
          </button>
          <div class="profile" style="position:relative; cursor:pointer;" onclick="this.querySelector('.profile-menu').style.display = this.querySelector('.profile-menu').style.display === 'none' ? 'block' : 'none';">
            <div class="avatar"><img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username'] ?? 'User') ?>&background=0D8B4E&color=fff&rounded=true" alt="Avatar"></div>
            <div class="profile-copy">
              <strong><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></strong>
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>
            <div class="profile-menu" style="display:none; position:absolute; top:100%; right:0; background:#fff; box-shadow:0 4px 12px rgba(0,0,0,0.1); border-radius:8px; padding:8px; min-width:140px; z-index:100;">
              <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="../admin/index.php" style="display:block; padding:8px; color:var(--primary); text-decoration:none; font-size:14px; text-align:left; border-bottom: 1px solid #eee;">Admin Panel</a>
              <?php endif; ?>
              <a href="../logout.php" style="display:block; padding:8px; color:var(--priority-high); text-decoration:none; font-size:14px; text-align:left;">Logout</a>
            </div>
          </div>
        </div>
      </header>

