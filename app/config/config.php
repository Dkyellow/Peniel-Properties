<?php
/**
 * Peniel Properties - Core Application Configuration
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// Error reporting (Set to 0 in production)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Base Path Definition
define('ROOT_PATH', dirname(__DIR__, 2));
define('APP_PATH', dirname(__DIR__));
define('UPLOAD_DIR', ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'properties');

// Determine Base URL dynamically to support both localhost/subfolder and root domain
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Compute the subfolder relative to DOCUMENT_ROOT if running in a subdirectory
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $dirName = str_replace('\\', '/', dirname($scriptName));
    
    // Normalize admin or root directory
    $subDir = preg_replace('#/(admin|app).*$#', '', $dirName);
    $subDir = rtrim($subDir, '/');
    
    define('BASE_URL', $protocol . $host . $subDir);
}

// Database Credentials
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'peniel_properties');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// Brand & Logo Configuration (Configurable without modifying layouts)
define('SITE_NAME', 'Peniel Properties');
define('SITE_TAGLINE', 'Find a Place to Call Yours');
define('SITE_DESCRIPTION', 'Discover properties for sale and rent across Zimbabwe. Exclusive residential and commercial real estate.');

// Logo assets (easily swap production artwork here)
define('LOGO_MAIN', 'assets/images/logo.svg');
define('LOGO_WHITE', 'assets/images/logo-white.svg');
define('LOGO_FAVICON', 'assets/images/favicon.svg');

// Primary Contact Info
define('COMPANY_PHONE', '+263 77 212 3456');
define('COMPANY_WHATSAPP', '+263772123456');
define('COMPANY_EMAIL', 'info@penielproperties.co.zw');
define('COMPANY_ADDRESS', 'Suite 4, Peniel House, Borrowdale Road, Harare, Zimbabwe');

// Security & Uploads
define('MAX_FILE_SIZE', 6 * 1024 * 1024); // 6MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_IMAGE_EXTS', ['jpg', 'jpeg', 'png', 'webp']);
