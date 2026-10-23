<?php
/**
 * Public newsletter subscription endpoint.
 */

require_once __DIR__ . '/app/helpers/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['unsubscribe'], $_GET['email'])) {
    $email = strtolower(trim($_GET['email']));
    $token = trim($_GET['unsubscribe']);
    $expectedToken = hash_hmac('sha256', $email, NEWSLETTER_UNSUBSCRIBE_KEY);

    if (filter_var($email, FILTER_VALIDATE_EMAIL) && hash_equals($expectedToken, $token)) {
        $db = get_db_connection();
        if ($db) {
            try {
                $stmt = $db->prepare("UPDATE newsletter_subscribers SET status = 'unsubscribed', unsubscribed_at = CURRENT_TIMESTAMP WHERE email = ?");
                $stmt->execute([$email]);
                set_flash('success', 'You have been unsubscribed from Peniel Properties updates.');
            } catch (PDOException $e) {
                error_log('Newsletter Unsubscribe Error: ' . $e->getMessage());
                set_flash('danger', 'We could not process your unsubscribe request.');
            }
        } else {
            set_flash('danger', 'The newsletter is temporarily unavailable.');
        }
    } else {
        set_flash('danger', 'This unsubscribe link is invalid or expired.');
    }

    header('Location: ' . url() . '#newsletter-signup');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit_newsletter'])) {
    header('Location: ' . url());
    exit;
}

$redirect = trim($_POST['redirect'] ?? '');
if ($redirect === '' || $redirect[0] !== '/' || str_starts_with($redirect, '//')) {
    $redirect = '/';
}

$redirectUrl = rtrim(BASE_URL, '/') . $redirect;
$email = strtolower(trim($_POST['email'] ?? ''));
$messageType = 'danger';
$message = 'Please enter a valid email address.';

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $message = 'Security validation failed. Please refresh the page and try again.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
    $message = 'Please enter a valid email address.';
} else {
    $db = get_db_connection();
    if (!$db) {
        $message = 'The newsletter is temporarily unavailable. Please try again shortly.';
    } else {
        try {
            $stmt = $db->prepare(
                "INSERT INTO newsletter_subscribers (email, status, subscribed_at, unsubscribed_at)
                 VALUES (?, 'active', CURRENT_TIMESTAMP, NULL)
                 ON DUPLICATE KEY UPDATE status = 'active', subscribed_at = CURRENT_TIMESTAMP, unsubscribed_at = NULL"
            );
            $stmt->execute([$email]);
            $messageType = 'success';
            $message = 'You are subscribed. We will send you new property opportunities and market updates.';
        } catch (PDOException $e) {
            error_log('Newsletter Signup Error: ' . $e->getMessage());
            $message = 'We could not complete your subscription. Please try again shortly.';
        }
    }
}

set_flash($messageType, $message);
header('Location: ' . $redirectUrl . '#newsletter-signup');
exit;
