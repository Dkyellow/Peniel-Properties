<?php
/**
 * Global Helper Functions
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Escape HTML output to prevent XSS
 */
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate full URL from relative path
 */
function url(string $path = ''): string {
    $path = ltrim($path, '/');
    return rtrim(BASE_URL, '/') . ($path !== '' ? '/' . $path : '');
}

/**
 * Generate full URL for static assets
 */
function asset(string $path = ''): string {
    return url($path);
}

/**
 * CSRF Token Generation
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF Hidden Input Field
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Verify CSRF Token
 */
function verify_csrf_token(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Format Price with Currency and Rental Frequency
 */
function format_price($price, string $currency = 'USD', ?string $frequency = null): string {
    $symbol = ($currency === 'USD') ? '$' : $currency . ' ';
    $formatted = $symbol . number_format((float)$price, 0);
    
    if (!empty($frequency)) {
        $freqMap = [
            'month' => '/ mo',
            'year'  => '/ yr',
            'day'   => '/ day'
        ];
        $formatted .= ' ' . ($freqMap[strtolower($frequency)] ?? '/ ' . $frequency);
    }
    
    return $formatted;
}

/**
 * Clean Slug Generator
 */
function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'property-' . time() : $text;
}

/**
 * Generate Unique Property Reference
 */
function generate_property_reference(PDO $db): string {
    do {
        $ref = 'PP-' . rand(1000, 9999);
        $stmt = $db->prepare("SELECT COUNT(*) FROM properties WHERE reference_no = ?");
        $stmt->execute([$ref]);
        $exists = $stmt->fetchColumn() > 0;
    } while ($exists);

    return $ref;
}

/**
 * Generate official WhatsApp click-to-chat URL
 */
function get_whatsapp_url(string $phone, string $message): string {
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    return 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($message);
}

/**
 * Render visual status badge for listings
 */
function render_status_badge(string $status, string $category = 'sale'): string {
    $status = strtolower($status);
    $badgeClasses = [
        'available'   => 'badge-available',
        'under_offer' => 'badge-under-offer',
        'sold'        => 'badge-sold',
        'rented'      => 'badge-rented'
    ];

    $labels = [
        'available'   => ($category === 'rent' ? 'For Rent' : 'For Sale'),
        'under_offer' => 'Under Offer',
        'sold'        => 'Sold',
        'rented'      => 'Rented'
    ];

    $class = $badgeClasses[$status] ?? 'badge-neutral';
    $label = $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));

    return '<span class="property-badge ' . e($class) . '">' . e($label) . '</span>';
}

/**
 * Flash message helpers
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type'    => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

function get_flash(): ?array {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Authentication Guards
 */
function is_logged_in(): bool {
    return !empty($_SESSION['admin_id']);
}

function current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'        => $_SESSION['admin_id'],
        'username'  => $_SESSION['admin_username'] ?? '',
        'full_name' => $_SESSION['admin_name'] ?? '',
        'role'      => $_SESSION['admin_role'] ?? 'admin'
    ];
}

function require_login(): void {
    if (!is_logged_in()) {
        set_flash('danger', 'Please log in to access the administrator panel.');
        header('Location: ' . url('admin/login.php'));
        exit;
    }
}

/**
 * Handle secure image upload with MIME validation
 */
function handle_image_upload(array $file): string {
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Invalid parameters.');
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown upload error.');
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        throw new RuntimeException('Exceeded filesize limit (Max 6MB).');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES, true)) {
        throw new RuntimeException('Invalid file format. Only JPG, PNG, and WebP are allowed.');
    }

    $ext = match ($mimeType) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        default      => 'jpg'
    };

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $fileName = 'prop_' . bin2hex(random_bytes(10)) . '.' . $ext;
    $targetPath = UPLOAD_DIR . DIRECTORY_SEPARATOR . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    return 'uploads/properties/' . $fileName;
}

/**
 * Fetch a site setting by key
 */
function get_setting(string $key, string $default = ''): string {
    $db = get_db_connection();
    if (!$db) {
        return $default;
    }

    try {
        $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return ($val !== false && $val !== null) ? $val : $default;
    } catch (Exception $e) {
        return $default;
    }
}
