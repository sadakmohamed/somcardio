<?php
/**
 * Database & System Configuration — Somali Cardiac Society
 * Secure environment loading (.env) with PDO connection
 */

// ── 1. Lightweight Native .env Parser ────────────────────────────────
function loadEnv(string $path): void {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

// Load environment configuration
loadEnv(__DIR__ . '/../.env');

// ── 2. Helper to fetch Environment Variable with Fallback ────────────
function env(string $key, mixed $default = null): mixed {
    $value = getenv($key);
    if ($value === false) {
        return $_ENV[$key] ?? $default;
    }
    return $value;
}

// ── 3. System Constants ──────────────────────────────────────────────
define('APP_ENV',       env('APP_ENV', 'development'));
define('SITE_NAME',     env('APP_NAME', 'Somali Cardiac Society'));
define('SITE_URL',      env('APP_URL', '/ssc'));

define('DB_HOST',       env('DB_HOST', 'localhost'));
define('DB_PORT',       env('DB_PORT', '3306'));
define('DB_NAME',       env('DB_NAME', 'scs_db'));
define('DB_USER',       env('DB_USER', 'root'));
define('DB_PASS',       env('DB_PASS', ''));
define('DB_CHARSET',    env('DB_CHARSET', 'utf8mb4'));

define('CONTACT_EMAIL', env('CONTACT_EMAIL', 'sadikothm@gmail.com'));

define('UPLOADS_DIR',   __DIR__ . '/../uploads');
define('UPLOADS_URL',   SITE_URL . '/uploads');

// ── 4. Error Display Policy Based on Environment ─────────────────────
if (APP_ENV === 'production') {
    ini_set('display_errors', '0');
    error_reporting(0);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

// ── 5. Database Connection (PDO) ─────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failure: " . $e->getMessage());
            if (APP_ENV === 'production') {
                die("A system error occurred. Please try again later.");
            } else {
                die("Database Connection Error: " . $e->getMessage());
            }
        }
    }
    
    return $pdo;
}
