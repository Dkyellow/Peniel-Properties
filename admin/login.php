<?php
/**
 * Administrator Secure Login
 */

require_once __DIR__ . '/../app/helpers/helpers.php';

// If already logged in, redirect to admin dashboard
if (is_logged_in()) {
    header('Location: ' . url('admin/index.php'));
    exit;
}

$db = get_db_connection();
$errorMessage = '';

// Rate Limiting / Login Throttling
$maxAttempts = 5;
$lockoutTime = 300; // 5 minutes

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

$timeSinceLast = time() - ($_SESSION['last_attempt_time'] ?? 0);
if ($_SESSION['login_attempts'] >= $maxAttempts && $timeSinceLast < $lockoutTime) {
    $remaining = ceil(($lockoutTime - $timeSinceLast) / 60);
    $errorMessage = "Too many failed attempts. Please wait {$remaining} minute(s) before trying again.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errorMessage)) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errorMessage = 'Security validation failed (CSRF token expired). Please refresh.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $errorMessage = 'Please enter both your username and password.';
        } elseif (!$db) {
            $errorMessage = 'Database is currently unreachable. Please verify database connection.';
        } else {
            try {
                $stmt = $db->prepare("SELECT * FROM admins WHERE username = ? OR email = ? LIMIT 1");
                $stmt->execute([$username, $username]);
                $admin = $stmt->fetch();

                if ($admin && password_verify($password, $admin['password_hash'])) {
                    // Reset attempt counter
                    $_SESSION['login_attempts'] = 0;

                    // Regenerate session ID to prevent session fixation attacks
                    session_regenerate_id(true);

                    // Set authenticated session state
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_name'] = $admin['full_name'];
                    $_SESSION['admin_role'] = $admin['role'];

                    // Update last login timestamp
                    $db->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);

                    header('Location: ' . url('admin/index.php'));
                    exit;
                } else {
                    $_SESSION['login_attempts']++;
                    $_SESSION['last_attempt_time'] = time();
                    $errorMessage = 'Invalid username or password credentials.';
                }
            } catch (PDOException $e) {
                error_log("Login Query Error: " . $e->getMessage());
                $errorMessage = 'An error occurred during authentication.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | Peniel Properties</title>
  <link rel="icon" type="image/png" href="<?= asset(LOGO_FAVICON) ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>">
  <style>
    body {
      background-color: var(--admin-navy);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 24px;
    }
    .login-panel {
      background-color: #FFFFFF;
      border-radius: 12px;
      width: 100%;
      max-width: 440px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
      overflow: hidden;
    }
    .login-header {
      background-color: #051A2E;
      padding: 36px 32px 30px;
      text-align: center;
      border-bottom: 3px solid var(--admin-lime);
    }
    .login-header img {
      max-height: 52px;
      margin: 0 auto;
    }
    .login-body {
      padding: 36px 32px;
    }
  </style>
</head>
<body>

  <div class="login-panel">
    <div class="login-header">
      <img src="<?= asset(LOGO_WHITE) ?>" alt="Peniel Properties">
      <div style="color: #94A3B8; font-size: 0.8125rem; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; margin-top: 12px;">
        Staff Management Portal
      </div>
    </div>

    <div class="login-body">
      <?php if (!empty($errorMessage)): ?>
        <div style="background-color: #FEE2E2; color: #991B1B; padding: 12px 16px; border-radius: 6px; font-size: 0.875rem; margin-bottom: 20px; border: 1px solid #FECACA;">
          <?= e($errorMessage) ?>
        </div>
      <?php endif; ?>

      <form action="" method="POST">
        <?= csrf_field() ?>

        <div style="margin-bottom: 20px;">
          <label class="admin-label" for="adminUser">Username or Email</label>
          <input type="text" id="adminUser" name="username" class="admin-input" required autocomplete="username" placeholder="e.g. admin">
        </div>

        <div style="margin-bottom: 24px;">
          <label class="admin-label" for="adminPass">Password</label>
          <div style="position: relative;">
            <input type="password" id="adminPass" name="password" class="admin-input" required autocomplete="current-password" placeholder="••••••••••••" style="padding-right: 44px;">
            <button type="button" id="togglePasswordBtn" aria-label="Show password" title="Show password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--admin-muted); padding: 4px; display: flex; align-items: center; justify-content: center;">
              <svg id="eyeIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-admin btn-admin-lime" style="width: 100%; height: 48px; justify-content: center; font-size: 1rem;">
          Sign In to Dashboard
        </button>
      </form>

      <div style="text-align: center; margin-top: 24px;">
        <a href="<?= url() ?>" style="font-size: 0.875rem; color: var(--admin-muted); text-decoration: none;">
          &larr; Return to Public Website
        </a>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const toggleBtn = document.getElementById('togglePasswordBtn');
      const passInput = document.getElementById('adminPass');

      if (toggleBtn && passInput) {
        toggleBtn.addEventListener('click', function () {
          const isPassword = passInput.type === 'password';
          passInput.type = isPassword ? 'text' : 'password';
          toggleBtn.setAttribute('title', isPassword ? 'Hide password' : 'Show password');
          toggleBtn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');

          if (isPassword) {
            // Eye-off icon
            toggleBtn.innerHTML = `
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                <line x1="2" y1="2" x2="22" y2="22"></line>
              </svg>
            `;
          } else {
            // Eye icon
            toggleBtn.innerHTML = `
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            `;
          }
        });
      }
    });
  </script>
</body>
</html>
