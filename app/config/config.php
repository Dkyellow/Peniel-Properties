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

// Optional .env loader — lets cPanel/production credentials live in a single
// untracked file at the project root (the root .htaccess blocks web access to it).
if (defined('ROOT_PATH')) {
    $envFile = ROOT_PATH . DIRECTORY_SEPARATOR . '.env';
    if (is_readable($envFile) && ($envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) !== false) {
        foreach ($envLines as $envLine) {
            $envLine = trim($envLine);
            if ($envLine === '' || $envLine[0] === '#' || strpos($envLine, '=') === false) {
                continue;
            }
            list($envKey, $envValue) = explode('=', $envLine, 2);
            $envKey = trim($envKey);
            $envValue = trim($envValue);
            if ($envValue !== '' && strlen($envValue) > 1 && ($envValue[0] === '"' || $envValue[0] === "'") && substr($envValue, -1) === $envValue[0]) {
                $envValue = substr($envValue, 1, -1);
            }
            if ($envKey !== '' && getenv($envKey) === false) {
                if (function_exists('putenv')) {
                    putenv($envKey . '=' . $envValue);
                }
                $_ENV[$envKey] = $envValue;
            }
        }
    }
}

if (!function_exists('env_value')) {
    function env_value(string $key, string $default = ''): string
    {
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return (string) $_ENV[$key];
        }
        return $default;
    }
}

// Database Credentials
define('DB_HOST', env_value('DB_HOST', '127.0.0.1'));
define('DB_PORT', env_value('DB_PORT', '3306'));
define('DB_NAME', env_value('DB_NAME', 'peniel_properties'));
define('DB_USER', env_value('DB_USER', 'root'));
define('DB_PASS', env_value('DB_PASS', ''));
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
define('COMPANY_PHONE', '+263 77 243 2941');
define('COMPANY_WHATSAPP', '+263772432941');
define('COMPANY_EMAIL', 'info@penielproperties.co.zw');
define('COMPANY_ADDRESS', 'Suite 4, Peniel House, Borrowdale Road, Harare, Zimbabwe');

// Security & Uploads
define('MAX_FILE_SIZE', 6 * 1024 * 1024); // 6MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_IMAGE_EXTS', ['jpg', 'jpeg', 'png', 'webp']);
