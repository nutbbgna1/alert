<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: page/index.php");
    exit;
}
require_once 'includes/lang.php';
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
  <meta charset="UTF-8">
  <title>Login - <?= __t('app_name') ?></title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    body { background: #f9fafb; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
    .auth-card { background: #fff; padding: 32px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); width: 100%; max-width: 400px; text-align: center; }
    .auth-card h1 { margin-top: 0; color: var(--text-main); }
    .auth-form { display: flex; flex-direction: column; gap: 16px; margin-top: 24px; }
    .auth-input { padding: 12px; border: 2px solid #e5e7eb; background: #f3f4f6; border-radius: 8px; font-size: 15px; outline: none; transition: all 0.2s; }
    .auth-input:focus { border-color: var(--primary); background: #ffffff; box-shadow: 0 0 0 3px rgba(13, 139, 78, 0.15); }
    .auth-btn { background: var(--primary); color: #fff; padding: 12px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
    .auth-btn:hover { background: var(--primary-dark); }
    .auth-link { margin-top: 16px; font-size: 14px; }
    .error-msg { color: #E53935; background: #FFEBEE; padding: 10px; border-radius: 6px; font-size: 14px; margin-bottom: 16px; }
  </style>
</head>
<body>
  <div class="auth-card">
    <h1>Login</h1>
    <?php if (isset($_SESSION['error'])): ?>
      <div class="error-msg"><?= htmlspecialchars($_SESSION['error']) ?></div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <form class="auth-form" action="actions/auth_action.php" method="POST">
      <input type="hidden" name="action" value="login">
      <input type="text" name="username" class="auth-input" placeholder="Username" required>
      <input type="password" name="password" class="auth-input" placeholder="Password" required>
      <button type="submit" class="auth-btn">Log In</button>
    </form>
    <div class="auth-link">
      Don't have an account? <a href="register.php" style="color:var(--primary);text-decoration:none;">Register</a>
    </div>

    <div style="margin-top: 24px; border-top: 1px solid var(--border-color); padding-top: 24px;">
      <button type="button" class="auth-btn" id="faceLoginBtn" style="background: #333;">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 8px;"><path d="M9 3h-2a2 2 0 0 0 -2 2v2"></path><path d="M15 3h2a2 2 0 0 1 2 2v2"></path><path d="M19 15v2a2 2 0 0 1 -2 2h-2"></path><path d="M9 21h-2a2 2 0 0 1 -2 -2v-2"></path><path d="M9 10h.01"></path><path d="M15 10h.01"></path><path d="M9 15h6"></path></svg>
        Log in with Face ID / Biometrics
      </button>
      <p style="font-size: 13px; color: #666; margin-top: 12px;">Ensure you have registered your device in Settings first.</p>
    </div>
  </div>

<script>
document.getElementById('faceLoginBtn').addEventListener('click', async () => {
    try {
        const btn = document.getElementById('faceLoginBtn');
        btn.textContent = 'Authenticating...';
        btn.disabled = true;

        // Optionally get username from input if you want identifier-first, or just leave empty for discoverable credentials
        const username = document.querySelector('input[name="username"]').value;
        const formData = new FormData();
        if (username) formData.append('username', username);

        const resp = await fetch('actions/webauthn_handler.php?action=get_login_challenge', {
            method: 'POST',
            body: formData
        });
        
        if (!resp.ok) throw new Error(await resp.text());
        const getArgs = await resp.json();

        // Convert base64url to Uint8Array
        const b64ToBuf = (str) => Uint8Array.from(atob(str.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0));
        
        getArgs.publicKey.challenge = b64ToBuf(getArgs.publicKey.challenge);
        if (getArgs.publicKey.allowCredentials) {
            getArgs.publicKey.allowCredentials.forEach(cred => {
                cred.id = b64ToBuf(cred.id);
            });
        }

        const credential = await navigator.credentials.get(getArgs);

        const verifyData = new FormData();
        verifyData.append('action', 'verify_login');
        verifyData.append('id', btoa(String.fromCharCode.apply(null, new Uint8Array(credential.rawId))));
        verifyData.append('clientDataJSON', btoa(String.fromCharCode.apply(null, new Uint8Array(credential.response.clientDataJSON))));
        verifyData.append('authenticatorData', btoa(String.fromCharCode.apply(null, new Uint8Array(credential.response.authenticatorData))));
        verifyData.append('signature', btoa(String.fromCharCode.apply(null, new Uint8Array(credential.response.signature))));
        if (credential.response.userHandle) {
            verifyData.append('userHandle', btoa(String.fromCharCode.apply(null, new Uint8Array(credential.response.userHandle))));
        }

        const verifyResp = await fetch('actions/webauthn_handler.php', {
            method: 'POST',
            body: verifyData
        });
        
        const verifyJson = await verifyResp.json();
        if (verifyJson.status === 'success') {
            window.location.href = 'page/index.php';
        } else {
            alert('Login failed: ' + verifyJson.msg);
        }
    } catch (err) {
        alert('Error: ' + err.message);
        console.error(err);
    } finally {
        const btn = document.getElementById('faceLoginBtn');
        btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 8px;"><path d="M9 3h-2a2 2 0 0 0 -2 2v2"></path><path d="M15 3h2a2 2 0 0 1 2 2v2"></path><path d="M19 15v2a2 2 0 0 1 -2 2h-2"></path><path d="M9 21h-2a2 2 0 0 1 -2 -2v-2"></path><path d="M9 10h.01"></path><path d="M15 10h.01"></path><path d="M9 15h6"></path></svg> Log in with Face ID / Biometrics`;
        btn.disabled = false;
    }
});
</script>
</body>
</html>
