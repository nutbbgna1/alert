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
  <title>Register - <?= __t('app_name') ?></title>
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
    <h1>Create Account</h1>
    <?php if (isset($_SESSION['error'])): ?>
      <div class="error-msg"><?= htmlspecialchars($_SESSION['error']) ?></div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <form class="auth-form" action="actions/auth_action.php" method="POST">
      <input type="hidden" name="action" value="register">
      <input type="text" name="full_name" class="auth-input" placeholder="Full Name (ชื่อ-นามสกุล)" required>
      <input type="text" name="nickname" class="auth-input" placeholder="Nickname (ชื่อเล่น)" required>
      <input type="text" name="department" class="auth-input" placeholder="Department (แผนก)" required>
      <input type="text" name="username" class="auth-input" placeholder="Choose a Username" required>
      <input type="password" name="password" class="auth-input" placeholder="Create a Password" required>
      <button type="submit" class="auth-btn">Register</button>
    </form>
    <div class="auth-link">
      Already have an account? <a href="login.php" style="color:var(--primary);text-decoration:none;">Log In</a>
    </div>
  </div>
</body>
</html>
