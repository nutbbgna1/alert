<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

$saved = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real app, save to a settings table or config file
    $saved = true;
}
?>
  <section class="view active" id="view-settings">
    <div class="hero-row">
      <div>
        <h1>Settings</h1>
        <p class="subtle">Manage your preferences</p>
      </div>
    </div>

    <?php if($saved): ?>
      <div class="toast-inline success">✓ Settings saved successfully</div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:8px;">

      <!-- Profile Card -->
      <div class="panel">
        <div class="panel-head"><h2>Profile</h2></div>
        <form method="POST" class="settings-form">
          <label>Display Name
            <input type="text" name="display_name" value="<?= htmlspecialchars($_SESSION['username']) ?>" placeholder="Your name">
          </label>
          <label>Email
            <input type="email" name="email" value="<?= htmlspecialchars($_SESSION['username']) ?>@example.com" placeholder="Email address">
          </label>
          <label>Avatar
            <div style="display:flex;align-items:center;gap:16px;margin-top:8px;">
              <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username']) ?>&background=0D8B4E&color=fff&rounded=true&size=64" alt="Avatar" style="width:64px;height:64px;border-radius:50%;">
              <button type="button" class="outline-btn" style="font-size:13px;">Change Avatar</button>
            </div>
          </label>
          <button type="submit" class="primary-btn" style="margin-top:8px;">Save Profile</button>
        </form>
      </div>

      <!-- Notifications Card -->
      <div class="panel">
        <div class="panel-head"><h2>Notifications</h2></div>
        <form method="POST" class="settings-form">
          <label class="toggle-row">
            <div>
              <strong>Alert Reminders</strong>
              <p class="subtle">Get reminded before alert time</p>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" name="reminders" checked>
              <span class="toggle-slider"></span>
            </label>
          </label>
          <label class="toggle-row">
            <div>
              <strong>Daily Summary</strong>
              <p class="subtle">Receive daily summary at 8:00 AM</p>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" name="daily_summary">
              <span class="toggle-slider"></span>
            </label>
          </label>
          <label class="toggle-row">
            <div>
              <strong><?= __t('notif_overdue') ?></strong>
              <p class="subtle"><?= __t('notif_overdue_desc') ?></p>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" name="overdue" checked>
              <span class="toggle-slider"></span>
            </label>
          </label>
          <button type="submit" class="primary-btn" style="margin-top:8px;">Save Notifications</button>
        </form>
      </div>

      <!-- Appearance Card -->
      <div class="panel">
        <div class="panel-head"><h2>Appearance</h2></div>
        <form method="POST" class="settings-form">
          <label>Theme
            <select name="theme">
              <option value="light" selected>Light</option>
              <option value="dark">Dark</option>
              <option value="auto">Auto (System)</option>
            </select>
          </label>
          <label>Accent Color
            <div style="display:flex;gap:12px;margin-top:8px;">
              <?php foreach(['#0D8B4E','#3B82F6','#8B5CF6','#EF4444','#F59E0B'] as $c): ?>
                <button type="button" class="color-swatch <?= $c === '#0D8B4E' ? 'active' : '' ?>" style="background:<?= $c ?>;" data-color="<?= $c ?>"></button>
              <?php endforeach; ?>
            </div>
          </label>
          <label>Font Size
            <select name="font_size">
              <option value="sm">Small</option>
              <option value="md" selected>Medium</option>
              <option value="lg">Large</option>
            </select>
          </label>
          <button type="submit" class="primary-btn" style="margin-top:8px;">Apply</button>
        </form>
      </div>

      <!-- Security Card -->
      <div class="panel">
        <div class="panel-head"><h2>Security</h2></div>
        <div class="settings-form">
          <div class="danger-zone-item">
            <div>
              <strong>WebAuthn / Face ID</strong>
              <p class="subtle">Log in using your face, fingerprint, or device PIN</p>
            </div>
            <button class="outline-btn" id="registerDeviceBtn">Register Device</button>
          </div>
        </div>
      </div>

      <!-- Danger Zone -->
      <div class="panel">
        <div class="panel-head"><h2 style="color:var(--priority-high);">Danger Zone</h2></div>
        <div class="settings-form">
          <div class="danger-zone-item">
            <div>
              <strong><?= __t('danger_clear_completed') ?></strong>
              <p class="subtle"><?= __t('danger_clear_desc') ?></p>
            </div>
            <button class="outline-btn danger-btn" id="clearCompletedBtn">Clear</button>
          </div>
          <div class="danger-zone-item" style="margin-top:16px;">
            <div>
              <strong>Reset All Data</strong>
              <p class="subtle"><?= __t('danger_reset_desc') ?></p>
            </div>
            <button class="outline-btn danger-btn" id="resetAllBtn">Reset</button>
          </div>
        </div>
      </div>

    </div>
  </section>

<script>
document.getElementById('registerDeviceBtn').addEventListener('click', async () => {
    try {
        const btn = document.getElementById('registerDeviceBtn');
        btn.textContent = 'Registering...';
        btn.disabled = true;

        const resp = await fetch('../actions/webauthn_handler.php?action=get_registration_challenge');
        if (!resp.ok) throw new Error(await resp.text());
        const createArgs = await resp.json();

        // Convert base64url to Uint8Array
        const b64ToBuf = (str) => Uint8Array.from(atob(str.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0));
        
        createArgs.publicKey.challenge = b64ToBuf(createArgs.publicKey.challenge);
        createArgs.publicKey.user.id = b64ToBuf(createArgs.publicKey.user.id);
        if (createArgs.publicKey.excludeCredentials) {
            createArgs.publicKey.excludeCredentials.forEach(cred => {
                cred.id = b64ToBuf(cred.id);
            });
        }

        const credential = await navigator.credentials.create(createArgs);

        const formData = new FormData();
        formData.append('action', 'verify_registration');
        formData.append('clientDataJSON', btoa(String.fromCharCode.apply(null, new Uint8Array(credential.response.clientDataJSON))));
        formData.append('attestationObject', btoa(String.fromCharCode.apply(null, new Uint8Array(credential.response.attestationObject))));

        const verifyResp = await fetch('../actions/webauthn_handler.php', {
            method: 'POST',
            body: formData
        });
        
        const verifyData = await verifyResp.json();
        if (verifyData.status === 'success') {
            alert('Device registered successfully! You can now use Face ID / Biometrics to log in.');
        } else {
            alert('Failed to register: ' + verifyData.msg);
        }
    } catch (err) {
        alert('Error: ' + err.message);
        console.error(err);
    } finally {
        const btn = document.getElementById('registerDeviceBtn');
        btn.textContent = 'Register Device';
        btn.disabled = false;
    }
});
</script>

<?php include '../includes/footer.php'; ?>
