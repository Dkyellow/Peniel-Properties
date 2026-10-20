<?php
/**
 * Admin Notification Feed - Returns unread inquiry stats as JSON
 */

require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

$payload = [
    'ok'      => true,
    'count'   => 0,
    'latest'  => null
];

$db = get_db_connection();

if ($db) {
    try {
        $payload['count'] = (int) $db->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn();

        $latestStmt = $db->query("
            SELECT i.id, i.name, i.phone, i.created_at, p.title AS property_title, p.reference_no
            FROM inquiries i
            LEFT JOIN properties p ON i.property_id = p.id
            WHERE i.status = 'new'
            ORDER BY i.created_at DESC
            LIMIT 1
        ");
        $payload['latest'] = $latestStmt->fetch() ?: null;
    } catch (Exception $e) {
        error_log("Notification feed error: " . $e->getMessage());
        $payload['ok'] = false;
    }
} else {
    $payload['ok'] = false;
}

echo json_encode($payload);
