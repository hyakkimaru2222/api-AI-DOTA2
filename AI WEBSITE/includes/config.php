<?php
define('OPENDOTA_API_BASE', 'https://api.opendota.com/api');
define('CACHE_DURATION', 300);
define('SITE_NAME', 'Dota2ProTracker');
define('SITE_TAGLINE', 'Professional Dota 2 Statistics & Analytics');

$isProduction = ($_SERVER['REPL_OWNER'] ?? '') !== '';
if (!$isProduction) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
}

date_default_timezone_set('UTC');

// Disable dangerous PHP functions and features
if (function_exists('ini_set')) {
    ini_set('expose_php', 'Off');
    ini_set('allow_url_fopen', '1'); // Keep enabled for API calls
    ini_set('allow_url_include', '0');
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
}

// Load security manager
require_once __DIR__ . '/security.php';

if (session_status() === PHP_SESSION_NONE) {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', $isSecure ? 1 : 0);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.name', 'D2PT_SESSION');
}

// Initialize security (rate limiting, headers, attack detection)
$securityManager = initSecurity();

function initSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        
        // Regenerate session ID on first access
        if (!isset($_SESSION['initiated'])) {
            session_regenerate_id(true);
            $_SESSION['initiated'] = true;
            $_SESSION['created'] = time();
        }
        
        // Regenerate session ID periodically (every 30 minutes)
        if (isset($_SESSION['created']) && (time() - $_SESSION['created']) > 1800) {
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
}
