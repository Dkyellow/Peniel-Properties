<?php
/**
 * Admin - Global Website Settings
 */

$pageTitle = 'Site Settings';
$activeNav = 'settings';

require_once __DIR__ . '/includes/header.php';

$error = '';
$settings = [];

if ($db) {
    try {
        $rows = $db->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll();
        foreach ($rows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }
    } catch (PDOException $e) {}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security verification failed.';
    } elseif (!$db) {
        $error = 'Database is currently unavailable.';
    } else {
        $allowedKeys = [
            'site_name', 'site_tagline', 'site_description',
            'contact_phone', 'whatsapp_number', 'contact_email',
            'office_address', 'working_hours',
            'facebook_url', 'instagram_url', 'linkedin_url',
            'default_currency'
        ];

        try {
            $stmt = $db->prepare("
                INSERT INTO site_settings (setting_key, setting_value)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");

            foreach ($allowedKeys as $key) {
                if (isset($_POST[$key])) {
                    $val = trim($_POST[$key]);
                    $stmt->execute([$key, $val]);
                    $settings[$key] = $val;
                }
            }

            set_flash('success', 'Site settings saved successfully.');
            header('Location: ' . url('admin/settings.php'));
            exit;
        } catch (PDOException $e) {
            $error = 'Error saving settings: ' . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security verification failed.';
    } elseif (!$db) {
        $error = 'Database is currently unavailable.';
    } else {
        $currentPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        if (empty($currentPass) || empty($newPass)) {
            $error = 'Please fill in all password fields.';
        } elseif (strlen($newPass) < 8) {
            $error = 'New password must be at least 8 characters long.';
        } elseif ($newPass !== $confirmPass) {
            $error = 'New password and confirmation do not match.';
        } else {
            $currentUser = current_user();
            $stmt = $db->prepare("SELECT password_hash FROM admins WHERE id = ?");
            $stmt->execute([$currentUser['id']]);
            $adminHash = $stmt->fetchColumn();

            if ($adminHash && password_verify($currentPass, $adminHash)) {
                $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                $updateStmt = $db->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
                $updateStmt->execute([$newHash, $currentUser['id']]);
                set_flash('success', 'Administrator password updated successfully.');
                header('Location: ' . url('admin/settings.php'));
                exit;
            } else {
                $error = 'Current password is incorrect.';
            }
        }
    }
}
?>

<div style="max-width: 860px; margin: 0 auto;">
  <div style="margin-bottom: 24px;">
    <h2 style="font-size: 1.5rem; color: var(--admin-navy);">Website & Contact Settings</h2>
    <span style="font-size: 0.875rem; color: var(--admin-muted);">Configure brand details, WhatsApp numbers, and office contacts across the website.</span>
  </div>

  <?php if (!empty($error)): ?>
    <div style="background-color: #FEE2E2; color: #991B1B; padding: 14px 18px; border-radius: var(--admin-radius); margin-bottom: 24px; border: 1px solid #FECACA;">
      <?= e($error) ?>
    </div>
  <?php endif; ?>

  <div class="admin-card" style="padding: 32px;">
    <form action="" method="POST">
      <?= csrf_field() ?>

      <h3 style="font-size: 1.125rem; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">1. Brand & General</h3>

      <div class="form-grid" style="margin-bottom: 24px;">
        <div>
          <label class="admin-label" for="siteName">Company / Site Name</label>
          <input type="text" id="siteName" name="site_name" class="admin-input" value="<?= e($settings['site_name'] ?? SITE_NAME) ?>">
        </div>

        <div>
          <label class="admin-label" for="siteTagline">Tagline</label>
          <input type="text" id="siteTagline" name="site_tagline" class="admin-input" value="<?= e($settings['site_tagline'] ?? SITE_TAGLINE) ?>">
        </div>

        <div class="form-full">
          <label class="admin-label" for="siteDesc">Meta Description (SEO)</label>
          <textarea id="siteDesc" name="site_description" class="admin-textarea" rows="2"><?= e($settings['site_description'] ?? SITE_DESCRIPTION) ?></textarea>
        </div>
      </div>

      <h3 style="font-size: 1.125rem; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">2. Communication & WhatsApp Integration</h3>

      <div class="form-grid" style="margin-bottom: 24px;">
        <div>
          <label class="admin-label" for="waNum">WhatsApp Direct Number * (International format, no + or spaces)</label>
          <input type="text" id="waNum" name="whatsapp_number" class="admin-input" placeholder="e.g. 263772123456" value="<?= e($settings['whatsapp_number'] ?? COMPANY_WHATSAPP) ?>">
          <span style="font-size: 0.75rem; color: var(--admin-muted);">Used for official click-to-chat links across property cards and inquiries.</span>
        </div>

        <div>
          <label class="admin-label" for="phoneNum">Public Display Phone</label>
          <input type="text" id="phoneNum" name="contact_phone" class="admin-input" placeholder="+263 77 212 3456" value="<?= e($settings['contact_phone'] ?? COMPANY_PHONE) ?>">
        </div>

        <div>
          <label class="admin-label" for="emailAddr">Official Inquiries Email</label>
          <input type="email" id="emailAddr" name="contact_email" class="admin-input" value="<?= e($settings['contact_email'] ?? COMPANY_EMAIL) ?>">
        </div>

        <div>
          <label class="admin-label" for="hoursWork">Working Hours</label>
          <input type="text" id="hoursWork" name="working_hours" class="admin-input" value="<?= e($settings['working_hours'] ?? 'Monday - Friday: 8:00 AM - 5:00 PM') ?>">
        </div>

        <div class="form-full">
          <label class="admin-label" for="officeAddr">Office Address</label>
          <input type="text" id="officeAddr" name="office_address" class="admin-input" value="<?= e($settings['office_address'] ?? COMPANY_ADDRESS) ?>">
        </div>
      </div>

      <h3 style="font-size: 1.125rem; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">3. Social Media Links</h3>

      <div class="form-grid" style="margin-bottom: 32px;">
        <div>
          <label class="admin-label" for="fbUrl">Facebook Page</label>
          <input type="url" id="fbUrl" name="facebook_url" class="admin-input" value="<?= e($settings['facebook_url'] ?? '') ?>">
        </div>

        <div>
          <label class="admin-label" for="instaUrl">Instagram Page</label>
          <input type="url" id="instaUrl" name="instagram_url" class="admin-input" value="<?= e($settings['instagram_url'] ?? '') ?>">
        </div>

        <div class="form-full">
          <label class="admin-label" for="liUrl">LinkedIn Page</label>
          <input type="url" id="liUrl" name="linkedin_url" class="admin-input" value="<?= e($settings['linkedin_url'] ?? '') ?>">
        </div>
      </div>

      <button type="submit" name="save_settings" value="1" class="btn-admin btn-admin-lime" style="padding: 12px 28px; font-size: 1rem;">
        Save Settings
      </button>
    </form>
  </div>

  <!-- Change Password Card -->
  <div class="admin-card" style="padding: 32px; margin-top: 32px;">
    <h3 style="font-size: 1.125rem; margin-bottom: 8px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px; color: var(--admin-navy);">
      Security & Account Password
    </h3>
    <p style="font-size: 0.875rem; color: var(--admin-muted); margin-bottom: 24px;">
      Update your administrator password credentials. Passwords must be at least 8 characters long.
    </p>

    <form action="" method="POST">
      <?= csrf_field() ?>

      <div class="form-grid" style="margin-bottom: 24px;">
        <div class="form-full">
          <label class="admin-label" for="currPass">Current Password</label>
          <div style="position: relative;">
            <input type="password" id="currPass" name="current_password" class="admin-input" required autocomplete="current-password" placeholder="••••••••••••" style="padding-right: 44px;">
            <button type="button" class="toggle-password-btn" data-target="currPass" aria-label="Show password" title="Show password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--admin-muted); padding: 4px; display: flex; align-items: center; justify-content: center;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </button>
          </div>
        </div>

        <div>
          <label class="admin-label" for="newPass">New Password (min 8 chars)</label>
          <div style="position: relative;">
            <input type="password" id="newPass" name="new_password" class="admin-input" required minlength="8" autocomplete="new-password" placeholder="••••••••••••" style="padding-right: 44px;">
            <button type="button" class="toggle-password-btn" data-target="newPass" aria-label="Show password" title="Show password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--admin-muted); padding: 4px; display: flex; align-items: center; justify-content: center;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </button>
          </div>
        </div>

        <div>
          <label class="admin-label" for="confPass">Confirm New Password</label>
          <div style="position: relative;">
            <input type="password" id="confPass" name="confirm_password" class="admin-input" required minlength="8" autocomplete="new-password" placeholder="••••••••••••" style="padding-right: 44px;">
            <button type="button" class="toggle-password-btn" data-target="confPass" aria-label="Show password" title="Show password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--admin-muted); padding: 4px; display: flex; align-items: center; justify-content: center;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <button type="submit" name="change_password" value="1" class="btn-admin btn-admin-primary" style="padding: 10px 24px;">
        Update Password
      </button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
