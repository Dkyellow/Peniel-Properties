<?php
/**
 * Admin - Newsletter Subscribers
 */

require_once __DIR__ . '/includes/auth.php';

$db = get_db_connection();
$filterStatus = trim($_GET['status'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Security verification failed.');
    } elseif (!$db) {
        set_flash('danger', 'Database connection is unavailable.');
    } else {
        $action = $_POST['action'] ?? '';
        try {
            if ($action === 'create_campaign') {
                $subject = trim($_POST['subject'] ?? '');
                $previewText = trim($_POST['preview_text'] ?? '');
                $bodyHtml = trim($_POST['body_html'] ?? '');
                if ($subject === '' || strlen($subject) > 255 || $bodyHtml === '') {
                    throw new RuntimeException('Enter a subject and newsletter content before saving.');
                }
                $db->prepare('INSERT INTO newsletter_campaigns (subject, preview_text, body_html) VALUES (?, ?, ?)')
                    ->execute([$subject, $previewText, $bodyHtml]);
                set_flash('success', 'Campaign saved as a draft.');
            } elseif ($action === 'send_campaign') {
                $campaignId = (int)($_POST['campaign_id'] ?? 0);
                if ($campaignId < 1 || !function_exists('mail')) {
                    throw new RuntimeException('Mail delivery is not available on this server.');
                }

                $campaignStmt = $db->prepare("SELECT * FROM newsletter_campaigns WHERE id = ? AND status = 'draft'");
                $campaignStmt->execute([$campaignId]);
                $campaign = $campaignStmt->fetch();
                if (!$campaign) {
                    throw new RuntimeException('This campaign is unavailable or has already been sent.');
                }

                $subscribers = $db->query("SELECT email FROM newsletter_subscribers WHERE status = 'active' ORDER BY id ASC")->fetchAll();
                $sentCount = 0;
                $failedCount = 0;
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                $headers .= 'From: ' . COMPANY_EMAIL . "\r\n";
                $headers .= 'Reply-To: ' . COMPANY_EMAIL . "\r\n";

                foreach ($subscribers as $subscriber) {
                    $unsubscribeToken = hash_hmac('sha256', $subscriber['email'], NEWSLETTER_UNSUBSCRIBE_KEY);
                    $unsubscribeUrl = url('newsletter.php?unsubscribe=' . rawurlencode($unsubscribeToken) . '&email=' . rawurlencode($subscriber['email']));
                    $emailBody = '<!doctype html><html><body style="font-family:Arial,sans-serif;color:#152333;line-height:1.6;">';
                    $emailBody .= '<div style="max-width:680px;margin:0 auto;padding:32px 20px;">';
                    $emailBody .= '<p style="color:#0B2D50;font-weight:bold;">' . e(SITE_NAME) . '</p>';
                    if (!empty($campaign['preview_text'])) {
                        $emailBody .= '<p style="color:#5F7184;">' . e($campaign['preview_text']) . '</p>';
                    }
                    $emailBody .= $campaign['body_html'];
                    $emailBody .= '<hr style="border:0;border-top:1px solid #DDE4EB;margin:32px 0 16px;">';
                    $emailBody .= '<p style="font-size:12px;color:#5F7184;">You are receiving this because you subscribed to Peniel Properties updates. <a href="' . e($unsubscribeUrl) . '">Unsubscribe</a>.</p>';
                    $emailBody .= '</div></body></html>';

                    if (mail($subscriber['email'], $campaign['subject'], $emailBody, $headers)) {
                        $sentCount++;
                    } else {
                        $failedCount++;
                    }
                }

                $status = $sentCount > 0 ? 'sent' : 'failed';
                $db->prepare('UPDATE newsletter_campaigns SET status = ?, recipient_count = ?, sent_count = ?, failed_count = ?, sent_at = CURRENT_TIMESTAMP WHERE id = ?')
                    ->execute([$status, count($subscribers), $sentCount, $failedCount, $campaignId]);
                set_flash($status === 'sent' ? 'success' : 'danger', "Campaign processed: {$sentCount} accepted, {$failedCount} failed.");
            } else {
                $subscriberId = (int)($_POST['subscriber_id'] ?? 0);
                if ($subscriberId > 0 && $action === 'set_status') {
                $status = ($_POST['status'] ?? '') === 'unsubscribed' ? 'unsubscribed' : 'active';
                $db->prepare("UPDATE newsletter_subscribers SET status = ?, unsubscribed_at = ? WHERE id = ?")
                    ->execute([$status, $status === 'unsubscribed' ? date('Y-m-d H:i:s') : null, $subscriberId]);
                set_flash('success', 'Subscriber status updated.');
                } elseif ($subscriberId > 0 && $action === 'delete') {
                $db->prepare('DELETE FROM newsletter_subscribers WHERE id = ?')->execute([$subscriberId]);
                set_flash('success', 'Subscriber deleted.');
                }
            }
        } catch (RuntimeException $e) {
            error_log('Newsletter Admin Error: ' . $e->getMessage());
            set_flash('danger', $e instanceof RuntimeException ? $e->getMessage() : 'The newsletter operation failed.');
        }
    }
    header('Location: ' . url('admin/newsletter.php?' . http_build_query(['status' => $filterStatus])));
    exit;
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    if (!$db) {
        set_flash('danger', 'Database connection is unavailable.');
        header('Location: ' . url('admin/newsletter.php'));
        exit;
    }

    $stmt = $db->query("SELECT email, status, subscribed_at, unsubscribed_at FROM newsletter_subscribers ORDER BY subscribed_at DESC");
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=peniel-newsletter-subscribers.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Email', 'Status', 'Subscribed At', 'Unsubscribed At']);
    while ($subscriber = $stmt->fetch()) {
        fputcsv($output, [$subscriber['email'], $subscriber['status'], $subscriber['subscribed_at'], $subscriber['unsubscribed_at']]);
    }
    fclose($output);
    exit;
}

$subscribers = [];
$campaigns = [];
if ($db) {
    try {
        $where = '';
        $params = [];
        if (in_array($filterStatus, ['active', 'unsubscribed'], true)) {
            $where = ' WHERE status = ?';
            $params[] = $filterStatus;
        }
        $stmt = $db->prepare("SELECT * FROM newsletter_subscribers{$where} ORDER BY subscribed_at DESC");
        $stmt->execute($params);
        $subscribers = $stmt->fetchAll();
        $campaigns = $db->query('SELECT * FROM newsletter_campaigns ORDER BY created_at DESC LIMIT 20')->fetchAll();
    } catch (PDOException $e) {
        error_log('Newsletter Query Error: ' . $e->getMessage());
    }
}

$pageTitle = 'Newsletter Subscribers';
$activeNav = 'newsletter';
require_once __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h2 style="font-size: 1.5rem; color: var(--admin-navy);">Newsletter Subscribers</h2>
    <span style="font-size: 0.875rem; color: var(--admin-muted);">Total in this view: <?= count($subscribers) ?></span>
  </div>
  <a href="<?= url('admin/newsletter.php?export=csv') ?>" class="btn-admin btn-admin-primary">Export CSV</a>
</div>

<div class="admin-card" style="padding: 24px;">
  <h3 class="admin-card-title" style="margin-bottom: 6px;">Create Campaign</h3>
  <p style="font-size: 0.875rem; color: var(--admin-muted); margin-bottom: 20px;">Write a campaign, save it as a draft, then send it to all active subscribers.</p>
  <form method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="create_campaign">
    <div class="form-grid">
      <div>
        <label class="admin-label" for="campaignSubject">Subject *</label>
        <input class="admin-input" id="campaignSubject" name="subject" maxlength="255" required placeholder="New homes in Borrowdale">
      </div>
      <div>
        <label class="admin-label" for="campaignPreview">Preview text</label>
        <input class="admin-input" id="campaignPreview" name="preview_text" maxlength="255" placeholder="A short summary shown in inboxes">
      </div>
      <div class="form-full">
        <label class="admin-label" for="campaignBody">Newsletter content *</label>
        <textarea class="admin-textarea" id="campaignBody" name="body_html" rows="9" required placeholder="Write your newsletter content here. Basic HTML is supported."></textarea>
      </div>
    </div>
    <button type="submit" class="btn-admin btn-admin-primary" style="margin-top: 18px;">Save Draft</button>
  </form>
</div>

<div class="admin-card">
  <div class="admin-card-header"><h3 class="admin-card-title">Campaigns</h3></div>
  <?php if (!empty($campaigns)): ?>
    <div style="overflow-x: auto;">
      <table class="admin-table">
        <thead><tr><th>Subject</th><th>Status</th><th>Recipients</th><th>Created</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($campaigns as $campaign): ?>
          <tr>
            <td><strong><?= e($campaign['subject']) ?></strong></td>
            <td><span class="admin-badge <?= $campaign['status'] === 'sent' ? 'badge-published' : ($campaign['status'] === 'failed' ? 'badge-new' : 'badge-draft') ?>"><?= ucfirst($campaign['status']) ?></span></td>
            <td><?= (int)$campaign['sent_count'] ?> / <?= (int)$campaign['recipient_count'] ?></td>
            <td><?= date('d M Y H:i', strtotime($campaign['created_at'])) ?></td>
            <td>
              <?php if ($campaign['status'] === 'draft'): ?>
                <form method="POST" onsubmit="return confirm('Send this campaign to all active subscribers?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="send_campaign">
                  <input type="hidden" name="campaign_id" value="<?= (int)$campaign['id'] ?>">
                  <button type="submit" class="btn-admin btn-admin-lime" style="padding: 5px 10px; font-size: 0.75rem;">Send Now</button>
                </form>
              <?php else: ?>
                <span style="font-size: 0.75rem; color: var(--admin-muted);">Processed</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div style="padding: 36px; text-align: center; color: var(--admin-muted);">No campaigns created yet.</div>
  <?php endif; ?>
</div>

<div style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;">
  <a href="<?= url('admin/newsletter.php') ?>" class="btn-admin <?= empty($filterStatus) ? 'btn-admin-primary' : 'btn-admin-outline' ?>">All</a>
  <a href="<?= url('admin/newsletter.php?status=active') ?>" class="btn-admin <?= $filterStatus === 'active' ? 'btn-admin-primary' : 'btn-admin-outline' ?>">Active</a>
  <a href="<?= url('admin/newsletter.php?status=unsubscribed') ?>" class="btn-admin <?= $filterStatus === 'unsubscribed' ? 'btn-admin-primary' : 'btn-admin-outline' ?>">Unsubscribed</a>
</div>

<div class="admin-card">
  <?php if (!empty($subscribers)): ?>
    <div style="overflow-x: auto;">
      <table class="admin-table">
        <thead><tr><th>Email</th><th>Status</th><th>Subscribed</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($subscribers as $subscriber): ?>
          <tr>
            <td><strong><?= e($subscriber['email']) ?></strong></td>
            <td><span class="admin-badge <?= $subscriber['status'] === 'active' ? 'badge-published' : 'badge-draft' ?>"><?= ucfirst($subscriber['status']) ?></span></td>
            <td><?= date('d M Y H:i', strtotime($subscriber['subscribed_at'])) ?></td>
            <td>
              <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                <form method="POST">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="set_status">
                  <input type="hidden" name="subscriber_id" value="<?= (int)$subscriber['id'] ?>">
                  <input type="hidden" name="status" value="<?= $subscriber['status'] === 'active' ? 'unsubscribed' : 'active' ?>">
                  <button type="submit" class="btn-admin btn-admin-outline" style="padding: 5px 8px; font-size: 0.75rem;"><?= $subscriber['status'] === 'active' ? 'Unsubscribe' : 'Reactivate' ?></button>
                </form>
                <form method="POST">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="subscriber_id" value="<?= (int)$subscriber['id'] ?>">
                  <button type="submit" class="btn-admin btn-admin-danger" style="padding: 5px 8px; font-size: 0.75rem;" data-confirm="Delete this subscriber?">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div style="padding: 48px; text-align: center; color: var(--admin-muted);">No newsletter subscribers in this view.</div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
